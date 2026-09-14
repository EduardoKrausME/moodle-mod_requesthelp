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
 * requesthelp.php
 *
 * @package   mod_requesthelp
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['actions'] = 'Actions';
$string['addinformation'] = 'Add information';
$string['additionalinformation'] = 'Additional information';
$string['allstatuses'] = 'All statuses';
$string['allsubjects'] = 'All subjects';
$string['askforhelp'] = 'I need help';
$string['assignedto'] = 'Assigned to';
$string['autorefresh'] = 'Queue updates automatically';
$string['claim'] = 'Take request';
$string['conversation'] = 'Conversation';
$string['created'] = 'Created';
$string['defaultsubjects'] = 'Lesson content
Exercise
Activity
Technical question
Other';
$string['description'] = 'Describe your question or problem';
$string['descriptionrequired'] = 'Describe the question or problem.';
$string['eventrequestanswered'] = 'Help request answered';
$string['eventrequestcreated'] = 'Help request created';
$string['eventrequestresolved'] = 'Help request resolved';
$string['filter'] = 'Filter';
$string['invalidsubject'] = 'The selected subject is not available in this activity.';
$string['markanswered'] = 'Answered in class';
$string['messagerequired'] = 'Write a message before sending.';
$string['modulename'] = 'Request help';
$string['modulename_help'] = 'Students submit questions to a central queue that teachers can answer in Moodle or in the classroom.';
$string['modulenameplural'] = 'Request help activities';
$string['myrequests'] = 'My requests';
$string['nonewmodules'] = 'There are no Request help activities in this course.';
$string['norequests'] = 'There are no requests in this queue.';
$string['noyourrequests'] = 'You have not requested help yet.';
$string['openrequests'] = 'Open requests';
$string['pluginadministration'] = 'Request help administration';
$string['pluginname'] = 'Request help';
$string['privacy:metadata:requesthelp_messages'] = 'Stores messages added to help requests.';
$string['privacy:metadata:requesthelp_messages:message'] = 'The message content.';
$string['privacy:metadata:requesthelp_messages:timecreated'] = 'When the message was created.';
$string['privacy:metadata:requesthelp_messages:userid'] = 'The author of the message.';
$string['privacy:metadata:requesthelp_requests'] = 'Stores help requests submitted by students.';
$string['privacy:metadata:requesthelp_requests:assignedto'] = 'The teacher assigned to the request, when applicable.';
$string['privacy:metadata:requesthelp_requests:description'] = 'The question or problem described by the student.';
$string['privacy:metadata:requesthelp_requests:subject'] = 'The selected subject.';
$string['privacy:metadata:requesthelp_requests:timecreated'] = 'When the request was submitted.';
$string['privacy:metadata:requesthelp_requests:userid'] = 'The user who submitted the help request.';
$string['queue'] = 'Help queue';
$string['refreshinterval'] = 'Automatic queue refresh';
$string['refreshinterval_help'] = 'How often the teacher queue is refreshed while the page is open. Set to Never to disable automatic refresh.';
$string['reopen'] = 'Reopen';
$string['requestcreated'] = 'Your request was added to the queue.';
$string['requesthelp:addinstance'] = 'Add a new Request help activity';
$string['requesthelp:manage'] = 'Manage and respond to help requests';
$string['requesthelp:submit'] = 'Submit help requests';
$string['requesthelp:view'] = 'View Request help activities';
$string['requesthelpname'] = 'Activity name';
$string['requestsettings'] = 'Help queue settings';
$string['requestsummary'] = 'Request #{$a}';
$string['resolve'] = 'Resolve';
$string['respond'] = 'Respond';
$string['response'] = 'Response';
$string['responsetime'] = 'First response time';
$string['seconds'] = '{$a} seconds';
$string['send'] = 'Send';
$string['sendrequest'] = 'Send request';
$string['status'] = 'Status';
$string['statusanswered'] = 'Answered';
$string['statusopen'] = 'Open';
$string['statusresolved'] = 'Resolved';
$string['student'] = 'Student';
$string['studentcanreopen'] = 'Allow students to reopen their own resolved requests';
$string['studentcanresolve'] = 'Allow students to mark their own requests as resolved';
$string['studentreply'] = 'Student';
$string['subject'] = 'Subject';
$string['subjects'] = 'Subjects';
$string['subjects_help'] = 'Enter one subject per line. Students choose one when asking for help.';
$string['teacherreply'] = 'Teacher';
$string['totalanswered'] = 'Answered';
$string['totalopen'] = 'Open';
$string['totalresolved'] = 'Resolved';
$string['viewrequest'] = 'View request';
$string['waiting'] = 'Waiting';
$string['waitingtime'] = 'Response time';
