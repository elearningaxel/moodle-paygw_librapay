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
 * Strings for component 'paygw_librapay', language 'en'.
 *
 * @package    paygw_librapay
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['continuetopayment'] = 'Continue to payment';
$string['duplicatetransaction'] = 'This transaction has already been submitted.';
$string['email'] = 'Merchant email';
$string['email_help'] = 'Email address for receiving payment notifications from LibraPay.';
$string['encryptionkey'] = 'Encryption key';
$string['encryptionkey_help'] = 'The 32-character hexadecimal encryption key provided by LibraPay for P_SIGN computation.';
$string['gatewaydescription'] = 'LibraPay is an authorized payment gateway provider by Libra Internet Bank for processing card transactions in Romania.';
$string['gatewayname'] = 'LibraPay';
$string['invalidencryptionkey'] = 'Encryption key must be exactly 32 hexadecimal characters.';
$string['invalidmerchant'] = 'Merchant ID must be exactly 15 digits.';
$string['invalidresponse'] = 'Invalid response received from payment gateway.';
$string['invalidsignature'] = 'Payment verification failed. The response signature is invalid.';
$string['invalidterminal'] = 'Terminal ID must be exactly 8 digits.';
$string['merchant'] = 'Merchant ID';
$string['merchant_help'] = 'The 15-digit merchant identifier provided by LibraPay (format: 0000000 + Terminal).';
$string['merchname'] = 'Merchant name';
$string['merchname_help'] = 'Your merchant/business name as registered with LibraPay.';
$string['merchurl'] = 'Merchant URL';
$string['merchurl_help'] = 'Your website URL as registered with LibraPay.';
$string['messageprovider:payment_failed'] = 'Payment failed notification';
$string['messageprovider:payment_successful'] = 'Payment successful notification';
$string['noscript'] = 'JavaScript is required to continue. Please click the button below to proceed to payment.';
$string['payment:failed:message'] = 'Your payment of {$a->amount} {$a->currency} for "{$a->description}" failed. Please try again or contact support.';
$string['payment:failed:subject'] = 'Payment failed';
$string['payment:successful:message'] = 'Your payment of {$a->amount} {$a->currency} for "{$a->description}" was successful. Order ID: {$a->orderid}. You can now access your purchase at {$a->url}';
$string['payment:successful:subject'] = 'Payment successful - Receipt';
$string['paymentfailed'] = 'Payment failed. Please try again or contact support.';
$string['paymentpending'] = 'Payment is being processed. You will be notified once it is complete.';
$string['paymentsuccessful'] = 'Payment was successful. Thank you for your purchase!';
$string['pluginname'] = 'LibraPay';
$string['pluginname_desc'] = 'The LibraPay plugin allows you to receive payments via LibraPay (Libra Internet Bank).';
$string['privacy:metadata:paygw_librapay_transactions'] = 'Stores LibraPay payment transaction data.';
$string['privacy:metadata:paygw_librapay_transactions:amount'] = 'The payment amount.';
$string['privacy:metadata:paygw_librapay_transactions:orderid'] = 'The unique order ID for the transaction.';
$string['privacy:metadata:paygw_librapay_transactions:timecreated'] = 'The time the transaction was created.';
$string['privacy:metadata:paygw_librapay_transactions:userid'] = 'The ID of the user who made the payment.';
$string['processingerror'] = 'A processing error occurred. Please try again later.';
$string['redirecting'] = 'Redirecting to payment...';
$string['redirectingtolibrapay'] = 'You are being redirected to LibraPay to complete your payment. Please wait...';
$string['sessionmismatch'] = 'Session verification failed. Please try the payment again.';
$string['terminal'] = 'Terminal ID';
$string['terminal_help'] = 'The 8-digit terminal identifier provided by LibraPay.';
$string['testmode'] = 'Test mode';
$string['testmode_help'] = 'Use the LibraPay test environment (sandbox) for testing payments. Disable this for live production payments.';
$string['transactionalreadyprocessed'] = 'This transaction has already been processed.';
$string['transactiondenied'] = 'Transaction was denied by the bank. Please check your card details or try a different payment method.';
