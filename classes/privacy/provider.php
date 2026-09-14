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
 * provider.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_requesthelp\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Class provider.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Method get_metadata.
     *
     * @param collection $collection Parameter collection.
     * @return collection Return value.
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
     * Method get_contexts_for_userid.
     *
     * @param int $userid Parameter userid.
     * @return contextlist Return value.
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
                 WHERE r.userid = :requestuserid OR r.assignedto = :assigneduserid OR msg.userid = :messageuserid";
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
     * Method export_user_data.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return mixed Return value.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_MODULE) {
                continue;
            }
            $cm = get_coursemodule_from_id("requesthelp", $context->instanceid);
            if (!$cm) {
                continue;
            }
            $requests = $DB->get_records("requesthelp_requests",
                ["requesthelpid" => $cm->instance, "userid" => $userid], "timecreated ASC");
            foreach ($requests as $request) {
                $data = (object)[
                    "subject" => $request->subject,
                    "description" => $request->description,
                    "status" => $request->status,
                    "timecreated" => transform::datetime($request->timecreated),
                ];
                writer::with_context($context)->export_data([get_string("requestsummary", "mod_requesthelp", $request->id)], $data);
            }
        }
    }

    /**
     * Method delete_data_for_all_users_in_context.
     *
     * @param \context $context Parameter context.
     * @return mixed Return value.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel !== CONTEXT_MODULE) {
            return;
        }
        $cm = get_coursemodule_from_id("requesthelp", $context->instanceid);
        if (!$cm) {
            return;
        }
        $requestids = $DB->get_fieldset_select("requesthelp_requests", "id", "requesthelpid = :id", ["id" => $cm->instance]);
        if ($requestids) {
            list($insql, $params) = $DB->get_in_or_equal($requestids, SQL_PARAMS_NAMED);
            $DB->delete_records_select("requesthelp_messages", "requestid {$insql}", $params);
        }
        $DB->delete_records("requesthelp_requests", ["requesthelpid" => $cm->instance]);
    }

    /**
     * Method delete_data_for_user.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return mixed Return value.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_MODULE) {
                continue;
            }
            $cm = get_coursemodule_from_id("requesthelp", $context->instanceid);
            if (!$cm) {
                continue;
            }
            $activityrequestids = $DB->get_fieldset_select("requesthelp_requests", "id",
                "requesthelpid = :instanceid", ["instanceid" => $cm->instance]);
            if ($activityrequestids) {
                list($activitysql, $activityparams) = $DB->get_in_or_equal($activityrequestids, SQL_PARAMS_NAMED, "ar");
                $activityparams["messageuserid"] = $userid;
                $DB->delete_records_select("requesthelp_messages",
                    "requestid {$activitysql} AND userid = :messageuserid", $activityparams);
            }

            $ownedrequestids = $DB->get_fieldset_select("requesthelp_requests", "id",
                "requesthelpid = :instanceid AND userid = :userid", ["instanceid" => $cm->instance, "userid" => $userid]);
            if ($ownedrequestids) {
                list($ownedsql, $ownedparams) = $DB->get_in_or_equal($ownedrequestids, SQL_PARAMS_NAMED, "or");
                $DB->delete_records_select("requesthelp_messages", "requestid {$ownedsql}", $ownedparams);
                $DB->delete_records_select("requesthelp_requests", "id {$ownedsql}", $ownedparams);
            }

            $DB->set_field("requesthelp_requests", "assignedto", 0, ["requesthelpid" => $cm->instance, "assignedto" => $userid]);
        }
    }
}
