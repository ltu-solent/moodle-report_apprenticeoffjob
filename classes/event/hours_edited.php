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
 * Teacher data entry point for apprentice off job hours
 *
 * @package    report_apprenticeoffjob
 * @copyright  2020 onwards Solent University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_apprenticeoffjob\event;


 /**
  * The report_apprenticeoffjob hours edited event class.
  *
  * @package    report_apprenticeoffjob
  * @since      Moodle 3.6
  * @copyright  2020 onwards Solent University
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

class hours_edited extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = 0;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id ".$this->userid." updated the target hours for user with id ". $this->relateduserid .".";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('hoursupdated', 'report_apprenticeoffjob');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/report/apprenticeoffjob/edit.php', array(
            'studentid' => $this->relateduserid,
            'courseid' => $this->courseid));
    }

}
