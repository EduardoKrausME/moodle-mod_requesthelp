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
 * restore_requesthelp_activity_task.class.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->dirroot}/mod/requesthelp/backup/moodle2/restore_requesthelp_stepslib.php");

/**
 * Class restore_requesthelp_activity_task.
 */
class restore_requesthelp_activity_task extends restore_activity_task {
    /**
     * Method define_my_settings.
     *
     * @return mixed Return value.
     */
    protected function define_my_settings() {
    }

    /**
     * Method define_my_steps.
     *
     * @return mixed Return value.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_requesthelp_activity_structure_step("requesthelp_structure", "requesthelp.xml"));
    }

    /**
     * Method define_decode_contents.
     *
     * @return mixed Return value.
     */
    public static function define_decode_contents() {
        return [new restore_decode_content("requesthelp", ["intro"], "requesthelp")];
    }

    /**
     * Method define_decode_rules.
     *
     * @return mixed Return value.
     */
    public static function define_decode_rules() {
        return [
            new restore_decode_rule("REQUESTHELPVIEWBYID", "/mod/requesthelp/view.php?id=$1", "course_module"),
            new restore_decode_rule("REQUESTHELPINDEX", "/mod/requesthelp/index.php?id=$1", "course"),
        ];
    }
}
