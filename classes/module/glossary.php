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

namespace local_course_exporter\module;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/glossary/lib.php');

/**
 * Class to export a glossary module to PDF.
 */
class glossary implements module_interface {

    /**
     * Export glossary entries to PDF–compatible HTML.
     *
     * @param \moodle_database $db
     * @param object $cm
     * @return string
     * @throws \dml_exception
     */
    public function export_to_pdf(\moodle_database $db, object $cm): string {
        $entries = $db->get_records('glossary_entries', ['glossaryid' => $cm->instance], 'concept ASC');

        $html = '<div>';
        foreach ($entries as $entry) {
            $concept = htmlspecialchars($entry->concept, ENT_QUOTES, 'UTF-8');
            $definition = format_text($entry->definition, $entry->definitionformat, [
                'context' => \context_module::instance($cm->id)
            ]);
            $html .= "<h3>{$concept}</h3>";
            $html .= "<span>{$definition}</span>";
        }
        $html .= '</div>';

        return $html;
    }
}
