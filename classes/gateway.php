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
 * Contains class for LibraPay payment gateway.
 *
 * @package    paygw_librapay
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_librapay;

use core_payment\form\account_gateway;

/**
 * The gateway class for LibraPay payment gateway.
 *
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_payment\gateway {

    /**
     * Returns the list of currencies supported by LibraPay.
     * LibraPay only supports RON (Romanian Leu).
     *
     * @return string[]
     */
    public static function get_supported_currencies(): array {
        return ['RON'];
    }

    /**
     * Configuration form for the gateway instance.
     *
     * @param account_gateway $form
     */
    public static function add_configuration_to_gateway_form(account_gateway $form): void {
        $mform = $form->get_mform();

        $mform->addElement('advcheckbox', 'testmode', get_string('testmode', 'paygw_librapay'));
        $mform->setDefault('testmode', 1);
        $mform->addHelpButton('testmode', 'testmode', 'paygw_librapay');

        $mform->addElement('text', 'terminal', get_string('terminal', 'paygw_librapay'));
        $mform->setType('terminal', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('terminal', 'terminal', 'paygw_librapay');
        $mform->addRule('terminal', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'merchant', get_string('merchant', 'paygw_librapay'));
        $mform->setType('merchant', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('merchant', 'merchant', 'paygw_librapay');
        $mform->addRule('merchant', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'merchname', get_string('merchname', 'paygw_librapay'));
        $mform->setType('merchname', PARAM_TEXT);
        $mform->addHelpButton('merchname', 'merchname', 'paygw_librapay');
        $mform->addRule('merchname', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'merchurl', get_string('merchurl', 'paygw_librapay'));
        $mform->setType('merchurl', PARAM_URL);
        $mform->addHelpButton('merchurl', 'merchurl', 'paygw_librapay');
        $mform->addRule('merchurl', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'email', get_string('email', 'paygw_librapay'));
        $mform->setType('email', PARAM_EMAIL);
        $mform->addHelpButton('email', 'email', 'paygw_librapay');
        $mform->addRule('email', get_string('required'), 'required', null, 'client');

        $mform->addElement('passwordunmask', 'encryptionkey', get_string('encryptionkey', 'paygw_librapay'));
        $mform->setType('encryptionkey', PARAM_RAW);
        $mform->addHelpButton('encryptionkey', 'encryptionkey', 'paygw_librapay');
        $mform->addRule('encryptionkey', get_string('required'), 'required', null, 'client');
    }

    /**
     * Validates the gateway configuration form.
     *
     * @param account_gateway $form
     * @param \stdClass $data
     * @param array $files
     * @param array $errors form errors (passed by reference)
     */
    public static function validate_gateway_form(
        account_gateway $form,
        \stdClass $data,
        array $files,
        array &$errors
    ): void {
        if ($data->enabled) {
            if (empty($data->terminal)) {
                $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
            }
            if (empty($data->merchant)) {
                $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
            }
            if (empty($data->merchname)) {
                $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
            }
            if (empty($data->merchurl)) {
                $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
            }
            if (empty($data->email)) {
                $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
            }
            if (empty($data->encryptionkey)) {
                $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
            }
            // Validate terminal is 8 digits.
            if (!empty($data->terminal) && !preg_match('/^\d{8}$/', $data->terminal)) {
                $errors['terminal'] = get_string('invalidterminal', 'paygw_librapay');
            }
            // Validate merchant is 15 digits.
            if (!empty($data->merchant) && !preg_match('/^\d{15}$/', $data->merchant)) {
                $errors['merchant'] = get_string('invalidmerchant', 'paygw_librapay');
            }
            // Validate encryption key is 32 hex characters.
            if (!empty($data->encryptionkey) && !preg_match('/^[a-fA-F0-9]{32}$/', $data->encryptionkey)) {
                $errors['encryptionkey'] = get_string('invalidencryptionkey', 'paygw_librapay');
            }
        }
    }
}
