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
 * restore_requesthelp_stepslib.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_requesthelp_activity_structure_step extends restore_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $paths = [new restore_path_element("requesthelp", "/activity/requesthelp")];
        if ($this->get_setting_value("userinfo")) {
            $paths[] = new restore_path_element("requesthelp_request", "/activity/requesthelp/requests/request");
            $paths[] = new restore_path_element("requesthelp_message", "/activity/requesthelp/requests/request/messages/message");
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Method process_requesthelp.
     *
     * @param mixed $data Parameter data.
     * @return mixed Return value.
     */
    protected function process_requesthelp($data) {
        global $DB;
        $data = (object)$data;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record("requesthelp", $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Method process_requesthelp_request.
     *
     * @param mixed $data Parameter data.
     * @return mixed Return value.
     */
    protected function process_requesthelp_request($data) {
        global $DB;
        $data = (object)$data;
        $data->requesthelpid = $this->get_new_parentid("requesthelp");
        $data->userid = $this->get_mappingid("user", $data->userid, 0);
        $data->assignedto = $data->assignedto ? $this->get_mappingid("user", $data->assignedto, 0) : 0;
        $newitemid = $DB->insert_record("requesthelp_requests", $data);
        $this->set_mapping("requesthelp_request", $data->id, $newitemid);
    }

    /**
     * Method process_requesthelp_message.
     *
     * @param mixed $data Parameter data.
     * @return mixed Return value.
     */
    protected function process_requesthelp_message($data) {
        global $DB;
        $data = (object)$data;
        $data->requestid = $this->get_new_parentid("requesthelp_request");
        $data->userid = $this->get_mappingid("user", $data->userid, 0);
        $DB->insert_record("requesthelp_messages", $data);
    }

    /**
     * Method after_execute.
     *
     * @return mixed Return value.
     */
    protected function after_execute() {
        $this->add_related_files("mod_requesthelp", "intro", null);
    }
}
