<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Helper methods for interacting with the LibraPay API.
 *
 * @package    paygw_librapay
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_librapay;

use core_payment\helper;

/**
 * Helper class for LibraPay payment gateway.
 *
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class librapay_helper {
    /** @var string Test environment URL */
    const URL_TEST = 'https://merchant.librapay.ro/pay_auth.php';

    /** @var string Live environment URL */
    const URL_LIVE = 'https://secure.librapay.ro/pay_auth.php';

    /** @var object Gateway configuration */
    private $config;

    /**
     * Constructor.
     *
     * @param object $config Gateway configuration
     */
    public function __construct(object $config) {
        $this->config = $config;
    }

    /**
     * Get the LibraPay URL based on test mode setting.
     *
     * @return string
     */
    public function get_librapay_url(): string {
        return !empty($this->config->testmode) ? self::URL_TEST : self::URL_LIVE;
    }

    /**
     * Generate a unique order ID.
     * Must be 6-19 numeric characters, no leading zeros.
     *
     * @return string
     */
    public function generate_order_id(): string {
        global $DB;

        // Generate order ID using timestamp and random component.
        // Retry if collision occurs (extremely unlikely).
        $maxattempts = 10;
        for ($i = 0; $i < $maxattempts; $i++) {
            // Use microtime for better uniqueness.
            $micro = str_replace('.', '', sprintf('%.6f', microtime(true)));
            $random = random_int(100, 999);
            $orderid = substr($micro . $random, 0, 12);

            // Ensure no leading zero.
            if ($orderid[0] === '0') {
                $orderid = '1' . substr($orderid, 1);
            }

            // Check for collision.
            if (!$DB->record_exists('paygw_librapay_transactions', ['orderid' => $orderid])) {
                return $orderid;
            }
        }

        // Fallback: use uniqid-based approach.
        return substr(hexdec(uniqid()), 0, 12);
    }

    /**
     * Generate NONCE for security.
     * Uses cryptographically secure random bytes.
     *
     * @return string 32-character hex string
     */
    public function generate_nonce(): string {
        return bin2hex(random_bytes(16));
    }

    /**
     * Get current GMT timestamp in LibraPay format.
     *
     * @return string YYYYMMDDHHMMSS format
     */
    public function get_timestamp(): string {
        return gmdate('YmdHis');
    }

    /**
     * Format amount for LibraPay.
     * Must have exactly 2 decimal places.
     *
     * @param float $amount
     * @return string
     */
    public function format_amount(float $amount): string {
        return number_format($amount, 2, '.', '');
    }

    /**
     * Build DATA_CUSTOM field with product and user data.
     *
     * @param string $description Product description
     * @param float $amount Payment amount
     * @param \stdClass $user Moodle user object
     * @return string Base64 encoded serialized data
     */
    public function build_data_custom(string $description, float $amount, \stdClass $user): string {
        $productsdata = [
            [
                'ItemName' => substr($description, 0, 50),
                'ItemDesc' => substr($description, 0, 50),
                'Quantity' => 1,
                'Price' => $this->format_amount($amount),
            ],
        ];

        // Build minimal required user data from Moodle profile.
        $userdata = [
            'Email' => $user->email,
            'Name' => fullname($user),
            'Phone' => !empty($user->phone1) ? $user->phone1 : (!empty($user->phone2) ? $user->phone2 : '0000000000'),
            'BillingEmail' => $user->email,
            'BillingName' => fullname($user),
            'BillingPhone' => !empty($user->phone1) ? $user->phone1 : (!empty($user->phone2) ? $user->phone2 : '0000000000'),
            'BillingCity' => !empty($user->city) ? $user->city : 'N/A',
            'BillingCountry' => !empty($user->country) ? $user->country : 'RO',
            'ShippingEmail' => $user->email,
            'ShippingName' => fullname($user),
            'ShippingAddress' => !empty($user->address) ? $user->address : 'N/A',
            'ShippingPhone' => !empty($user->phone1) ? $user->phone1 : (!empty($user->phone2) ? $user->phone2 : '0000000000'),
            'ShippingCity' => !empty($user->city) ? $user->city : 'N/A',
            'ShippingCountry' => !empty($user->country) ? $user->country : 'RO',
        ];

        $data = [
            'ProductsData' => $productsdata,
            'UserData' => $userdata,
        ];

        // Use JSON encoding for security (avoids object injection risks).
        return base64_encode(json_encode($data));
    }

    /**
     * Compute P_SIGN for authorization request.
     * Uses HMAC-SHA1 algorithm as per LibraPay documentation.
     *
     * @param array $fields Array of field values in the correct order
     * @return string 40-character uppercase hex string
     */
    public function compute_psign_request(array $fields): string {
        $message = $this->build_psign_message($fields);
        $key = pack('H*', $this->config->encryptionkey);
        $hash = hash_hmac('sha1', $message, $key);
        return strtoupper($hash);
    }

    /**
     * Compute P_SIGN for authorization response validation.
     *
     * @param array $fields Array of field values in the correct order
     * @return string 40-character uppercase hex string
     */
    public function compute_psign_response(array $fields): string {
        return $this->compute_psign_request($fields);
    }

    /**
     * Build the P_SIGN message string.
     * Each field is prefixed with its length, NULL values become "-".
     *
     * @param array $fields
     * @return string
     */
    private function build_psign_message(array $fields): string {
        $message = '';
        foreach ($fields as $value) {
            if ($value === null || $value === '') {
                $message .= '-';
            } else {
                $message .= strlen($value) . $value;
            }
        }
        return $message;
    }

    /**
     * Build request P_SIGN fields array for authorization request.
     *
     * @param string $amount
     * @param string $order
     * @param string $desc
     * @param string $timestamp
     * @param string $nonce
     * @param string $backref
     * @return string
     */
    public function compute_request_psign(
        string $amount,
        string $order,
        string $desc,
        string $timestamp,
        string $nonce,
        string $backref
    ): string {
        // Fields must be in this exact order for P_SIGN computation.
        $fields = [
            $amount,
            'RON',
            $order,
            $desc,
            $this->config->merchname,
            $this->config->merchurl,
            $this->config->merchant,
            $this->config->terminal,
            $this->config->email,
            '0',
            null,
            null,
            $timestamp,
            $nonce,
            $backref,
        ];

        return $this->compute_psign_request($fields);
    }

    /**
     * Validate response P_SIGN from LibraPay.
     *
     * @param array $response Response data from LibraPay
     * @return bool
     */
    public function validate_response_psign(array $response): bool {
        $fields = [
            $response['TERMINAL'] ?? '',
            $response['TRTYPE'] ?? '0',
            $response['ORDER'] ?? '',
            $response['AMOUNT'] ?? '',
            $response['CURRENCY'] ?? 'RON',
            $response['DESC'] ?? '',
            $response['ACTION'] ?? '',
            $response['RC'] ?? '',
            $response['MESSAGE'] ?? '',
            $response['RRN'] ?? '',
            $response['INT_REF'] ?? '',
            $response['APPROVAL'] ?? '',
            $response['TIMESTAMP'] ?? '',
            $response['NONCE'] ?? '',
        ];

        $computedpsign = $this->compute_psign_response($fields);
        $receivedpsign = strtoupper($response['P_SIGN'] ?? '');

        return hash_equals($computedpsign, $receivedpsign);
    }

    /**
     * Check if payment was approved.
     *
     * @param array $response
     * @return bool
     */
    public function is_payment_approved(array $response): bool {
        // ACTION=0 means approved, RC=00 means authorized.
        return ($response['ACTION'] ?? '') === '0' && ($response['RC'] ?? '') === '00';
    }

    /**
     * Save transaction to database.
     *
     * @param string $orderid
     * @param int $userid
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @param float $amount
     * @param array $response LibraPay response data
     * @return int Transaction ID
     */
    public function save_transaction(
        string $orderid,
        int $userid,
        string $component,
        string $paymentarea,
        int $itemid,
        float $amount,
        array $response
    ): int {
        global $DB;

        $transaction = new \stdClass();
        $transaction->orderid = $orderid;
        $transaction->userid = $userid;
        $transaction->component = $component;
        $transaction->paymentarea = $paymentarea;
        $transaction->itemid = $itemid;
        $transaction->amount = $amount;
        $transaction->currency = 'RON';
        $transaction->action = $response['ACTION'] ?? '';
        $transaction->rc = $response['RC'] ?? '';
        $transaction->message = $response['MESSAGE'] ?? '';
        $transaction->rrn = $response['RRN'] ?? '';
        $transaction->intref = $response['INT_REF'] ?? '';
        $transaction->approval = $response['APPROVAL'] ?? '';
        $transaction->timecreated = time();

        return $DB->insert_record('paygw_librapay_transactions', $transaction);
    }

    /**
     * Check if a transaction already exists (prevent replay).
     *
     * @param string $orderid
     * @return bool
     */
    public function transaction_exists(string $orderid): bool {
        global $DB;
        return $DB->record_exists('paygw_librapay_transactions', ['orderid' => $orderid]);
    }

    /**
     * Deliver the course/product to the user.
     *
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @param int $userid
     * @param float $amount
     */
    public function deliver_order(string $component, string $paymentarea, int $itemid, int $userid, float $amount): void {
        $payable = helper::get_payable($component, $paymentarea, $itemid);
        $paymentid = helper::save_payment(
            $payable->get_account_id(),
            $component,
            $paymentarea,
            $itemid,
            $userid,
            $amount,
            'RON',
            'librapay'
        );
        helper::deliver_order($component, $paymentarea, $itemid, $paymentid, $userid);
    }

    /**
     * Send notification to user about payment status.
     *
     * @param int $userid User ID to notify
     * @param string $status Payment status ('successful' or 'failed')
     * @param array $data Notification data (amount, currency, description, orderid, url)
     */
    public function notify_user(int $userid, string $status, array $data): void {
        $message = new \core\message\message();
        $message->component = 'paygw_librapay';
        $message->name = 'payment_' . $status;
        $message->userfrom = \core_user::get_noreply_user();
        $message->userto = $userid;
        $message->subject = get_string('payment:' . $status . ':subject', 'paygw_librapay');
        $message->fullmessage = get_string('payment:' . $status . ':message', 'paygw_librapay', (object) $data);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = '';
        $message->smallmessage = $message->subject;
        $message->notification = 1;
        $message->contexturl = $data['url'] ?? '';
        $message->contexturlname = get_string('paymentsuccessful', 'paygw_librapay');

        message_send($message);
    }
}
