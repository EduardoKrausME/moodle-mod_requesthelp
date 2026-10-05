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
 * Privacy provider for Request help.
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_requesthelp\privacy;

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Describe stored personal data.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table("requesthelp_requests", [
            "userid" => "privacy:metadata:requesthelp_requests:userid",
            "subject" => "privacy:metadata:requesthelp_requests:subject",
            "description" => "privacy:metadata:requesthelp_requests:description",
            "assignedto" => "privacy:metadata:requesthelp_requests:assignedto",
            "timecreated" => "privacy:metadata:requesthelp_requests:timecreated",
        ], "privacy:metadata:requesthelp_requests");
        $collection->add_database_table("requesthelp_messages", [
            "userid" => "privacy:metadata:requesthelp_messages:userid",
            "message" => "privacy:metadata:requesthelp_messages:message",
            "timecreated" => "privacy:metadata:requesthelp_messages:timecreated",
        ], "privacy:metadata:requesthelp_messages");
        return $collection;
    }

    /**
     * Get contexts containing data for a user.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {requesthelp} rh ON rh.id = cm.instance
             LEFT JOIN {requesthelp_requests} r ON r.requesthelpid = rh.id
             LEFT JOIN {requesthelp_messages} msg ON msg.requestid = r.id
                 WHERE r.userid = :requestuserid
                    OR r.assignedto = :assigneduserid
                    OR msg.userid = :messageuserid";
        $contextlist->add_from_sql($sql, [
            "contextlevel" => CONTEXT_MODULE,
            "modname" => "requesthelp",
            "requestuserid" => $userid,
            "assigneduserid" => $userid,
            "messageuserid" => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Get users with data in a module context.
     *
     * @param userlist $userlist User list.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }

        $cm = get_coursemodule_from_id("requesthelp", $context->instanceid);
        if (!$cm) {
            return;
        }

        $sql = "SELECT userid
                  FROM {requesthelp_requests}
                 WHERE requesthelpid = :ownerinstance
                   AND userid > 0
                 UNION
                SELECT assignedto AS userid
                  FROM {requesthelp_requests}
                 WHERE requesthelpid = :assignedinstance
                   AND assignedto > 0
                 UNION
                SELECT msg.userid
                  FROM {requesthelp_messages} msg
                  JOIN {requesthelp_requests} r ON r.id = msg.requestid
                 WHERE r.requesthelpid = :messageinstance
                   AND msg.userid > 0";
        $userlist->add_from_sql("userid", $sql, [
            "ownerinstance" => $cm->instance,
            "assignedinstance" => $cm->instance,
            "messageinstance" => $cm->instance,
        ]);
    }

    /**
     * Export data belonging to a user.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id("requesthelp", $context->instanceid);
            if (!$cm) {
                continue;
            }

            $requests = $DB->get_records(
                "requesthelp_requests",
                ["requesthelpid" => $cm->instance, "userid" => $userid],
                "timecreated ASC"
            );
            foreach ($requests as $request) {
                $data = (object)[
                    "subject" => $request->subject,
                    "description" => $request->description,
                    "status" => $request->status,
                    "timecreated" => transform::datetime($request->timecreated),
                ];
                writer::with_context($context)->export_data(
                    [get_string("requestsummary", "mod_requesthelp", $request->id)],
                    $data
                );
            }

            $assignedrequests = $DB->get_records(
                "requesthelp_requests",
                ["requesthelpid" => $cm->instance, "assignedto" => $userid],
                "timecreated ASC"
            );
            foreach ($assignedrequests as $request) {
                $data = (object)[
                    "subject" => $request->subject,
                    "status" => $request->status,
                    "timecreated" => transform::datetime($request->timecreated),
                ];
                writer::with_context($context)->export_data(
                    [
                        get_string("assignedrequests", "mod_requesthelp"),
                        get_string("requestsummary", "mod_requesthelp", $request->id),
                    ],
                    $data
                );
            }

            $sql = "SELECT msg.*
                      FROM {requesthelp_messages} msg
                      JOIN {requesthelp_requests} r ON r.id = msg.requestid
                     WHERE r.requesthelpid = :instanceid
                       AND msg.userid = :userid
                  ORDER BY msg.timecreated ASC, msg.id ASC";
            $messages = $DB->get_records_sql($sql, [
                "instanceid" => $cm->instance,
                "userid" => $userid,
            ]);
            foreach ($messages as $message) {
                $data = (object)[
                    "message" => $message->message,
                    "isstaff" => (bool)$message->isstaff,
                    "timecreated" => transform::datetime($message->timecreated),
                ];
                writer::with_context($context)->export_data(
                    [
                        get_string("requestsummary", "mod_requesthelp", $message->requestid),
                        get_string("conversation", "mod_requesthelp"),
                        get_string("messagesummary", "mod_requesthelp", $message->id),
                    ],
                    $data
                );
            }
        }
    }

    /**
     * Delete all user data in a context.
     *
     * @param context $context Context.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;

        if (!$context instanceof context_module) {
            return;
        }

        $cm = get_coursemodule_from_id("requesthelp", $context->instanceid);
        if (!$cm) {
            return;
        }

        $requestids = $DB->get_fieldset_select(
            "requesthelp_requests",
            "id",
            "requesthelpid = :id",
            ["id" => $cm->instance]
        );
        if ($requestids) {
            [$insql, $params] = $DB->get_in_or_equal($requestids, SQL_PARAMS_NAMED);
            $DB->delete_records_select("requesthelp_messages", "requestid {$insql}", $params);
        }
        $DB->delete_records("requesthelp_requests", ["requesthelpid" => $cm->instance]);
    }

    /**
     * Delete data for one user.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id("requesthelp", $context->instanceid);
            if (!$cm) {
                continue;
            }

            $activityrequestids = $DB->get_fieldset_select(
                "requesthelp_requests",
                "id",
                "requesthelpid = :instanceid",
                ["instanceid" => $cm->instance]
            );
            if ($activityrequestids) {
                [$activitysql, $activityparams] = $DB->get_in_or_equal(
                    $activityrequestids,
                    SQL_PARAMS_NAMED,
                    "ar"
                );
                $activityparams["messageuserid"] = $userid;
                $DB->delete_records_select(
                    "requesthelp_messages",
                    "requestid {$activitysql} AND userid = :messageuserid",
                    $activityparams
                );
            }

            $ownedrequestids = $DB->get_fieldset_select(
                "requesthelp_requests",
                "id",
                "requesthelpid = :instanceid AND userid = :userid",
                ["instanceid" => $cm->instance, "userid" => $userid]
            );
            if ($ownedrequestids) {
                [$ownedsql, $ownedparams] = $DB->get_in_or_equal(
                    $ownedrequestids,
                    SQL_PARAMS_NAMED,
                    "or"
                );
                $DB->delete_records_select("requesthelp_messages", "requestid {$ownedsql}", $ownedparams);
                $DB->delete_records_select("requesthelp_requests", "id {$ownedsql}", $ownedparams);
            }

            $DB->set_field(
                "requesthelp_requests",
                "assignedto",
                0,
                ["requesthelpid" => $cm->instance, "assignedto" => $userid]
            );
        }
    }

    /**
     * Delete data for a set of approved users in one context.
     *
     * @param approved_userlist $userlist Approved users.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }

        $cm = get_coursemodule_from_id("requesthelp", $context->instanceid);
        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();
        if (!$userids) {
            return;
        }

        [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, "du");

        $activityrequestids = $DB->get_fieldset_select(
            "requesthelp_requests",
            "id",
            "requesthelpid = :instanceid",
            ["instanceid" => $cm->instance]
        );
        if ($activityrequestids) {
            [$activitysql, $activityparams] = $DB->get_in_or_equal(
                $activityrequestids,
                SQL_PARAMS_NAMED,
                "ar"
            );
            $DB->delete_records_select(
                "requesthelp_messages",
                "requestid {$activitysql} AND userid {$usersql}",
                $activityparams + $userparams
            );
        }

        $ownedparams = ["instanceid" => $cm->instance] + $userparams;
        $ownedrequestids = $DB->get_fieldset_select(
            "requesthelp_requests",
            "id",
            "requesthelpid = :instanceid AND userid {$usersql}",
            $ownedparams
        );
        if ($ownedrequestids) {
            [$ownedsql, $ownedidparams] = $DB->get_in_or_equal(
                $ownedrequestids,
                SQL_PARAMS_NAMED,
                "or"
            );
            $DB->delete_records_select("requesthelp_messages", "requestid {$ownedsql}", $ownedidparams);
            $DB->delete_records_select("requesthelp_requests", "id {$ownedsql}", $ownedidparams);
        }

        $DB->set_field_select(
            "requesthelp_requests",
            "assignedto",
            0,
            "requesthelpid = :assignedinstance AND assignedto {$usersql}",
            ["assignedinstance" => $cm->instance] + $userparams
        );
    }
}
