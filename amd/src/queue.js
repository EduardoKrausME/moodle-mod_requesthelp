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
 * queue.js
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery", "core/templates", "core/ajax"], function($, Templates, Ajax) {
    return {
        init: function(config) {
            var interval = parseInt(config.interval, 10) || 0;
            if (interval <= 0) {
                return;
            }

            var refresh = function() {
                var calls = Ajax.call([{
                    methodname: "mod_requesthelp_get_queue",
                    args: {
                        cmid: config.cmid,
                        status: config.status,
                        subject: config.subject
                    }
                }]);

                calls[0].then(function(data) {
                    var rows = data.rows || [];
                    Templates.render("mod_requesthelp/queue_rows", {
                        requests: rows,
                        hasrequests: rows.length > 0
                    }).then(function(html) {
                        $(`[data-region='queue']`).html(html);
                    }).catch(function() {});
                    if (data.stats) {
                        $(`[data-stat='open']`).text(data.stats.open);
                        $(`[data-stat='answered']`).text(data.stats.answered);
                        $(`[data-stat='resolved']`).text(data.stats.resolved);
                    }
                }).catch(function() {});
            };

            window.setInterval(refresh, interval * 1000);
        }
    };
});
