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
 * get_queue.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_requesthelp\external;

use context_module;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use mod_requesthelp\manager;
use mod_requesthelp\status;

/**
 * Class get_queue.
 */
class get_queue extends external_api {
    /**
     * Method execute_parameters.
     *
     * @return external_function_parameters Return value.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            "cmid" => new external_value(PARAM_INT, "Course module ID"),
            "status" => new external_value(PARAM_INT, "Status filter", VALUE_DEFAULT, -1),
            "subject" => new external_value(PARAM_TEXT, "Subject filter", VALUE_DEFAULT, ""),
        ]);
    }

    /**
     * Method execute.
     *
     * @param int $cmid Parameter cmid.
     * @param int $status Parameter status.
     * @param string $subject Parameter subject.
     * @return array Return value.
     */
    public static function execute(int $cmid, int $status = -1, string $subject = ""): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            "cmid" => $cmid,
            "status" => $status,
            "subject" => $subject,
        ]);

        $cm = get_coursemodule_from_id("requesthelp", $params["cmid"], 0, false, MUST_EXIST);
        $instance = $DB->get_record("requesthelp", ["id" => $cm->instance], "*", MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability("mod/requesthelp:manage", $context);

        $manager = new manager($instance, $cm, $context);
        $requests = $manager->get_requests($params["status"], $params["subject"]);
        $rows = [];
        foreach ($requests as $request) {
            $rows[] = $manager->export_queue_row($request);
        }
        $stats = $manager->get_stats();

        return [
            "rows" => array_values($rows),
            "stats" => [
                "open" => $stats[status::OPEN],
                "answered" => $stats[status::ANSWERED],
                "resolved" => $stats[status::RESOLVED],
            ],
        ];
    }

    /**
     * Method execute_returns.
     *
     * @return external_single_structure Return value.
     */
    public static function execute_returns(): external_single_structure {
        $row = new external_single_structure([
            "id" => new external_value(PARAM_INT, "Request ID"),
            "subject" => new external_value(PARAM_RAW, "Formatted subject"),
            "description" => new external_value(PARAM_RAW, "Formatted description"),
            "student" => new external_value(PARAM_RAW, "Student full name"),
            "created" => new external_value(PARAM_RAW, "Formatted creation time"),
            "waiting" => new external_value(PARAM_RAW, "Formatted waiting time"),
            "status" => new external_value(PARAM_RAW, "Localized status label"),
            "statusclass" => new external_value(PARAM_ALPHANUMEXT, "Status CSS class"),
            "assigned" => new external_value(PARAM_RAW, "Assigned teacher name"),
            "url" => new external_value(PARAM_URL, "Request URL"),
            "canmarkanswered" => new external_value(PARAM_BOOL, "Whether it can be marked answered"),
            "canresolve" => new external_value(PARAM_BOOL, "Whether it can be resolved"),
            "canreopen" => new external_value(PARAM_BOOL, "Whether it can be reopened"),
            "sesskey" => new external_value(PARAM_RAW, "Session key"),
        ]);

        return new external_single_structure([
            "rows" => new external_multiple_structure($row),
            "stats" => new external_single_structure([
                "open" => new external_value(PARAM_INT, "Open requests"),
                "answered" => new external_value(PARAM_INT, "Answered requests"),
                "resolved" => new external_value(PARAM_INT, "Resolved requests"),
            ]),
        ]);
    }
}
