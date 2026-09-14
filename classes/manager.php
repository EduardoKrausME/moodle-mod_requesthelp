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
 * manager.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_requesthelp;

use context_module;
use moodle_url;
use stdClass;

/**
 * Class manager.
 */
class manager {
    /**
     * Property instance.
     *
     * @var stdClass
     */
    private stdClass $instance;
    /**
     * Property cm.
     *
     * @var stdClass
     */
    private stdClass $cm;
    /**
     * Property context.
     *
     * @var context_module
     */
    private context_module $context;

    /**
     * Method __construct.
     *
     * @param stdClass $instance Parameter instance.
     * @param stdClass $cm Parameter cm.
     * @param context_module $context Parameter context.
     */
    public function __construct(stdClass $instance, stdClass $cm, context_module $context) {
        $this->instance = $instance;
        $this->cm = $cm;
        $this->context = $context;
    }

    /**
     * Method get_subjects.
     *
     * @return array Return value.
     */
    public function get_subjects(): array {
        $lines = preg_split('/\R/u', (string)$this->instance->subjects) ?: [];
        $subjects = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== "") {
                $subjects[$line] = $line;
            }
        }
        return array_values($subjects);
    }

    /**
     * Method create_request.
     *
     * @param int $userid Parameter userid.
     * @param string $subject Parameter subject.
     * @param string $description Parameter description.
     * @return int Return value.
     */
    public function create_request(int $userid, string $subject, string $description): int {
        global $DB;

        $subject = trim($subject);
        $description = trim($description);
        if ($subject === "" || !in_array($subject, $this->get_subjects(), true)) {
            throw new \moodle_exception("invalidsubject", "mod_requesthelp");
        }
        if ($description === "") {
            throw new \moodle_exception("descriptionrequired", "mod_requesthelp");
        }

        $now = time();
        $record = (object)[
            "requesthelpid" => $this->instance->id,
            "userid" => $userid,
            "subject" => $subject,
            "description" => $description,
            "status" => status::OPEN,
            "assignedto" => 0,
            "timecreated" => $now,
            "timefirstresponse" => 0,
            "timeresolved" => 0,
            "timemodified" => $now,
        ];
        $id = $DB->insert_record("requesthelp_requests", $record);

        $event = \mod_requesthelp\event\request_created::create([
            "objectid" => $id,
            "context" => $this->context,
            "userid" => $userid,
            "other" => ["requesthelpid" => $this->instance->id],
        ]);
        $event->trigger();
        return $id;
    }

    /**
     * Method get_request.
     *
     * @param int $id Parameter id.
     * @return stdClass Return value.
     */
    public function get_request(int $id): stdClass {
        global $DB;
        return $DB->get_record("requesthelp_requests", ["id" => $id, "requesthelpid" => $this->instance->id], "*", MUST_EXIST);
    }

    /**
     * Method get_user_requests.
     *
     * @param int $userid Parameter userid.
     * @return array Return value.
     */
    public function get_user_requests(int $userid): array {
        global $DB;
        return $DB->get_records("requesthelp_requests",
            ["requesthelpid" => $this->instance->id, "userid" => $userid], "timecreated DESC");
    }

    /**
     * Method get_requests.
     *
     * @param int $statusfilter Parameter statusfilter.
     * @param string $subjectfilter Parameter subjectfilter.
     * @return array Return value.
     */
    public function get_requests(int $statusfilter = -1, string $subjectfilter = ""): array {
        global $DB;

        $params = ["requesthelpid" => $this->instance->id];
        $where = "r.requesthelpid = :requesthelpid";
        if (in_array($statusfilter, [status::OPEN, status::ANSWERED, status::RESOLVED], true)) {
            $where .= " AND r.status = :status";
            $params["status"] = $statusfilter;
        }
        if ($subjectfilter !== "") {
            $where .= " AND r.subject = :subject";
            $params["subject"] = $subjectfilter;
        }

        $sql = "SELECT r.*, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, a.firstname AS afirstname, a.lastname AS alastname
                  FROM {requesthelp_requests} r
                  JOIN {user} u ON u.id = r.userid
             LEFT JOIN {user} a ON a.id = r.assignedto
                 WHERE {$where}
              ORDER BY CASE r.status WHEN 0 THEN 0 WHEN 1 THEN 1 ELSE 2 END,
                       r.timecreated ASC";
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Method get_stats.
     *
     * @return array Return value.
     */
    public function get_stats(): array {
        global $DB;
        $stats = [status::OPEN => 0, status::ANSWERED => 0, status::RESOLVED => 0];
        $records = $DB->get_records_sql(
            "SELECT status, COUNT(1) AS total FROM {requesthelp_requests} WHERE requesthelpid = :id GROUP BY status",
            ["id" => $this->instance->id]);
        foreach ($records as $record) {
            $stats[(int)$record->status] = (int)$record->total;
        }
        return $stats;
    }

    /**
     * Method claim_request.
     *
     * @param int $requestid Parameter requestid.
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    public function claim_request(int $requestid, int $userid): void {
        global $DB;
        $request = $this->get_request($requestid);
        $request->assignedto = $userid;
        $request->timemodified = time();
        $DB->update_record("requesthelp_requests", $request);
    }

    /**
     * Method mark_answered.
     *
     * @param int $requestid Parameter requestid.
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    public function mark_answered(int $requestid, int $userid): void {
        global $DB;
        $request = $this->get_request($requestid);
        $now = time();
        if (!$request->assignedto) {
            $request->assignedto = $userid;
        }
        if (!$request->timefirstresponse) {
            $request->timefirstresponse = $now;
        }
        $request->status = status::ANSWERED;
        $request->timeresolved = 0;
        $request->timemodified = $now;
        $DB->update_record("requesthelp_requests", $request);

        $event = \mod_requesthelp\event\request_answered::create([
            "objectid" => $request->id,
            "context" => $this->context,
            "userid" => $userid,
            "relateduserid" => $request->userid,
            "other" => ["requesthelpid" => $this->instance->id],
        ]);
        $event->trigger();
    }

    /**
     * Method set_status.
     *
     * @param int $requestid Parameter requestid.
     * @param int $newstatus Parameter newstatus.
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    public function set_status(int $requestid, int $newstatus, int $userid): void {
        global $DB;
        if (!in_array($newstatus, [status::OPEN, status::ANSWERED, status::RESOLVED], true)) {
            throw new \coding_exception("Invalid request help status");
        }
        $request = $this->get_request($requestid);
        $now = time();
        $request->status = $newstatus;
        $request->timemodified = $now;
        if ($newstatus === status::OPEN) {
            $request->timeresolved = 0;
        } else if ($newstatus === status::ANSWERED) {
            if (!$request->timefirstresponse) {
                $request->timefirstresponse = $now;
            }
            $request->timeresolved = 0;
        } else if ($newstatus === status::RESOLVED) {
            $request->timeresolved = $now;
            if (!$request->timefirstresponse && has_capability("mod/requesthelp:manage", $this->context, $userid)) {
                $request->timefirstresponse = $now;
            }
        }
        $DB->update_record("requesthelp_requests", $request);

        if ($newstatus === status::RESOLVED) {
            $event = \mod_requesthelp\event\request_resolved::create([
                "objectid" => $request->id,
                "context" => $this->context,
                "userid" => $userid,
                "relateduserid" => $request->userid,
                "other" => ["requesthelpid" => $this->instance->id],
            ]);
            $event->trigger();
        }
    }

    /**
     * Method add_message.
     *
     * @param int $requestid Parameter requestid.
     * @param int $userid Parameter userid.
     * @param string $message Parameter message.
     * @param bool $isstaff Parameter isstaff.
     * @return void Return value.
     */
    public function add_message(int $requestid, int $userid, string $message, bool $isstaff): void {
        global $DB;
        $request = $this->get_request($requestid);
        $message = trim($message);
        if ($message === "") {
            throw new \moodle_exception("messagerequired", "mod_requesthelp");
        }

        $now = time();
        $DB->insert_record("requesthelp_messages", (object)[
            "requestid" => $request->id,
            "userid" => $userid,
            "message" => $message,
            "isstaff" => $isstaff ? 1 : 0,
            "timecreated" => $now,
        ]);

        if ($isstaff) {
            if (!$request->assignedto) {
                $request->assignedto = $userid;
            }
            if (!$request->timefirstresponse) {
                $request->timefirstresponse = $now;
            }
            if ($request->status !== status::RESOLVED) {
                $request->status = status::ANSWERED;
            }
            $event = \mod_requesthelp\event\request_answered::create([
                "objectid" => $request->id,
                "context" => $this->context,
                "userid" => $userid,
                "relateduserid" => $request->userid,
                "other" => ["requesthelpid" => $this->instance->id],
            ]);
            $event->trigger();
        } else if ($request->status === status::ANSWERED) {
            $request->status = status::OPEN;
        }
        $request->timemodified = $now;
        $DB->update_record("requesthelp_requests", $request);
    }

    /**
     * Method get_messages.
     *
     * @param int $requestid Parameter requestid.
     * @return array Return value.
     */
    public function get_messages(int $requestid): array {
        global $DB;
        $sql = "SELECT m.*, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename
                  FROM {requesthelp_messages} m
                  JOIN {user} u ON u.id = m.userid
                 WHERE m.requestid = :requestid
              ORDER BY m.timecreated ASC, m.id ASC";
        return $DB->get_records_sql($sql, ["requestid" => $requestid]);
    }

    /**
     * Method export_queue_row.
     *
     * @param stdClass $request Parameter request.
     * @return array Return value.
     */
    public function export_queue_row(stdClass $request): array {
        global $OUTPUT;
        $now = time();
        $waitseconds = ($request->timefirstresponse ?: $now) - $request->timecreated;
        $assigned = "";
        if (!empty($request->assignedto)) {
            $assigned = trim(($request->afirstname ?? "") . " " . ($request->alastname ?? ""));
        }
        return [
            "id" => $request->id,
            "subject" => format_string($request->subject),
            "description" => shorten_text(format_text($request->description, FORMAT_PLAIN), 180),
            "student" => fullname($request),
            "created" => userdate($request->timecreated, get_string("strftimedatetimeshort", "langconfig")),
            "waiting" => format_time(max(0, $waitseconds)),
            "status" => status::label((int)$request->status),
            "statusclass" => status::css((int)$request->status),
            "assigned" => $assigned,
            "url" => (new moodle_url("/mod/requesthelp/request.php", ["id" => $request->id]))->out(false),
            "canmarkanswered" => (int)$request->status === status::OPEN,
            "canresolve" => (int)$request->status !== status::RESOLVED,
            "canreopen" => (int)$request->status === status::RESOLVED,
            "sesskey" => sesskey(),
        ];
    }

    /**
     * Method export_student_row.
     *
     * @param stdClass $request Parameter request.
     * @return array Return value.
     */
    public function export_student_row(stdClass $request): array {
        $now = time();
        $waitseconds = ($request->timefirstresponse ?: $now) - $request->timecreated;
        return [
            "id" => $request->id,
            "subject" => format_string($request->subject),
            "created" => userdate($request->timecreated, get_string("strftimedatetimeshort", "langconfig")),
            "waiting" => format_time(max(0, $waitseconds)),
            "status" => status::label((int)$request->status),
            "statusclass" => status::css((int)$request->status),
            "url" => (new moodle_url("/mod/requesthelp/request.php", ["id" => $request->id]))->out(false),
        ];
    }

    /**
     * Method export_detail.
     *
     * @param stdClass $request Parameter request.
     * @param bool $isstaff Parameter isstaff.
     * @param int $userid Parameter userid.
     * @return array Return value.
     */
    public function export_detail(stdClass $request, bool $isstaff, int $userid): array {
        global $DB;
        $student = $DB->get_record("user", ["id" => $request->userid],
            "id,firstname,lastname,firstnamephonetic,lastnamephonetic,middlename,alternatename", MUST_EXIST);
        $assigned = "";
        if ($request->assignedto) {
            $assigneduser = $DB->get_record("user", ["id" => $request->assignedto],
                "id,firstname,lastname,firstnamephonetic,lastnamephonetic,middlename,alternatename");
            if ($assigneduser) {
                $assigned = fullname($assigneduser);
            }
        }
        $response = $request->timefirstresponse ? format_time($request->timefirstresponse - $request->timecreated) :
            get_string("waiting", "mod_requesthelp");
        return [
            "id" => $request->id,
            "subject" => format_string($request->subject),
            "description" => format_text($request->description, FORMAT_PLAIN),
            "student" => fullname($student),
            "created" => userdate($request->timecreated),
            "status" => status::label((int)$request->status),
            "statusclass" => status::css((int)$request->status),
            "responsetime" => $response,
            "assigned" => $assigned,
            "isstaff" => $isstaff,
            "canclaim" => $isstaff && !$request->assignedto,
            "canmarkanswered" => $isstaff && (int)$request->status === status::OPEN,
            "canresolve" => $request->status !== status::RESOLVED && ($isstaff || !empty($this->instance->studentcanresolve &&
                        (int)$request->userid === $userid)),
            "canreopen" => $request->status === status::RESOLVED && ($isstaff || !empty($this->instance->studentcanreopen &&
                        (int)$request->userid === $userid)),
            "backurl" => new moodle_url("/mod/requesthelp/view.php", ["id" => $this->cm->id]),
        ];
    }
}
