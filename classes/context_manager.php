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
 * @package    local_edai_pdf
 * @copyright  2024 Edunao SAS (contact@edunao.com)
 * @author     Pierre FACQ <pierre.facq@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_edai_pdf;

defined('MOODLE_INTERNAL') || die();

use local_edai_pdf\module\module_factory;
use moodle_database;

class context_manager {
    protected moodle_database $db;

    /**
     * Constructor.
     *
     * @param moodle_database $db
     */
    public function __construct(moodle_database $db) {
        $this->db = $db;
    }

    public function get_current_page_context(\moodle_page $page, int $courseid) {
        $contextinfo = "Currently viewing ";
        // If we are on a module page, let's get more info.
        if (!empty($page->cm)) {
            $modinfo = get_fast_modinfo($courseid);
            $cm = $modinfo->get_cm($page->cm->id);
            $contextinfo .= "{$cm->modname}: \"{$cm->name}\".";
        } else {
            $contextinfo .= "course main page.";
        }

        return $contextinfo;
    }


    /**
     * Retrieves contextual information about a course.
     *
     * @param int $courseid
     * @return string
     * @throws \moodle_exception
     */
    public function get_course_context(int $courseid): string {
        $course  = $this->db->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $context = \context_course::instance($courseid);
        $summary = format_text($course->summary, $course->summaryformat, ['context' => $context]);
        $context = <<<EOL
        Course:
        - full name: {$course->fullname}
        - summary: $summary
        EOL;
        return $context;
    }

    /**
     * Retrieves contextual information about a section.
     *
     * @param int $sectionid
     * @return string
     * @throws \moodle_exception
     */
    public function get_section_context(int $sectionid): string {
        $section = $this->db->get_record('course_sections', ['id' => $sectionid], '*', MUST_EXIST);
        $context = \context_course::instance($section->course);
        $summary = format_text($section->summary, $section->summaryformat, ['context' => $context]);
        $context = <<<EOL
        Section {$section->section}:
        - name: {$section->name}
        - summary: $summary
        EOL;
        return $context;
    }

    /**
     * Retrieves contextual information about a course module.
     *
     * @param int $cmid
     * @return string
     * @throws \moodle_exception
     */
    public function get_course_module_context(int $cmid): string {
        $cm = $this->db->get_record('course_modules', ['id' => $cmid], '*', MUST_EXIST);
        $module = $this->db->get_record('modules', ['id' => $cm->module], '*', MUST_EXIST);
        $context = <<<EOL
        Course module:
        - name: {$module->name}
        
        
        EOL;

        return $context . $this->get_module_context_custom($cm->id);
    }

    /**
     * Retrieves full contextual information about a course, including all sections and their modules.
     *
     * @param int $courseid
     * @return string
     * @throws \moodle_exception
     */
    public function get_course_context_recursive(int $courseid): string {
        $coursecontext = $this->get_course_context($courseid);

        // Fetch all sections in the course.
        $sections = $this->db->get_records('course_sections', ['course' => $courseid], 'section ASC');
        foreach ($sections as $section) {
            if ($section->section == 0) {
                continue;
            }

            $coursecontext .= "\n" . $this->get_section_context_recursive($section->id);
        }

        return $coursecontext;
    }

    /**
     * Retrieves full contextual information about a section, including all its modules.
     *
     * @param int $sectionid
     * @return string
     * @throws \moodle_exception
     */
    public function get_section_context_recursive(int $sectionid): string {
        $section = $this->db->get_record('course_sections', ['id' => $sectionid], '*', MUST_EXIST);
        $sectioncontext = $this->get_section_context($sectionid);

        // Fetch all course modules in the section
        $cms = $this->db->get_records('course_modules', [
            'course' => $section->course,
            'section' => $section->id
        ], 'id ASC');
        foreach ($cms as $cm) {
            $sectioncontext .= "\n" . $this->get_course_module_context($cm->id);
        }

        return $sectioncontext;
    }

    /**
     * Retrieves full contextual information about a module, including its parent section and course.
     *
     * @param int $cmid
     * @return string
     * @throws \moodle_exception
     */
    public function get_course_module_context_full(int $cmid): string {
        $cm = $this->db->get_record('course_modules', ['id' => $cmid], '*', MUST_EXIST);
        $coursecontext = $this->get_course_context($cm->course);
        $sectioncontext = $this->get_section_context($cm->section);
        $modulecontext = $this->get_course_module_context($cmid);

        $fullcontext = $coursecontext . $sectioncontext . $modulecontext;

        return $fullcontext;
    }

    /**
     * Retrieves module-specific contextual information.
     *
     * @param int $cmid
     * @return string
     * @throws \moodle_exception
     */
    protected function get_module_context_custom(int $cmid): string {
        $cm = $this->db->get_record('course_modules', ['id' => $cmid], '*', MUST_EXIST);
        $module = $this->db->get_record('modules', ['id' => $cm->module], '*', MUST_EXIST);
        $modulename = $module->name;

        // Instantiate the appropriate module class using the factory.
        $modulefactory = new module_factory();
        try {
            $moduleinstance = $modulefactory->create($modulename);
        } catch (\Exception $e) {
            return '';
        }

        return $moduleinstance->get_context($this->db, $cm);
    }

    /**
     * Retrieves an array of CURLFile objects for the specified course ID.
     *
     * @param int $courseid The ID of the course.
     * @return \CURLFile[] An array of CURLFile objects.
     * @throws \moodle_exception If there is an error retrieving course or file information.
     */
    public function get_course_files(int $courseid): array {
        $allowedextensions = ['pdf', 'doc', 'docx', 'txt', 'pptx'];

        $sql = <<<SQL
        SELECT f.id, f.contextid, f.component, f.filearea, f.itemid, f.filepath, f.filename, f.mimetype
        FROM {files} f
        INNER JOIN {context} ctx ON f.contextid = ctx.id AND ctx.contextlevel = :contextlevel
        INNER JOIN {course_modules} cm ON cm.id = ctx.instanceid AND cm.course = :courseid
        INNER JOIN {modules} m ON cm.module = m.id
        WHERE f.filename <> '.' AND m.name <> 'coursecertificate'
        SQL;
        $files = $this->db->get_records_sql($sql, [
            'contextlevel' => CONTEXT_MODULE,
            'courseid' => $courseid,
        ]);
        if (empty($files)) {
            return [];
        }

        $fs = get_file_storage();
        $curlfiles = [];
        foreach ($files as $file) {
            $extension = pathinfo($file->filename, PATHINFO_EXTENSION);
            if (!$extension || !in_array(strtolower($extension), $allowedextensions, true)) {
                continue;
            }

            $storedfile = $fs->get_file(
                $file->contextid,
                $file->component,
                $file->filearea,
                $file->itemid,
                $file->filepath,
                $file->filename
            );
            if (!$storedfile || $storedfile->is_directory()) {
                continue;
            }

            $tmpfile = $storedfile->copy_content_to_temp();
            if (!$tmpfile) {
                continue;
            }

            $curlfiles[] = new \CURLFile($tmpfile, $file->mimetype, $file->filename);
        }

        return $curlfiles;
    }
}
