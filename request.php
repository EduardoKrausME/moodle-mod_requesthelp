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
 * request.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");

use mod_requesthelp\form\response_form;
use mod_requesthelp\manager;
use mod_requesthelp\status;

$id = required_param("id", PARAM_INT);
$action = optional_param("action", "", PARAM_ALPHA);

$request = $DB->get_record("requesthelp_requests", ["id" => $id], "*", MUST_EXIST);
$instance = $DB->get_record("requesthelp", ["id" => $request->requesthelpid], "*", MUST_EXIST);
$course = get_course($instance->course);
$cm = get_coursemodule_from_instance("requesthelp", $instance->id, $course->id, false, MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/requesthelp:view", $context);

$manager = new manager($instance, $cm, $context);
$isstaff = has_capability("mod/requesthelp:manage", $context);
if (!$isstaff && (int)$request->userid !== (int)$USER->id) {
    throw new required_capability_exception($context, "mod/requesthelp:manage", "nopermissions", "");
}

if ($action !== "") {
    require_sesskey();
    if ($isstaff) {
        if ($action === "claim") {
            $manager->claim_request($request->id, $USER->id);
        } else if ($action === "answered") {
            $manager->mark_answered($request->id, $USER->id);
        } else if ($action === "resolved") {
            $manager->set_status($request->id, status::RESOLVED, $USER->id);
        } else if ($action === "reopen") {
            $manager->set_status($request->id, status::OPEN, $USER->id);
        }
    } else {
        if ($action === "resolved" && !empty($instance->studentcanresolve)) {
            $manager->set_status($request->id, status::RESOLVED, $USER->id);
        } else if ($action === "reopen" && !empty($instance->studentcanreopen)) {
            $manager->set_status($request->id, status::OPEN, $USER->id);
        }
    }
    redirect(new moodle_url("/mod/requesthelp/request.php", ["id" => $request->id]));
}

$form = new response_form(null, ["isstaff" => $isstaff]);
if ($form->is_cancelled()) {
    redirect(new moodle_url("/mod/requesthelp/view.php", ["id" => $cm->id]));
}
if ($data = $form->get_data()) {
    require_sesskey();
    $manager->add_message($request->id, $USER->id, $data->message, $isstaff);
    redirect(new moodle_url("/mod/requesthelp/request.php", ["id" => $request->id]));
}

$request = $manager->get_request($request->id);
$messages = $manager->get_messages($request->id);

$PAGE->set_url(new moodle_url("/mod/requesthelp/request.php", ["id" => $request->id]));
$PAGE->set_title(format_string($request->subject));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->css("/mod/requesthelp/styles.css");

$messagerows = [];
foreach ($messages as $message) {
    $messagerows[] = [
        "fullname" => fullname($message),
        "time" => userdate($message->timecreated),
        "message" => format_text($message->message, FORMAT_PLAIN),
        "isstaff" => !empty($message->isstaff),
    ];
}

$data = $manager->export_detail($request, $isstaff, $USER->id);
$data["messages"] = $messagerows;
$data["hasmessages"] = !empty($messagerows);
$data["sesskey"] = sesskey();

echo $OUTPUT->header();
echo $OUTPUT->render_from_template("mod_requesthelp/request_detail", $data);

if ($request->status !== status::RESOLVED || $isstaff) {
    echo $OUTPUT->heading($isstaff ? get_string("respond", "mod_requesthelp") : get_string("addinformation", "mod_requesthelp"), 3);
    $form->display();
}

echo $OUTPUT->footer();
