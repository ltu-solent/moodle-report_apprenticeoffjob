<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");

class offjobhours extends moodleform {
	public function definition() {
		global $USER, $DB, $CFG, $OUTPUT;

		$mform = $this->_form;
    $students = get_students($this->_customdata['courseid']);
    $studentdata = get_student_data($students);

    foreach($students as $student => $v){
				$edit = is_updating($v->id, $studentdata);
	      $hoursarray=array();
	      $hoursarray[] =& $mform->createElement('text', $v->id . '_1_' . $edit, $v->id . '_1', $attributes='size="10"');
	      $hoursarray[] =& $mform->createElement('text', $v->id . '_2_' . $edit, $v->id . '_2', $attributes='size="10"');
	      $hoursarray[] =& $mform->createElement('text', $v->id . '_3_' . $edit, $v->id . '_3', $attributes='size="10"');
	      $hoursarray[] =& $mform->createElement('text', $v->id . '_4_' . $edit, $v->id . '_4', $attributes='size="10"');
	      $hoursarray[] =& $mform->createElement('text', $v->id . '_5_' . $edit, $v->id . '_5', $attributes='size="10"');

      // add a group of inputs for each student's hours
      $studentdetails = $v->firstname . ' ' . $v->lastname;
      $group = $mform->createElement('group', 'hours', $studentdetails, $hoursarray, null, false);
      $mform->addElement($group);
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
