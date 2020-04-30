<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('lib.php');

class offjobhours extends moodleform {
	public function definition() {
		global $USER, $DB, $CFG, $OUTPUT;

		$mform = $this->_form;

    $student = get_student($this->_customdata['studentid']);
    $studentdata = get_student_data($student->id);
		$activities = get_current_activities();

		foreach($studentdata as $student=>$value){
			$studentdetails = $value->firstname . ' ' . $value->lastname;
		}
		$mform->addElement('html', '<h3>' . $studentdetails. '</h3><br />');
		foreach($activities as $activity=>$a){
			$mform->addElement('text', 'activity_'.$a->id, $a->activityname);
	    $mform->setType('activity_'.$a->id, PARAM_RAW);
			$mform->addRule('activity_'.$a->id, get_string('errnumeric', 'report_apprenticeoffjob'), 'numeric', null, 'server', 1, 0);
		}
		$mform->addElement('filemanager', 'apprenticeoffjob_filemanager', 'Commitment statement', null, $fileoptions);

    $mform->addElement('hidden', 'studentid', $this->_customdata['studentid']);
    $mform->setType('studentid', PARAM_INT);
    $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
    $mform->setType('course', PARAM_INT);
    $this->add_action_buttons();
		$formdata = array();

		foreach($studentdata as $s => $d){
			$formdata['activity_'. $d->activityid] = $d->hours;
			$this->set_data($formdata);
		}
	}

	public function validation($data, $files) {
			$errors = parent::validation($data, $files);

			foreach($data as $k=>$v){
				if(strpos($k, 'activity_') !== false){
					if(floor($v) != $v){
						$errors[$k] = get_string('errnumeric', 'report_apprenticeoffjob');
					}
				}
			}

      return $errors;
  }
}
