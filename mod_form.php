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
 * mod_form.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("{$CFG->dirroot}/course/moodleform_mod.php");

/**
 * Class mod_requesthelp_mod_form.
 */
class mod_requesthelp_mod_form extends moodleform_mod {
    /**
     * Method definition.
     *
     * @return mixed Return value.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement("header", "general", get_string("general", "form"));
        $mform->addElement("text", "name", get_string("requesthelpname", "mod_requesthelp"), ["size" => 64]);
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", null, "required", null, "client");
        $mform->addRule("name", get_string("maximumchars", "", 1333), "maxlength", 1333, "client");

        $this->standard_intro_elements();

        $mform->addElement("html", html_writer::tag("h3", get_string("requestsettings", "mod_requesthelp")));
        $mform->addElement("textarea", "subjects", get_string("subjects", "mod_requesthelp"), ["rows" => 7, "cols" => 60]);
        $mform->setType("subjects", PARAM_TEXT);
        $mform->addHelpButton("subjects", "subjects", "mod_requesthelp");
        $mform->setDefault("subjects", get_string("defaultsubjects", "mod_requesthelp"));
        $mform->addRule("subjects", null, "required", null, "client");

        $mform->addElement("advcheckbox", "studentcanresolve", get_string("studentcanresolve", "mod_requesthelp"));
        $mform->setDefault("studentcanresolve", 1);

        $mform->addElement("advcheckbox", "studentcanreopen", get_string("studentcanreopen", "mod_requesthelp"));
        $mform->setDefault("studentcanreopen", 1);

        $mform->addElement("select", "refreshinterval", get_string("refreshinterval", "mod_requesthelp"), [
            0 => get_string("never"),
            5 => get_string("seconds", "mod_requesthelp", 5),
            10 => get_string("seconds", "mod_requesthelp", 10),
            15 => get_string("seconds", "mod_requesthelp", 15),
            30 => get_string("seconds", "mod_requesthelp", 30),
        ]);
        $mform->setDefault("refreshinterval", 10);
        $mform->addHelpButton("refreshinterval", "refreshinterval", "mod_requesthelp");

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
