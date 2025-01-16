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

namespace local_edai_pdf\module;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/glossary/lib.php');

class glossary extends module_base {

    protected function get_identifier() : string {
        return 'glossary';
    }

    protected function get_module_name() : string {
        return 'glossary';
    }

    public function get_context(\moodle_database $db, object $cm): string {
        $glossary = $db->get_record('glossary', ['id' => $cm->instance], '*', MUST_EXIST);
        $entries = $db->get_records('glossary_entries', ['glossaryid' => $glossary->id]);

        $context = "Custom Module Information:\n";
        $context .= "- Name: {$glossary->name}\n";
        $context .= "- Intro: {$glossary->intro}\n";
        $context .= "- Number of Entries: " . count($entries) . "\n\n";

        foreach ($entries as $entry) {
            $context .= "Entry:\n";
            $context .= "- Concept: {$entry->concept}\n";
            $context .= "- Definition: {$entry->definition}\n";
            $context .= "\n";
        }
        return $context;
    }

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
