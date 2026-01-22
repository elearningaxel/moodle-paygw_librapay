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
 * Redirects to LibraPay for payment processing.
 *
 * @package    paygw_librapay
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_payment\helper;
use paygw_librapay\librapay_helper;

require_once(__DIR__ . '/../../../config.php');

require_login();

$component = required_param('component', PARAM_ALPHANUMEXT);
$paymentarea = required_param('paymentarea', PARAM_ALPHANUMEXT);
$itemid = required_param('itemid', PARAM_INT);
$description = urldecode(required_param('description', PARAM_TEXT));

$config = (object) helper::get_gateway_configuration($component, $paymentarea, $itemid, 'librapay');
$payable = helper::get_payable($component, $paymentarea, $itemid);
$surcharge = helper::get_gateway_surcharge('librapay');

$cost = helper::get_rounded_cost($payable->get_amount(), $payable->get_currency(), $surcharge);

$librapayhelper = new librapay_helper($config);

// Generate unique order ID and security tokens.
$orderid = $librapayhelper->generate_order_id();
$nonce = $librapayhelper->generate_nonce();
$timestamp = $librapayhelper->get_timestamp();
$amount = $librapayhelper->format_amount($cost);

// Generate verification token for callback (prevents session dependency).
$token = bin2hex(random_bytes(32));

// Build BACKREF URL - where LibraPay will redirect after payment.
// Include token for verification since session may be lost on cross-domain redirect.
$backref = new moodle_url('/payment/gateway/librapay/process.php', [
    'component' => $component,
    'paymentarea' => $paymentarea,
    'itemid' => $itemid,
    'token' => $token,
]);

// Truncate description to 50 chars max.
$desc = substr($description, 0, 50);

// Build DATA_CUSTOM with product and user info.
$datacustom = $librapayhelper->build_data_custom($description, $cost, $USER);

// Compute P_SIGN for the request.
$psign = $librapayhelper->compute_request_psign(
    $amount,
    $orderid,
    $desc,
    $timestamp,
    $nonce,
    $backref->out(false)
);

// Store pending transaction in database for validation on return.
// This avoids session dependency issues with cross-domain redirects.
global $DB;
$pending = new stdClass();
$pending->orderid = $orderid;
$pending->userid = $USER->id;
$pending->component = $component;
$pending->paymentarea = $paymentarea;
$pending->itemid = $itemid;
$pending->amount = $cost;
$pending->currency = 'RON';
$pending->timecreated = time();
$pending->status = 'pending';
$pending->token = $token;
$DB->insert_record('paygw_librapay_transactions', $pending);

// Get LibraPay URL.
$librapayurl = $librapayhelper->get_librapay_url();

// Escape values for HTML output.
$librapayurlesc = htmlspecialchars($librapayurl);
$amountesc = htmlspecialchars($amount);
$orderidesc = htmlspecialchars($orderid);
$descesc = htmlspecialchars($desc);
$terminalesc = htmlspecialchars($config->terminal);
$timestampesc = htmlspecialchars($timestamp);
$nonceesc = htmlspecialchars($nonce);
$backrefesc = htmlspecialchars($backref->out(false));
$datacustomesc = htmlspecialchars($datacustom);
$psignesc = htmlspecialchars($psign);

// Get language strings.
$redirectingstr = get_string('redirecting', 'paygw_librapay');
$redirectingtolibrapaystr = get_string('redirectingtolibrapay', 'paygw_librapay');
$noscriptstr = get_string('noscript', 'paygw_librapay');
$continuetopaymentstr = get_string('continuetopayment', 'paygw_librapay');

// Output auto-submit form.
echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$redirectingstr}</title>
</head>
<body>
    <p>{$redirectingtolibrapaystr}</p>
    <form id="librapay_form" method="post" action="{$librapayurlesc}">
        <input type="hidden" name="AMOUNT" value="{$amountesc}">
        <input type="hidden" name="CURRENCY" value="RON">
        <input type="hidden" name="ORDER" value="{$orderidesc}">
        <input type="hidden" name="DESC" value="{$descesc}">
        <input type="hidden" name="TERMINAL" value="{$terminalesc}">
        <input type="hidden" name="TIMESTAMP" value="{$timestampesc}">
        <input type="hidden" name="NONCE" value="{$nonceesc}">
        <input type="hidden" name="BACKREF" value="{$backrefesc}">
        <input type="hidden" name="DATA_CUSTOM" value="{$datacustomesc}">
        <input type="hidden" name="P_SIGN" value="{$psignesc}">
        <noscript>
            <p>{$noscriptstr}</p>
            <input type="submit" value="{$continuetopaymentstr}">
        </noscript>
    </form>
    <script>
        document.getElementById('librapay_form').submit();
    </script>
</body>
</html>
HTML;
