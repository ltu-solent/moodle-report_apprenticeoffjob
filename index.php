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
 * Staff data entry point for apprentice off job hours
 *
 * @package    report_apprenticeoffjob
 * @copyright  2020 onwards Solent University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

$id = optional_param('id', '', PARAM_INT);
$courseid = optional_param('courseid', '', PARAM_INT);
$id = ($id ? $id : $courseid);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

$PAGE->set_url('/report/apprenticeoffjob/index.php', array('id' => $id));
$PAGE->set_pagelayout('report');

require_login($course);

// Check permissions.
$coursecontext = context_course::instance($course->id);
require_capability('report/apprenticeoffjob:view', $coursecontext);

// Set page title and page heading.
$PAGE->set_title($course->shortname .': '. get_string('pluginname' , 'report_apprenticeoffjob'));
$PAGE->set_heading(get_string('pluginname', 'report_apprenticeoffjob'));

// Trigger a log viewed event.
$event = \report_apprenticeoffjob\event\report_viewed::create(array(
            'context' => $coursecontext,
            'userid' => $USER->id
          ));
$event->trigger();

// Displaying the page.
echo $OUTPUT->header();

echo get_string('userhelp', 'report_apprenticeoffjob');
$table = new \report_apprenticeoffjob\tables\summary($course->id);
$table->print_table();

echo $OUTPUT->footer();
