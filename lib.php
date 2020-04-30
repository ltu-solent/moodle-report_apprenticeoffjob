<?php
function report_apprenticeoffjob_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/apprenticeoffjob:view', $context)) {
        $url = new moodle_url('/report/apprenticeoffjob/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_apprenticeoffjob'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

function get_student($studentid){
  // Get one student ID
  global $DB;
  $student = $DB->get_record('user',(['id'=>$studentid]), 'id, firstname, lastname');
  return $student;
}

function get_students($courseid){
  // Get the students IDs enrolled on the course
  global $DB;
  $students = get_role_users(5, context_course::instance($courseid), false, 'u.id, u.firstname, u.lastname');
  return $students;
}

function get_student_data($students){
  global $DB;
  // Get all the data held for specified student ids
  if(count($students) != 1){
    $studentids = [];
    foreach($students as $k => $v){
      $studentids[] = $v->id;
    }

    list($inorequalsql, $params) = $DB->get_in_or_equal($studentids, SQL_PARAMS_NAMED);
    $sql = "SELECT RAND() id, u.id userid, u.firstname, u.lastname,
             ra.studentid, ra.staffid, ra.activityid, ra.hours
             FROM {user} u
             LEFT JOIN {report_apprentice} ra ON ra.studentid = u.id
             WHERE u.id $inorequalsql ";


    $studentdata = $DB->get_records_sql($sql, $params);

  }else{

    if(is_array($students)){
      foreach($students as $student=>$s){
        $studentid = $s->id;
      }
    }else{
      $studentid = $students;
    }

    $studentdata = $DB->get_records_sql('SELECT RAND() id, u.id userid, u.firstname, u.lastname,
                                         ra.studentid, ra.staffid, ra.activityid, ra.hours
                                         FROM {user} u
                                         LEFT JOIN {report_apprentice} ra ON ra.studentid = u.id
                                         WHERE u.id = ? ', array($studentid));
  }

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

function save_hours($formdata){
  // Save hours entered by the teacher
  global $USER, $DB;
  foreach($formdata as $k=>$v){
    if(strpos($k, 'activity_') !== false){
      $dataobject = new stdClass();
      $dataobject->studentid = $formdata->studentid;
      $dataobject->staffid = $USER->id;

      if(strpos($k, 'filemanager') === false){
        $data = explode("_", $k);
        $dataobject->activityid = $data[1];
        $dataobject->hours = $v;
        $id = $DB->get_record_sql('SELECT id FROM {report_apprentice} WHERE studentid = ? AND activityid = ?'
                                  , array($dataobject->studentid, $dataobject->activityid));

        if(!$id){
          // If inserting - timecreated
          $date = new DateTime("now", core_date::get_user_timezone_object());
          $date->setTime(0, 0, 0);
          $dataobject->timecreated = $date->getTimestamp();
          $result = $DB->insert_record('report_apprentice', $dataobject, true, false);
        }else{
          //Get record being updated
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
  global $USER;
  // Should I be using current activities or activities used for the students?
  $activities = get_current_activities();
  $students = get_students($course);
  $studentdata = get_student_data($students);

  $headings = array();
  $headings[] = 'Student';
  foreach($activities as $activity=>$a){
    $headings[] = $a->activityname;
  }
  $headings[] = 'Total / Completed';
  $headings[] = 'Commitment Statement';
  $headings[] = '';
  $table = new html_table();
	$table->attributes['class'] = 'generaltable boxaligncenter';
	$table->id = 'apprenticeoffjob';
	$table->cellpadding = 5;
	$table->head = $headings;

  //Student data
  foreach($students as $st=>$v){
    $row = new html_table_row();
    $cells = array();
    $params = ['id'=> $v->id, 'user'=>$USER->id, 'course'=>$course];
    $url = new moodle_url('/local/apprenticeoffjob/index.php', $params);
    $log = html_writer::start_tag('a', array('href'=>$url));
    $log .= $v->firstname . ' ' . $v->lastname;
    $log .= html_writer::end_tag('a');
    $cells[] = new html_table_cell($log);

    foreach($activities as $activity=>$a){
      $cell = new html_table_cell(match_activity($activity, $st, $studentdata));
      $cell->id = $activity;
      $cells[] = $cell;
    }
    $cells[] = '';
    $cells[] = '';
    $params = ['studentid'=> $v->id, 'courseid'=> $course];
    $editurl = new moodle_url('/report/apprenticeoffjob/edit.php', $params);
    $editbutton = html_writer::start_tag('a', array('href'=>$editurl, 'class' => 'btn btn-secondary'));
    $editbutton .= get_string('reportedithours', 'report_apprenticeoffjob');
    $editbutton .= html_writer::end_tag('a');
    $cells[] = new html_table_cell($editbutton);
    $row->cells = $cells;
    $table->data[] = $row;
  }

  return $table;
}

function match_activity($activity, $student, $studentdata){
  foreach($studentdata as $s=>$d){
    if($d->userid == $student && $d->activityid == $activity){
      return $d->hours;
    }
  }
}

function report_apprenticeoffjob_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {

    if ($context->contextlevel != CONTEXT_USER) {
        send_file_not_found();
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'report_apprenticeoffjob', $filearea, $args[0], '/', $args[1]);

    if (!$file) {
     return false; // The file does not exist.
    }

    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
