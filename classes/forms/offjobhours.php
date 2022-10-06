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
 * Offjobhours target set up
 *
 * @package   report_apprenticeoffjob
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_apprenticeoffjob\forms;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

use core_user;
use moodleform;

/**
 * Offjob hours teacher's form
 */
class offjobhours extends moodleform {
    /**
     * Form definition
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $student = core_user::get_user($this->_customdata['studentid']);
        $targethours = \report_apprenticeoffjob\api::get_targethours([$student->id]);
        $activities = \local_apprenticeoffjob\api::get_activitytypes();
        $studentdetails = fullname($student);
        $mform->addElement('html', '<h3>Set target hours for ' . $studentdetails. '</h3><br />');
        foreach ($activities as $a) {
            $mform->addElement('text', 'activity_' . $a->id, $a->activityname);
            $mform->setType('activity_' . $a->id, PARAM_TEXT);
            $mform->addRule('activity_' . $a->id,
                get_string('errnumeric', 'report_apprenticeoffjob'),
                'numeric', null, 'client', 1, 0);
        }
        $mform->addElement('filemanager', 'apprenticeoffjob_filemanager', 'Commitment statement',
            null, $this->_customdata['fileoptions']);
        $mform->addElement('hidden', 'studentid', $this->_customdata['studentid']);
        $mform->setType('studentid', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);
        $this->add_action_buttons();
        $formdata = array();
        foreach ($targethours as $s => $d) {
            $formdata['activity_'. $d->activityid] = $d->hours;
            $this->set_data($formdata);
        }
    }

    /**
     * Form validation
     *
     * @param array $data
     * @param array $files
     * @return array Errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        foreach ($data as $k => $v) {
            if (strpos($k, 'activity_') !== false) {
                if (floor($v) != $v) {
                    $errors[$k] = get_string('errnumeric', 'report_apprenticeoffjob');
                }
            }
        }
        return $errors;
    }
}
