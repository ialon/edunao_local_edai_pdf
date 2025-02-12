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
 *
 *
 * @package    local_course_exporter
 * @copyright  2025 Edunao SAS (contact@edunao.com)
 * @author     Pierre FACQ <pierre.facq@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Serves files for PDF export.
 *
 * @package  local_edai_pdf
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param \context $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function local_edai_pdf_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG;
    require_once("$CFG->libdir/resourcelib.php");

    if (!has_capability('moodle/course:manageactivities', $context)) {
        return false;
    }

    // We need to extract the real component and filearea from the $filearea
    // If the real filearea contains an underscore, this code will not work
    $parts = explode('_', $filearea);
    $filearea = array_pop($parts);
    $component = implode('_', $parts);

    $arg = array_shift($args);
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/$component/$filearea/0/$relativepath";

    $file = $fs->get_file_by_hash(sha1($fullpath));

    // finally send the file
    send_stored_file($file, null, 0, $forcedownload, $options);
}
