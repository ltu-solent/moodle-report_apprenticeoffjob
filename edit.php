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
global $COURSE;

$studentid = required_param('studentid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

$PAGE->set_url('/report/apprenticeoffjob/edit.php', array('studentid'=>$studentid));
$PAGE->set_pagelayout('report');

require_login($courseid);

// Check permissions.
$coursecontext = context_course::instance($courseid);
require_capability('report/apprenticeoffjob:view', $coursecontext);
// Set page title and page heading.
$PAGE->set_title($course->shortname .': '. get_string('pluginname' , 'report_apprenticeoffjob'));
$PAGE->set_heading(get_string('pluginname', 'report_apprenticeoffjob'));

// Displaying the page.
echo $OUTPUT->header();

$fileoptions = array('maxbytes' => 41943040, 'maxfiles' => 1);
$data = new stdClass();
$data = file_prepare_standard_filemanager($data, 'apprenticeoffjob',
        $fileoptions, context_user::instance($studentid), 'report_apprenticeoffjob', 'apprenticeoffjob', 0); // 0 is the item id.


$hoursform = new offjobhours(null, array('studentid' => $studentid, 'courseid' => $courseid));
if ($hoursform->is_cancelled()) {
  redirect($CFG->wwwroot . '/report/apprenticeoffjob/index.php?id=' . $courseid);
} else if ($formdata = $hoursform->get_data()) {
  $savehours = save_hours($formdata);
  $data = file_postupdate_standard_filemanager($data, 'apprenticeoffjob',
          $fileoptions, context_user::instance($studentid), 'report_apprenticeoffjob', 'apprenticeoffjob', 0);
  redirect(new moodle_url('/report/apprenticeoffjob/index.php', ['courseid'=>$courseid]), get_string('hourssaved', 'report_apprenticeoffjob'), 15);
}

$hoursform->set_data($data);
$hoursform->display();

echo $OUTPUT->footer();
