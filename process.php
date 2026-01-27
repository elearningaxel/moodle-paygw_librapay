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
 * Process payment response from LibraPay (BACKREF callback).
 *
 * @package    paygw_librapay
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_payment\helper;
use paygw_librapay\librapay_helper;

// @codingStandardsIgnoreLine
require_once(__DIR__ . '/../../../config.php'); // No login check - this is a callback URL from LibraPay.

// Set page context for email notifications.
$PAGE->set_context(context_system::instance());

$component = required_param('component', PARAM_ALPHANUMEXT);
$paymentarea = required_param('paymentarea', PARAM_ALPHANUMEXT);
$itemid = required_param('itemid', PARAM_INT);
$token = required_param('token', PARAM_ALPHANUM);

// LibraPay sends response via GET or POST.
// Use optional_param() to safely retrieve and sanitize all response fields.
$response = [
    'TERMINAL' => optional_param('TERMINAL', '', PARAM_ALPHANUMEXT),
    'TRTYPE' => optional_param('TRTYPE', '', PARAM_ALPHANUMEXT),
    'ORDER' => optional_param('ORDER', '', PARAM_ALPHANUMEXT),
    'AMOUNT' => optional_param('AMOUNT', '', PARAM_RAW),
    'CURRENCY' => optional_param('CURRENCY', '', PARAM_ALPHA),
    'DESC' => optional_param('DESC', '', PARAM_TEXT),
    'ACTION' => optional_param('ACTION', null, PARAM_RAW),
    'RC' => optional_param('RC', '', PARAM_ALPHANUMEXT),
    'MESSAGE' => optional_param('MESSAGE', '', PARAM_TEXT),
    'RRN' => optional_param('RRN', '', PARAM_ALPHANUMEXT),
    'INT_REF' => optional_param('INT_REF', '', PARAM_ALPHANUMEXT),
    'APPROVAL' => optional_param('APPROVAL', '', PARAM_ALPHANUMEXT),
    'TIMESTAMP' => optional_param('TIMESTAMP', '', PARAM_ALPHANUMEXT),
    'NONCE' => optional_param('NONCE', '', PARAM_ALPHANUMEXT),
    'P_SIGN' => optional_param('P_SIGN', '', PARAM_ALPHANUMEXT),
];

// Verify we have the minimum required response fields.
// Note: Cannot use empty() for ACTION because '0' means approved and empty('0') returns true.
if (
    $response['ORDER'] === '' ||
    $response['ACTION'] === null || $response['ACTION'] === '' ||
    $response['P_SIGN'] === ''
) {
    redirect(
        new moodle_url('/'),
        get_string('invalidresponse', 'paygw_librapay'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

// Look up pending transaction from database using ORDER ID and token.
$pending = $DB->get_record('paygw_librapay_transactions', [
    'orderid' => $response['ORDER'],
    'token' => $token,
    'status' => 'pending',
]);

if (!$pending) {
    // Check if already processed.
    $existing = $DB->get_record('paygw_librapay_transactions', [
        'orderid' => $response['ORDER'],
        'status' => 'completed',
    ]);
    if ($existing) {
        redirect(
            new moodle_url('/'),
            get_string('transactionalreadyprocessed', 'paygw_librapay'),
            null,
            \core\output\notification::NOTIFY_WARNING
        );
    }
    redirect(
        new moodle_url('/'),
        get_string('sessionmismatch', 'paygw_librapay'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

// Verify the request parameters match the pending transaction.
if ($pending->component !== $component || $pending->paymentarea !== $paymentarea || (int)$pending->itemid !== $itemid) {
    redirect(
        new moodle_url('/'),
        get_string('sessionmismatch', 'paygw_librapay'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

// Get gateway configuration.
$config = (object) helper::get_gateway_configuration($component, $paymentarea, $itemid, 'librapay');
$librapayhelper = new librapay_helper($config);

// Validate P_SIGN to ensure response is authentic.
if (!$librapayhelper->validate_response_psign($response)) {
    redirect(
        new moodle_url('/'),
        get_string('invalidsignature', 'paygw_librapay'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

// Update transaction record with response data.
$pending->action = $response['ACTION'] ?? '';
$pending->rc = $response['RC'] ?? '';
$pending->message = $response['MESSAGE'] ?? '';
$pending->rrn = $response['RRN'] ?? '';
$pending->intref = $response['INT_REF'] ?? '';
$pending->approval = $response['APPROVAL'] ?? '';

// Get success URL for notifications.
$successurl = helper::get_success_url($component, $paymentarea, $itemid);

// Build notification data.
$notificationdata = [
    'amount' => number_format($pending->amount, 2),
    'currency' => 'RON',
    'description' => $response['DESC'] ?? '',
    'orderid' => $pending->orderid,
    'url' => $successurl->out(false),
];

// Check if payment was approved.
if ($librapayhelper->is_payment_approved($response)) {
    // Payment successful - mark as completed and deliver the order.
    $pending->status = 'completed';
    $DB->update_record('paygw_librapay_transactions', $pending);

    // Deliver the order to the user.
    $librapayhelper->deliver_order($component, $paymentarea, $itemid, $pending->userid, $pending->amount);

    // Send success notification to user.
    $librapayhelper->notify_user($pending->userid, 'successful', $notificationdata);

    // Redirect to success URL.
    redirect($successurl, get_string('paymentsuccessful', 'paygw_librapay'), 0, 'success');
} else {
    // Payment failed - mark as failed.
    $pending->status = 'failed';
    $DB->update_record('paygw_librapay_transactions', $pending);

    // Send failure notification to user.
    $librapayhelper->notify_user($pending->userid, 'failed', $notificationdata);

    // Map common error codes to user-friendly messages.
    $errormessage = $response['MESSAGE'] ?? get_string('paymentfailed', 'paygw_librapay');
    $action = $response['ACTION'] ?? '';
    switch ($action) {
        case '1':
            $errormessage = get_string('duplicatetransaction', 'paygw_librapay');
            break;
        case '2':
            $errormessage = get_string('transactiondenied', 'paygw_librapay');
            break;
        case '3':
            $errormessage = get_string('processingerror', 'paygw_librapay');
            break;
    }

    redirect(
        new moodle_url('/'),
        $errormessage,
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}
