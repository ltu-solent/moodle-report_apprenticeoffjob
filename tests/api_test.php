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

namespace report_apprenticeoffjob;

use advanced_testcase;
use local_apprenticeoffjob_generator;

/**
 * Tests for Apprentice off the job hours report
 *
 * @package    report_apprenticeoffjob
 * @category   test
 * @group sol
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright  2023 Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class api_test extends advanced_testcase {
    /**
     * Test get Target hours for student or list of students.
     * @covers \local_apprenticeoffjob\api::get_targethours
     * @return void
     */
    public function test_get_targethours(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $targethours = api::get_targethours([]);
        $this->assertCount(0, $targethours);
        $activitytypes = \local_apprenticeoffjob\api::get_activitytypes();
        $students = [];
        $studentids = [];
        $studenttargets = [];
        $studentlogged = [];
        /** @var report_apprenticeoffjob_generator $reportdg */
        $reportdg = $this->getDataGenerator()->get_plugin_generator('report_apprenticeoffjob');
        /** @var local_apprenticeoffjob_generator $localdg */
        $localdg = $this->getDataGenerator()->get_plugin_generator('local_apprenticeoffjob');
        for ($x = 0; $x < 5; $x++) {
            $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
            $students[$x] = $student;
            $studentids[] = $student->id;
            $targets = [
                'course' => $course->id,
                'studentid' => $student->id,
            ];
            $actuals = [];
            foreach ($activitytypes as $activitytype) {
                $key = 'activity_' . $activitytype->id;
                $targets[$key] = ($x + 10) * 5;
                for ($a = 0; $a < 5; $a++) {
                    $actuals[$activitytype->id][$a] = [
                        'course' => $course->id,
                        'activitytype' => $activitytype->id,
                        'activitydate' => time(),
                        'activitydetails' => "{$activitytype->activityname} {$a}",
                        'activityhours' => $x + $a,
                        'userid' => $student->id,
                    ];
                }
            }
            $this->setUser($teacher);
            $reportdg->set_target_hours($targets);
            $studenttargets[$x] = $targets;
            $studentlogged[$x] = $actuals;
            $this->setUser($student);
            foreach ($actuals as $activitytype) {
                foreach ($activitytype as $actual) {
                    $localdg->create_student_activity($actual);
                }
            }
        }
        $randomperson = $this->getDataGenerator()->create_user();
        $targethours = api::get_targethours($studentids);
        $this->assertCount(25, $targethours);
        // Just get target hours for the one student.
        $targethours = api::get_targethours($students[0]->id);
        $this->assertCount(5, $targethours);
        // And for one person not enrolled on anything.
        $targethours = api::get_targethours($randomperson->id);
        $this->assertCount(1, $targethours);
    }
}
