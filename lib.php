<?php
function report_apprenticeoffjob_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/apprenticeoffjob:view', $context)) {
        $url = new moodle_url('/report/apprenticeoffjob/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_apprenticeoffjob'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

function get_students($courseid){
  // Get the students enrolled on the course
  global $DB;
  $students = get_role_users(5, context_course::instance($courseid), false, 'u.id, u.firstname, u.lastname');

  return $students;
}

function get_student_data($students){
  // Get all the data held for specified student ids
  global $DB;
  $studentids = [];
  foreach($students as $k => $v){
    $studentids[] = $v->id;
  }
  $studentids = implode(",",$studentids);
  $studentdata = $DB->get_records_sql("SELECT *
                                      FROM {report_apprentice}
                                      WHERE studentid IN ($studentids)");
  return $studentdata;
}

function get_current_activities(){
  // Get options students can currently use for logging activities
  global $DB, $USER;
  $dbman = $DB->get_manager();
  if($dbman->table_exists('local_apprenticeactivities')){
    $activities = $DB->get_records_sql('SELECT *
                                    FROM {local_apprenticeactivities}
                                    WHERE status = ?',
                                    array(1));
    return $activities;
  }else{
    return false;
  }
}

function is_updating($student, $studentdata){
  // Check if the teacher has entered any hours for the students
  if(!empty($studentdata)){
     foreach($studentdata as $s => $d){
      if(in_array($student, get_object_vars($d))){
        return 1;
      }
    }
    return 0;
  }else{
    return 0;
  }
}

function save_hours($formdata){
  // Save hours entered by the teacher
  global $USER, $DB;
  foreach($formdata as $k=>$v){
    if($k != 'id' && $k != 'submitbutton'){
      // Create base dataobject
      $data = explode("_", $k);
      $update = $data[2];
      $dataobject = new stdClass();
      $dataobject->studentid = $data[0];
      $dataobject->staffid = $USER->id;
      $dataobject->activityid = $data[1];
      $dataobject->hours = $v;

      if($update == 0){
        // If updating, insert a new record and updated timecreated
        $date = new DateTime("now", core_date::get_user_timezone_object());
        $date->setTime(0, 0, 0);
        $dataobject->timecreated = $date->getTimestamp();
        $result = $DB->insert_record('report_apprentice', $dataobject, true, false);
      }elseif($update == 1){
        //Get record being updated
        $id = $DB->get_record_sql('SELECT id FROM {report_apprentice} WHERE studentid = ? AND activityid = ? AND hours != ?'
                                  , array($dataobject->studentid, $dataobject->activityid,  $dataobject->hours));
        //Check if hours have changed
        if($id){
          $date = new DateTime("now", core_date::get_user_timezone_object());
          $date->setTime(0, 0, 0);
          $dataobject->timemodified = $date->getTimestamp();
          $dataobject->id = $id->id;
          $result = $DB->update_record('report_apprentice', $dataobject, false);
        }
      }
    }
  }
}

function display_table($course){
  // Should I be using current activities or activities used for the students?
  $activities = get_current_activities();
  $students = get_students($course);
  $studentdata = get_student_data($students);
//var_dump($studentdata);
  $headings = array();
  $headings[] = 'Student';
  foreach($activities as $activity=>$a){
    $headings[] = $a->activityname;
  }
  $headings[] = 'Edit';
  $table = new html_table();
	$table->attributes['class'] = 'generaltable boxaligncenter';
	$table->id = 'apprenticeoffjob';
	$table->cellpadding = 5;
	$table->head = $headings;

  //Student data
  foreach($students as $student=>$s){
    $row = new html_table_row();
    $cells = array();
    $cells[] = new html_table_cell($s->firstname . ' ' . $s->lastname);
    foreach($activities as $activity=>$a){
      $cell = new html_table_cell(match_activity($activity, $s->id, $studentdata));
      $cell->id = $activity;
      $cells[] = $cell;
    }
    $cells[] = new html_table_cell('Edit');
    $row->cells = $cells;
    $table->data[] = $row;
  }

  return $table;
}

function match_activity($activity, $student, $studentdata){
  foreach($studentdata as $s=>$d){
    if($d->studentid == $student && $d->activityid == $activity){
      return $d->hours;
    }
  }
}
