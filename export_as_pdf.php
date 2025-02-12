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

require_once(__DIR__ . '/../../config.php');

// Get the course ID from parameters.
$courseid = required_param('courseid', PARAM_INT);

// Get the course context.
$context = context_course::instance($courseid);

// Require the user to be logged in and have the necessary capability.
require_login($courseid);
require_capability('moodle/course:manageactivities', $context);

// Initialize the $PAGE global object.
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/course_exporter/export_as_pdf.php', ['courseid' => $courseid]));

// Purge Moodle and browser's cache.
purge_all_caches();

try {
    // Generate PDF.
    $pdfexporter = new \local_course_exporter\pdf_exporter($DB, $courseid);
    $filepath = $pdfexporter->generate_pdf();
    // Send the PDF to the browser for download.
    send_file(
        $filepath,
        time() . '_course_' . $courseid . '.pdf',
        null,
        0,
        false,
        false,
        '',
        false,
        ['nocache' => true],
    );
} catch (Exception $e) {
    // Handle errors gracefully.
    echo $OUTPUT->header();
    echo $OUTPUT->notification('Failed to generate PDF: ' . $e->getMessage(), 'error');
    echo $OUTPUT->footer();
    exit;
}
