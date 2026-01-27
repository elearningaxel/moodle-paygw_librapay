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
use paygw_librapay\output\pay_redirect;

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

// Set up the page.
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/payment/gateway/librapay/pay.php');
$PAGE->set_pagelayout('redirect');
$PAGE->set_title(get_string('redirecting', 'paygw_librapay'));

// Create the renderable and output using the template.
$renderable = new pay_redirect(
    $librapayurl,
    $amount,
    'RON',
    $orderid,
    $desc,
    $config->terminal,
    $timestamp,
    $nonce,
    $backref->out(false),
    $datacustom,
    $psign
);

$output = $PAGE->get_renderer('paygw_librapay');
echo $OUTPUT->header();
echo $output->render_from_template('paygw_librapay/pay_redirect', $renderable->export_for_template($output));
echo $OUTPUT->footer();
