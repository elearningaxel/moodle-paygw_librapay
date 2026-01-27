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
 * Privacy provider for paygw_librapay.
 *
 * @package    paygw_librapay
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_librapay\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\plugin\provider as plugin_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\transform;

/**
 * Privacy provider for paygw_librapay.
 *
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    core_userlist_provider,
    metadata_provider,
    plugin_provider {
    /**
     * Returns metadata about this plugin's privacy policy.
     *
     * @param collection $collection The collection to add metadata to.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'paygw_librapay_transactions',
            [
                'userid' => 'privacy:metadata:paygw_librapay_transactions:userid',
                'orderid' => 'privacy:metadata:paygw_librapay_transactions:orderid',
                'amount' => 'privacy:metadata:paygw_librapay_transactions:amount',
                'timecreated' => 'privacy:metadata:paygw_librapay_transactions:timecreated',
            ],
            'privacy:metadata:paygw_librapay_transactions'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information.
     *
     * @param int $userid The user ID.
     * @return contextlist The list of contexts.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {paygw_librapay_transactions} t
                  JOIN {context} ctx ON ctx.contextlevel = :contextlevel AND ctx.instanceid = 0
                 WHERE t.userid = :userid";

        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_SYSTEM,
            'userid' => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing context and users.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        $sql = "SELECT userid FROM {paygw_librapay_transactions}";
        $userlist->add_from_sql('userid', $sql, []);
    }

    /**
     * Export all user data for the specified user.
     *
     * @param approved_contextlist $contextlist The list of approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;

        $transactions = $DB->get_records('paygw_librapay_transactions', ['userid' => $userid]);

        foreach ($transactions as $transaction) {
            $data = (object) [
                'orderid' => $transaction->orderid,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'status' => $transaction->action === '0' ? 'approved' : 'failed',
                'timecreated' => transform::datetime($transaction->timecreated),
            ];

            writer::with_context(\context_system::instance())->export_data(
                ['paygw_librapay', 'transactions', $transaction->id],
                $data
            );
        }
    }

    /**
     * Delete all user data for all users in the specified context.
     *
     * @param \context $context The context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        $DB->delete_records('paygw_librapay_transactions');
    }

    /**
     * Delete all user data for the specified user.
     *
     * @param approved_contextlist $contextlist The list of approved contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        $DB->delete_records('paygw_librapay_transactions', ['userid' => $userid]);
    }

    /**
     * Delete all user data for the specified users.
     *
     * @param approved_userlist $userlist The list of approved users.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $DB->delete_records_select('paygw_librapay_transactions', "userid $insql", $inparams);
    }
}
