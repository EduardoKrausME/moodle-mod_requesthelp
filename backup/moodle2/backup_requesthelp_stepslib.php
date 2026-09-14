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
 * backup_requesthelp_stepslib.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_requesthelp_activity_structure_step extends backup_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value("userinfo");

        $requesthelp = new backup_nested_element("requesthelp", ["id"], [
            "name", "intro", "introformat", "subjects", "studentcanresolve", "studentcanreopen",
            "refreshinterval", "timecreated", "timemodified",
        ]);
        $requests = new backup_nested_element("requests");
        $request = new backup_nested_element("request", ["id"], [
            "userid", "subject", "description", "status", "assignedto", "timecreated",
            "timefirstresponse", "timeresolved", "timemodified",
        ]);
        $messages = new backup_nested_element("messages");
        $message = new backup_nested_element("message", ["id"], [
            "userid", "message", "isstaff", "timecreated",
        ]);

        $requesthelp->add_child($requests);
        $requests->add_child($request);
        $request->add_child($messages);
        $messages->add_child($message);

        $requesthelp->set_source_table("requesthelp", ["id" => backup::VAR_ACTIVITYID]);
        if ($userinfo) {
            $request->set_source_table("requesthelp_requests", ["requesthelpid" => backup::VAR_PARENTID]);
            $message->set_source_table("requesthelp_messages", ["requestid" => backup::VAR_PARENTID]);
        }

        $request->annotate_ids("user", "userid");
        $request->annotate_ids("user", "assignedto");
        $message->annotate_ids("user", "userid");
        $requesthelp->annotate_files("mod_requesthelp", "intro", null);

        return $this->prepare_activity_structure($requesthelp);
    }
}
