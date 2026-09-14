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
 * status.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_requesthelp;

/**
 * Class status.
 */
final class status {
    /** @var int */
    public const OPEN = 0;

    /** @var int */
    public const ANSWERED = 1;

    /** @var int */
    public const RESOLVED = 2;

    /**
     * Method label.
     *
     * @param int $status Parameter status.
     * @return string Return value.
     */
    public static function label(int $status): string {
        return match ($status) {
            self::ANSWERED => get_string("statusanswered", "mod_requesthelp"),
            self::RESOLVED => get_string("statusresolved", "mod_requesthelp"),
            default => get_string("statusopen", "mod_requesthelp"),
        };
    }

    /**
     * Method css.
     *
     * @param int $status Parameter status.
     * @return string Return value.
     */
    public static function css(int $status): string {
        return match ($status) {
            self::ANSWERED => "answered",
            self::RESOLVED => "resolved",
            default => "open",
        };
    }
}
