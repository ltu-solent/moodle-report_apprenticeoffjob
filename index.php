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
 * @package    report
 * @subpackage apprenticeoffjob
 * @copyright  2020 onwards Solent University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once('form.php');

$id = required_param('id', PARAM_INT);
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

$PAGE->set_url('/report/apprenticeoffjob/index.php', array('id'=>$id));
$PAGE->set_pagelayout('report');

require_login($course);

// Check permissions.
$coursecontext = context_course::instance($course->id);
require_capability('report/apprenticeoffjob:view', $coursecontext);

// Set page title and page heading.
$PAGE->set_title($course->shortname .': '. get_string('pluginname' , 'report_apprenticeoffjob'));
$PAGE->set_heading(get_string('pluginname', 'report_apprenticeoffjob'));

// Displaying the page.
echo $OUTPUT->header();

$hoursform = new offjobhours(null, array('courseid' => $course->id));
if ($hoursform->is_cancelled()) {
  redirect($CFG->wwwroot. '/local/apprenticeoffjob/index.php');
} else if ($formdata = $hoursform->get_data()) {
  $savehours = save_hours($formdata);
  redirect(new moodle_url('/report/apprenticeoffjob/index.php', ['id'=>$id]), get_string('activitysaved', 'local_apprenticeoffjob'), 15);
}

$hoursform->display();

echo $OUTPUT->footer();
