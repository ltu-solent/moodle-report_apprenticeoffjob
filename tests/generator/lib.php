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
 * Data generator class
 *
 * @package    report_apprenticeoffjob
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright  2023 Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_apprenticeoffjob_generator extends component_generator_base {
    /**
     * Set target hours for a student
     *
     * @param array $formdata
     * @return stdClas
     */
    public function set_target_hours($formdata): stdClass {
        global $DB;
        // Requires staff is logged in.
        if (!isset($formdata['studentid'])) {
            throw new moodle_exception('Studentid not set');
        }
        // Expect all the enabled activity types to have something set, or set default.
        $activitytypes = \local_apprenticeoffjob\api::get_activitytypes();
        foreach ($activitytypes as $activitytype) {
            $key = 'activity_' . $activitytype->id;
            if (!isset($formdata[$key])) {
                $formdata[$key] = 0;
            }
        }
        $id = \report_apprenticeoffjob\api::save_hours((object)$formdata);
        return $DB->get_record('report_apprentice', ['id' => $id]);
    }
}
