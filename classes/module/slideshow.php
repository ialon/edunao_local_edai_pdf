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

require_once($CFG->dirroot . '/mod/slideshow/lib.php');

/**
 * Slideshow module class.
 *
 * Handles the creation, management, and export of slideshow activities.
 */
class slideshow implements module_interface {

    /**
     * Exports the slideshow module content to HTML for PDF.
     *
     * @param \moodle_database $db The Moodle database instance.
     * @param object $cm The course module record.
     * @return string HTML content representing the slideshow module.
     */
    public function export_to_pdf(\moodle_database $db, object $cm): string {
        // Retrieve the slideshow record.
        $slideshow = $db->get_record('slideshow', ['id' => $cm->instance], '*', MUST_EXIST);
        $context = \context_module::instance($cm->id);

        // Format intro using Moodle's format_text with filters.
        $intro = format_text($slideshow->intro, $slideshow->introformat, [
            'context' => $context,
            'trusted' => true,
            'filter'  => true,
            'noclean' => true
        ]);

        // Retrieve and format slides.
        $slides = $db->get_records('slideshow_slide', ['slideshow' => $cm->id], 'sortorder ASC');
        $slideshtml = '';

        foreach ($slides as $slide) {
            $content = format_text($slide->content, $slide->contentformat, [
                'context' => $context,
                'trusted' => true,
                'filter'  => true,
                'noclean' => true
            ]);

            // Initialize DOMDocument and load the HTML content.
            $dom = new \DOMDocument();
            // Suppress errors due to malformed HTML.
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            // Remove unwanted script tags (e.g., math/tex).
            $scripts = $dom->getElementsByTagName('script');
            for ($i = $scripts->length - 1; $i >= 0; $i--) {
                $script = $scripts->item($i);
                if ($script->getAttribute('type') === 'math/tex') {
                    $script->parentNode->removeChild($script);
                }
            }

            // Save the cleaned HTML.
            $cleanedcontent = $dom->saveHTML();

            $slideshtml .= <<<EOL
            <div class="slide">
                <h3>{$slide->name}</h3>
                <div>{$cleanedcontent}</div>
            </div>
            EOL;
        }

        // Compile the complete HTML for PDF.
        $pdfcontent = <<<EOL
        <div class="slideshow">
            <h2>{$slideshow->name}</h2>
            <div class="intro">
                {$intro}
            </div>
            <div class="slides">
                {$slideshtml}
            </div>
        </div>
        EOL;

        return $pdfcontent;
    }

}
