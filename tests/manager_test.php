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

/**
 * Tests for the request manager.
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_requesthelp;

/**
 * Tests for the request manager.
 */
final class manager_test extends \advanced_testcase {
    /**
     * Database status values are normalised before business logic compares them.
     *
     * @return void
     */
    public function test_status_is_normalised_for_message_transitions(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $instance = $this->getDataGenerator()->create_module("requesthelp", [
            "course" => $course->id,
            "subjects" => "General",
        ]);
        $cm = get_coursemodule_from_instance(
            "requesthelp",
            $instance->id,
            $course->id,
            false,
            MUST_EXIST
        );
        $context = \context_module::instance($cm->id);
        $manager = new manager($instance, $cm, $context);

        $requestid = $manager->create_request($user->id, "General", "I need help.");
        $manager->set_status($requestid, status::ANSWERED, $user->id);

        $request = $manager->get_request($requestid);
        $this->assertSame(status::ANSWERED, $request->status);

        $manager->add_message($requestid, $user->id, "More information.", false);
        $this->assertSame(status::OPEN, $manager->get_request($requestid)->status);

        $manager->set_status($requestid, status::RESOLVED, $user->id);
        $manager->add_message($requestid, $user->id, "Recorded teacher response.", true);
        $this->assertSame(status::RESOLVED, $manager->get_request($requestid)->status);
    }
}
