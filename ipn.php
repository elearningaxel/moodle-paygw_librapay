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
 * IPN (Instant Payment Notification) handler for LibraPay.
 *
 * This endpoint receives asynchronous payment notifications from LibraPay.
 * LibraPay will retry until it receives "1" as response.
 *
 * @package    paygw_librapay
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// No Moodle session needed for IPN.
define('NO_MOODLE_COOKIES', true);

use core_payment\helper;
use paygw_librapay\librapay_helper;

require_once(__DIR__ . '/../../../config.php');

// IPN data comes via POST.
$response = [];
$responsefields = [
    'TERMINAL', 'TRTYPE', 'ORDER', 'AMOUNT', 'CURRENCY', 'DESC',
    'ACTION', 'RC', 'MESSAGE', 'RRN', 'INT_REF', 'APPROVAL',
    'TIMESTAMP', 'NONCE', 'P_SIGN',
];

foreach ($responsefields as $field) {
    if (isset($_POST[$field])) {
        $response[$field] = $_POST[$field];
    }
}

// Verify minimum required fields.
if (empty($response['ORDER']) || empty($response['ACTION']) || empty($response['P_SIGN'])) {
    // Invalid request - but don't echo "1" so LibraPay retries.
    http_response_code(400);
    exit;
}

// Look up the transaction to get component, paymentarea, itemid.
global $DB;
$transaction = $DB->get_record('paygw_librapay_transactions', ['orderid' => $response['ORDER']]);

if (!$transaction) {
    // Transaction not found - this might be a race condition where IPN arrives before sync callback.
    // Or it could be a completely new transaction that sync callback hasn't processed yet.
    // Respond with 400 to trigger retry.
    http_response_code(400);
    exit;
}

// Get gateway configuration.
try {
    $config = (object) helper::get_gateway_configuration(
        $transaction->component,
        $transaction->paymentarea,
        $transaction->itemid,
        'librapay'
    );
} catch (Exception $e) {
    http_response_code(500);
    exit;
}

$librapayhelper = new librapay_helper($config);

// Validate P_SIGN.
if (!$librapayhelper->validate_response_psign($response)) {
    http_response_code(400);
    exit;
}

// Update transaction status if changed.
$newaction = $response['ACTION'] ?? '';
if ($transaction->action !== $newaction) {
    $transaction->action = $newaction;
    $transaction->rc = $response['RC'] ?? '';
    $transaction->message = $response['MESSAGE'] ?? '';
    $transaction->rrn = $response['RRN'] ?? '';
    $transaction->intref = $response['INT_REF'] ?? '';
    $transaction->approval = $response['APPROVAL'] ?? '';
    $DB->update_record('paygw_librapay_transactions', $transaction);
}

// If payment is now approved and hasn't been delivered yet, deliver it.
if ($librapayhelper->is_payment_approved($response)) {
    // Check if payment was already delivered by looking for a record in the payments table.
    $paymentexists = $DB->record_exists('payments', [
        'component' => $transaction->component,
        'paymentarea' => $transaction->paymentarea,
        'itemid' => $transaction->itemid,
        'userid' => $transaction->userid,
        'gateway' => 'librapay',
    ]);

    if (!$paymentexists) {
        // Deliver the order.
        $librapayhelper->deliver_order(
            $transaction->component,
            $transaction->paymentarea,
            $transaction->itemid,
            $transaction->userid,
            $transaction->amount
        );
    }
}

// Acknowledge receipt to LibraPay.
echo '1';
