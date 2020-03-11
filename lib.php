<?php
function report_apprenticeoffjob_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/apprenticeoffjob:view', $context)) {
        $url = new moodle_url('/report/apprenticeoffjob/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_apprenticeoffjob'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

function get_students($courseid){
  global $DB;
  $students = get_role_users(5, context_course::instance($courseid), false, 'u.id, u.firstname, u.lastname');

  return $students;
}

function get_student_data($students){
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

function form_data (){
  $formdata = array('id' => $activity->id,
                    'activitytype' => $activity->activitytype,
                    'activitydate' => $activity->activitydate,
                    'activitydetails' => $activity->activitydetails,
                    'activityhours' => $activity->activityhours,
                    'activityupdate' => 1
                    );
}

function save_hours($formdata){
  global $USER, $DB;
  foreach($formdata as $k=>$v){
    if($k != 'id' && $k != 'submitbutton'){
      //create data array
      $data = explode("_", $k);
      $update = $data[2];
      $dataobject = new stdClass();
      $dataobject->studentid = $data[0];
      $dataobject->staffid = $USER->id;
      $dataobject->activityid = $data[1];
      $dataobject->hours = $v;

      if($update == 0){
        $date = new DateTime("now", core_date::get_user_timezone_object());
        $date->setTime(0, 0, 0);
        $dataobject->timecreated = $date->getTimestamp();

        $result = $DB->insert_record('report_apprentice', $dataobject, true, false);
      }elseif($update == 1){
        //Get record being updated
        $id = $DB->get_record_sql('SELECT id FROM {report_apprentice} WHERE studentid = ? AND activityid = ? AND hours != ?'
                                  , array($dataobject->studentid, $dataobject->activityid,  $dataobject->hours));
        var_dump($id);
        //Check if hours have changed
        if($id){
          $date = new DateTime("now", core_date::get_user_timezone_object());
          $date->setTime(0, 0, 0);
          $dataobject->timemodified = $date->getTimestamp();
          $dataobject->id = $id;
          var_dump($dataobject);
          $result = $DB->update_record('report_apprentice', $dataobject, false);
        }
      }
    }
  }
}
