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
 * request_form.php
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
 * Class request_form.
 */
class request_form extends moodleform {
    /**
     * Method definition.
     *
     * @return mixed Return value.
     */
    public function definition() {
        $mform = $this->_form;
        $subjects = $this->_customdata["subjects"] ?? [];
        $options = array_combine($subjects, $subjects) ?: [];

        $mform->addElement("select", "subject", get_string("subject", "mod_requesthelp"), $options);
        $mform->addRule("subject", null, "required", null, "client");

        $mform->addElement("textarea", "description", get_string("description", "mod_requesthelp"),
            ["rows" => 6, "cols" => 70, "maxlength" => 4000]);
        $mform->setType("description", PARAM_TEXT);
        $mform->addRule("description", null, "required", null, "client");

        $this->add_action_buttons(false, get_string("sendrequest", "mod_requesthelp"));
    }
}
