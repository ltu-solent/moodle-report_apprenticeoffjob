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
 * List of apprentice target hours for course
 *
 * @package   report_apprenticeoffjob
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_apprenticeoffjob\tables;

use context_course;
use context_user;
use html_table;
use html_table_cell;
use html_table_row;
use html_writer;
use moodle_url;
use report_apprenticeoffjob\api as report_api;

/**
 * Summary table for course
 */
class summary {

    /**
     * Activity types
     *
     * @var array
     */
    private $activities = [];
    /**
     * Students enrolled on course
     *
     * @var array
     */
    private $students = [];
    /**
     * Target hours. Not used.
     *
     * @var array
     */
    private $targethours = [];
    /**
     * The table being created
     *
     * @var html_table
     */
    private $table;
    /**
     * Course context
     *
     * @var context_course
     */
    private $coursecontext;
    /**
     * Courseid
     *
     * @var int
     */
    private $courseid;

    /**
     * Table constructor
     *
     * @param int $courseid
     */
    public function __construct($courseid) {
        $this->coursecontext = context_course::instance($courseid);
        $this->courseid = $courseid;
        $this->activities = \local_apprenticeoffjob\api::get_activitytypes();
        $this->students = get_role_users(5, $this->coursecontext);
        $this->targethours = report_api::get_targethours(array_keys($this->students));

        $headings = [];
        $headings[] = get_string('tableheaderstudent', 'report_apprenticeoffjob');
        foreach ($this->activities as $a) {
            $headings[] = $a->activityname;
        }
        $headings[] = get_string('tableheaderhours', 'report_apprenticeoffjob');
        $headings[] = get_string('tableheadercommitment', 'report_apprenticeoffjob');
        $headings[] = get_string('actions', 'report_apprenticeoffjob');
        $this->table = new html_table();
        $this->table->attributes['class'] = 'generaltable boxaligncenter';
        $this->table->id = 'apprenticeoffjob-targethours-table';
        $this->table->head = $headings;
        $this->assemble();
    }

    /**
     * Assemble the table
     *
     * @return void
     */
    private function assemble() {
        foreach ($this->students as $student) {
            [$expectedhoursbyactivity, $totalexpectedhours] = \local_apprenticeoffjob\api::get_expected_hours($student->id);
            [$actualhours, $totalactualhours] = \local_apprenticeoffjob\api::get_actual_hours($student->id);
            $rag = '';
            $row = new html_table_row();
            $cells = [];

            // Link to individual user log.
            $params = ['id' => $student->id, 'course' => $this->courseid];
            $log = html_writer::link(
                new moodle_url('/local/apprenticeoffjob/index.php', $params),
                fullname($student)
            );
            $cell = new html_table_cell($log);
            $cell->attributes['class'] = 'nowrap';
            $cells[] = $cell;
            $activitycount = count($this->activities);
            $activityrag = 0;
            foreach ($this->activities as $activityid => $activity) {
                $targetactivityhours = $expectedhoursbyactivity[$activityid] ?? 0;
                $actualactivityhours = $actualhours[$activityid] ?? 0;
                if ($targetactivityhours == 0 && $actualactivityhours == 0) {
                    // Blank cell when there's nothing to report is less cluttered.
                    $cell = new html_table_cell();
                } else {
                    $cell = new html_table_cell($actualactivityhours . ' / ' . $targetactivityhours);
                }
                $cell->id = $activityid;
                $cells[] = $cell;
            }
            if ($totalactualhours == 0 && $totalexpectedhours > 0) {
                $rag = 'table-danger';
            } else if ($totalactualhours < $totalexpectedhours && $totalexpectedhours > 0) {
                $rag = ''; // Was table-warning.
            } else if ($totalactualhours >= $totalexpectedhours && $totalexpectedhours > 0) {
                $rag = 'table-success';
            } else {
                $rag = ''; // Was table-warning.
            }
            $cells[] = $totalactualhours . ' / ' . $totalexpectedhours;
            $usercontext = context_user::instance($student->id);
            $filename = \local_apprenticeoffjob\api::get_filename($usercontext->id);
            if ($filename) {
                $cells[] = '<i class="icon fa fa-check text-success fa-fw " aria-hidden="true"></i>';
            } else {
                $cells[] = '<i class="icon fa fa-times text-danger fa-fw " aria-hidden="true"></i>';
            }
            $params = ['studentid' => $student->id, 'courseid' => $this->courseid];
            $editbutton = html_writer::link(
                new moodle_url('/report/apprenticeoffjob/edit.php', $params),
                get_string('reportedithours', 'report_apprenticeoffjob'),
                [
                    'class' => 'btn btn-secondary',
                ]
            );
            $cells[] = new html_table_cell($editbutton);

            $row->cells = $cells;
            $row->attributes['class'] = $rag;
            $this->table->data[] = $row;
        }
    }

    /**
     * Outputs the table either directly or by returning string
     *
     * @param boolean $echo
     * @return string|void
     */
    public function print_table($echo = true) {
        $table = html_writer::table($this->table);
        if (!$echo) {
            return $table;
        }
        echo $table;
    }
}
