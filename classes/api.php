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
 * Helper class for apprentice off job
 *
 * @package   report_apprenticeoffjob
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_apprenticeoffjob;

use stdClass;

/**
 * Helper class
 */
class api {

    /**
     * Get target hours for each student
     *
     * @param array $studentids
     * @return array List of target hours for each type for each user
     */
    public static function get_targethours($studentids) {
        global $DB;
        // Get all the data held for specified student ids.
        if (!is_array($studentids)) {
            $studentids = [$studentids];
        }
        list($inorequalsql, $params) = $DB->get_in_or_equal($studentids, SQL_PARAMS_NAMED);
        // Create a random id, as there will be multiple entries or none for a user.
        $sql = "SELECT RAND() id, u.id userid, u.firstname, u.lastname,
                ra.studentid, ra.staffid, ra.activityid, ra.hours
                FROM {user} u
                LEFT JOIN {report_apprentice} ra ON ra.studentid = u.id
                JOIN {local_apprenticeactivities} aa ON aa.id = ra.activityid
                WHERE u.id $inorequalsql";

        $targethours = $DB->get_records_sql($sql, $params);
        return $targethours;
    }

    /**
     * Save target hours set by the teacher for each activity type
     *
     * @param stdClass $formdata
     * @return void
     */
    public static function save_hours($formdata) {
        global $USER, $DB;
        foreach ($formdata as $k => $v) {
            // Only interested in saving activity elements.
            if (strpos($k, 'activity_') === false) {
                continue;
            }

            $dataobject = new stdClass();
            $dataobject->studentid = $formdata->studentid;
            $dataobject->staffid = $USER->id;
            $activityparts = explode("_", $k);
            $dataobject->activityid = $activityparts[1];
            $dataobject->hours = $v;

            $id = $DB->get_record('report_apprentice', [
                'studentid' => $dataobject->studentid,
                'activityid' => $dataobject->activityid
            ]);
            if (!$id) {
                $dataobject->timecreated = time();
                $dataobject->timemodified = time();
                $DB->insert_record('report_apprentice', $dataobject);
            } else {
                $dataobject->timemodified = time();
                $dataobject->id = $id->id;
                $DB->update_record('report_apprentice', $dataobject);
            }
        }
    }
}
