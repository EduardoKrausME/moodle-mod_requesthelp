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
 * view.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");

use core\output\notification;
use mod_requesthelp\form\request_form;
use mod_requesthelp\manager;
use mod_requesthelp\status;

$id = optional_param("id", 0, PARAM_INT);
$r = optional_param("r", 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id("requesthelp", $id, 0, false, MUST_EXIST);
    $course = get_course($cm->course);
    $instance = $DB->get_record("requesthelp", ["id" => $cm->instance], "*", MUST_EXIST);
} else {
    $instance = $DB->get_record("requesthelp", ["id" => $r], "*", MUST_EXIST);
    $course = get_course($instance->course);
    $cm = get_coursemodule_from_instance("requesthelp", $instance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/requesthelp:view", $context);

$manager = new manager($instance, $cm, $context);
$isstaff = has_capability("mod/requesthelp:manage", $context);

$PAGE->set_url(new moodle_url("/mod/requesthelp/view.php", ["id" => $cm->id]));
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->css("/mod/requesthelp/styles.css");

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

if (!$isstaff) {
    require_capability("mod/requesthelp:submit", $context);
    $form = new request_form(null, ["subjects" => $manager->get_subjects()]);
    if ($form->is_cancelled()) {
        redirect($PAGE->url);
    }
    if ($data = $form->get_data()) {
        require_sesskey();
        $requestid = $manager->create_request($USER->id, $data->subject, $data->description);
        redirect(new moodle_url("/mod/requesthelp/request.php", ["id" => $requestid]),
            get_string("requestcreated", "mod_requesthelp"), null, notification::NOTIFY_SUCCESS);
    }
}

echo $OUTPUT->header();

if ($isstaff) {
    $statusfilter = optional_param("status", -1, PARAM_INT);
    $subjectfilter = optional_param("subject", "", PARAM_TEXT);
    $requests = $manager->get_requests($statusfilter, $subjectfilter);
    $stats = $manager->get_stats();

    $subjectoptions = [
        [
            "value" => "",
            "label" => get_string("allsubjects", "mod_requesthelp"),
            "selected" => $subjectfilter === "",
        ],
    ];
    foreach ($manager->get_subjects() as $subject) {
        $subjectoptions[] = ["value" => $subject, "label" => $subject, "selected" => $subjectfilter === $subject];
    }

    $rows = [];
    foreach ($requests as $request) {
        $rows[] = $manager->export_queue_row($request);
    }

    $data = [
        "cmid" => $cm->id,
        "instanceid" => $instance->id,
        "title" => format_string($instance->name),
        "open" => $stats[status::OPEN],
        "answered" => $stats[status::ANSWERED],
        "resolved" => $stats[status::RESOLVED],
        "requests" => array_values($rows),
        "hasrequests" => !empty($rows),
        "subjects" => $subjectoptions,
        "statusall" => $statusfilter === -1,
        "statusopen" => $statusfilter === status::OPEN,
        "statusanswered" => $statusfilter === status::ANSWERED,
        "statusresolved" => $statusfilter === status::RESOLVED,
        "sesskey" => sesskey(),
    ];

    echo $OUTPUT->render_from_template("mod_requesthelp/teacher", $data);
    if ((int)$instance->refreshinterval > 0) {
        $PAGE->requires->js_call_amd("mod_requesthelp/queue", "init", [[
            "cmid" => $cm->id,
            "interval" => (int)$instance->refreshinterval,
            "status" => $statusfilter,
            "subject" => $subjectfilter,
        ]]);
    }
} else {
    echo $OUTPUT->heading(format_string($instance->name));
    if (trim((string)$instance->intro) !== "") {
        echo $OUTPUT->box(format_module_intro("requesthelp", $instance, $cm->id), "generalbox mod_introbox");
    }

    echo $OUTPUT->heading(get_string("askforhelp", "mod_requesthelp"), 3);
    $form->display();

    $requests = $manager->get_user_requests($USER->id);
    $rows = [];
    foreach ($requests as $request) {
        $rows[] = $manager->export_student_row($request);
    }
    echo $OUTPUT->render_from_template("mod_requesthelp/student", [
        "requests" => $rows,
        "hasrequests" => !empty($rows),
    ]);
}

echo $OUTPUT->footer();
