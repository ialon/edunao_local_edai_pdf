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

require_once($CFG->dirroot . '/mod/simplequiz/lib.php');

class simplequiz implements module_interface {

    /**
     * Exports the simplequiz module content to HTML for PDF.
     *
     * @param \moodle_database $db The Moodle database instance.
     * @param object $cm The course module record.
     * @return string HTML content representing the simplequiz module.
     */
    public function export_to_pdf(\moodle_database $db, object $cm): string {
        $simplequiz = $db->get_record('simplequiz', ['id' => $cm->instance], '*', MUST_EXIST);
        $questions = json_decode($simplequiz->questions, true);

        // Validate JSON decoding.
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
            throw new \moodle_exception('Invalid questions format in simplequiz.');
        }

        // Start building the HTML content.
        $html = '<div style="font-family: Arial, sans-serif; margin: 20px;">';

        // Add the quiz title.
        $quizname = htmlspecialchars($simplequiz->name, ENT_QUOTES, 'UTF-8');
        $html .= "<h2 style='text-align: center;'>{$quizname}</h2>";

        // Add the introduction.
        $intro = format_text($simplequiz->intro, FORMAT_HTML, [
            'context' => \context_module::instance($cm->id)
        ]);
        $html .= "<div style='margin-bottom: 30px;'>{$intro}</div>";

        // Iterate through each question and append its HTML representation.
        foreach ($questions as $index => $question) {
            $questionnumber = $index + 1;
            $questiontext = htmlspecialchars($question['text'], ENT_QUOTES, 'UTF-8');

            // Start question block
            $html .= "<div style='margin-bottom: 20px;'>";
            $html .= "<p><strong>Question {$questionnumber}:</strong> {$questiontext}</p>";

            // Render options
            if (!empty($question['answers']) && is_array($question['answers'])) {
                $html .= "<ul style='list-style-type: none; padding-left: 0;'>";
                foreach ($question['answers'] as $optionindex => $option) {
                    $optionlabel = chr(65 + $optionindex);
                    $optionescaped = htmlspecialchars($option['text'], ENT_QUOTES, 'UTF-8');
                    $html .= "<li><strong>{$optionlabel}.</strong> {$optionescaped}</li>";
                }
                $html .= "</ul>";
            } else {
                $html .= "<p><em>No options available.</em></p>";
            }

            $html .= "</div>";
        }

        $html .= '</div>';

        return $html;
    }
}
