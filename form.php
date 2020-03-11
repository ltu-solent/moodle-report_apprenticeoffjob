<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");

class offjobhours extends moodleform {
	public function definition() {
		global $USER, $DB, $CFG, $OUTPUT;

		$mform = $this->_form;
    $students = get_students($this->_customdata['courseid']);
    $studentdata = get_student_data($students);
		$activities = get_current_activities();

    foreach($students as $student => $value){
				$edit = is_updating($value->id, $studentdata);
				$studentdetails = $value->firstname . ' ' . $value->lastname;
				$mform->addElement('html', '<h3>' . $studentdetails. '</h3><br />');
				foreach($activities as $a => $v){
					// Element ID created from studentid_activityid_edit(1/0)
					$mform->addElement('text', $value->id . '_' . $v->id . '_' . $edit, $v->activityname, $attributes='size="10"');
				}
			//$mform->addElement('filemanager', 'files_filemanager', 'Commitment statement', null, array('maxbytes' => 41943040, 'maxfiles' => 1));
    }

    $mform->addElement('hidden', 'id', $this->_customdata['courseid']);
    $mform->setType('id', PARAM_INT);
    $this->add_action_buttons();
		$formdata = array();

		foreach($studentdata as $s => $d){
			$edit = is_updating($d->studentid, $studentdata);
			$formdata[$d->studentid.'_'. $d->activityid .'_'. $edit] = $d->hours;
		}
		$this->set_data($formdata);
	}
}
