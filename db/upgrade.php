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
 * LibraPay payment gateway plugin upgrade script.
 *
 * @package    paygw_librapay
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the paygw_librapay plugin.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_paygw_librapay_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026012201) {
        // Add status field.
        $table = new xmldb_table('paygw_librapay_transactions');
        $field = new xmldb_field('status', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'pending', 'timecreated');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add token field.
        $field = new xmldb_field('token', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'status');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Update existing records to completed status.
        $sql = "UPDATE {paygw_librapay_transactions}
                   SET status = 'completed'
                 WHERE status = 'pending' AND action IS NOT NULL";
        $DB->execute($sql);

        upgrade_plugin_savepoint(true, 2026012201, 'paygw', 'librapay');
    }

    return true;
}
