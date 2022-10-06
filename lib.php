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
 * Lib file
 *
 * @package   report_apprenticeoffjob
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add apprentice off job to course navigation
 *
 * @param navigation_node $navigation
 * @param stdClass $course
 * @param context $context
 * @return void
 */
function report_apprenticeoffjob_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/apprenticeoffjob:view', $context)) {
        $url = new moodle_url('/report/apprenticeoffjob/index.php', array('id' => $course->id));
        $navigation->add(
            get_string('pluginname', 'report_apprenticeoffjob'),
            $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Plugin function for uploaded file.
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The cm object.
 * @param context $context The context object.
 * @param string $filearea The file area.
 * @param array $args List of arguments.
 * @param bool $forcedownload Whether or not to force the download of the file.
 * @param array $options Array of options.
 * @return void|false
 */
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
