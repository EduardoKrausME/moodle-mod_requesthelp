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
 * lib.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * requesthelp_supports
 *
 * @param $feature
 * @return string|true|null
 */
function requesthelp_supports($feature) {
    return match ($feature) {
        FEATURE_MOD_INTRO => true,
        FEATURE_SHOW_DESCRIPTION => true,
        FEATURE_BACKUP_MOODLE2 => true,
        FEATURE_COMPLETION_TRACKS_VIEWS => true,
        FEATURE_MOD_PURPOSE => MOD_PURPOSE_COMMUNICATION,
        default => null,
    };
}

/**
 * requesthelp_add_instance
 *
 * @param $data
 * @param $mform
 * @return bool|int
 * @throws dml_exception
 */
function requesthelp_add_instance($data, $mform = null) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $data->subjects = trim((string)$data->subjects);
    return $DB->insert_record("requesthelp", $data);
}

/**
 * requesthelp_update_instance
 *
 * @param $data
 * @param $mform
 * @return bool
 * @throws dml_exception
 */
function requesthelp_update_instance($data, $mform = null) {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();
    $data->subjects = trim((string)$data->subjects);
    return $DB->update_record("requesthelp", $data);
}

/**
 * requesthelp_delete_instance
 *
 * @param $id
 * @return bool
 * @throws coding_exception
 * @throws dml_exception
 */
function requesthelp_delete_instance($id) {
    global $DB;

    if (!$instance = $DB->get_record("requesthelp", ["id" => $id])) {
        return false;
    }

    $requestids = $DB->get_fieldset_select("requesthelp_requests", "id", "requesthelpid = :id", ["id" => $id]);
    if ($requestids) {
        [$insql, $params] = $DB->get_in_or_equal($requestids, SQL_PARAMS_NAMED);
        $DB->delete_records_select("requesthelp_messages", "requestid {$insql}", $params);
    }
    $DB->delete_records("requesthelp_requests", ["requesthelpid" => $id]);
    $DB->delete_records("requesthelp", ["id" => $id]);
    return true;
}
