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
 * response_form.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_requesthelp\form;

use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/formslib.php");

/**
 * Class response_form.
 */
class response_form extends moodleform {
    /**
     * Method definition.
     *
     * @return mixed Return value.
     */
    public function definition() {
        $mform = $this->_form;
        $isstaff = !empty($this->_customdata["isstaff"]);

        $name = $isstaff ? get_string("response", "mod_requesthelp") : get_string("additionalinformation", "mod_requesthelp");
        $mform->addElement("textarea", "message", $name, ["rows" => 5, "cols" => 70, "maxlength" => 4000]);
        $mform->setType("message", PARAM_TEXT);
        $mform->addRule("message", null, "required", null, "client");
        $this->add_action_buttons(true, get_string("send", "mod_requesthelp"));
    }
}
