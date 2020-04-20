<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('lib.php');

class offjobhours extends moodleform {
	public function definition() {
		global $DB;
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
	    $mform->setType('activity_'.$a->id, PARAM_FLOAT);
			$mform->addRule('activity_'.$a->id, get_string('error'), 'numeric', 'client');
		}
		$fileoptions = array('maxbytes' => 41943040, 'maxfiles' => 1);
		$mform->addElement('filemanager', 'activity_filemanager', 'Commitment statement', null, $fileoptions);

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

		$attachment = $DB->get_record('report_apprentice',(['studentid'=>$this->_customdata['studentid'],'attachment'=>1]));
		$entry = $DB->get_record('files',(['contextid'=>$this->_customdata['studentid'],'component'=>'report_apprenticeoffjob','filearea'=>'attachment','itemid'=>$attachment->id]));

		$fileoptions = array('maxbytes' => 41943040, 'maxfiles' => 1);
		if($entry->id){
			$entryid = $entry->id;
		}else{
			$entryid = 0;
		}

		$usercontext = context_user::instance($this->_customdata['studentid']);
		$data = file_prepare_standard_filemanager($entryid, 'files', $fileoptions, $usercontext->id, 'report_apprenticeoffjob', 'attachment', $entry->itemid);

		$this->set_data($data);
	}
}
