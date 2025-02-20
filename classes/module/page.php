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

use local_course_exporter\helper\pdf_cleaner;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/page/lib.php');

class page implements module_interface {

    /**
     * Exports the page module content to HTML for PDF.
     *
     * @param \moodle_database $db The Moodle database instance.
     * @param object $cm The course module record.
     * @return string HTML content representing the page module.
     */
    public function export_to_pdf(\moodle_database $db, object $cm): string {
        $page = $db->get_record('page', ['id' => $cm->instance], '*', MUST_EXIST);
        $context = \context_module::instance($cm->id);

        $pdfcleaner = new pdf_cleaner();

        // Emoji replacement.
        $page->content = $pdfcleaner->replace_emoji_with_images($page->content);

        // Replace math characters with TeX.
        $page->content = $pdfcleaner->replace_math_characters_with_tex($page->content);

        // Rewrite pluginfile URLs.
        $page->content = file_rewrite_pluginfile_urls(
            $page->content,
            'pluginfile.php',
            $context->id,
            'local_course_exporter',
            'mod_page_content', // The real component_filearea
            $page->revision
        );

        // Format content using Moodle's format_text with filters.
        $content = format_text($page->content, $page->contentformat, [
            'context' => $context,
            'trusted' => true,
            'filter' => true,
            'noclean' => true
        ]);

        // Initialize DOMDocument and load the HTML content.
        $dom = new \DOMDocument();
        // Suppress errors due to malformed HTML.
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        // Get all script tags.
        $scripts = $dom->getElementsByTagName('script');
        // Since DOMNodeList is live, we need to iterate in reverse to safely remove nodes.
        for ($i = $scripts->length - 1; $i >= 0; $i--) {
            $script = $scripts->item($i);
            if ($script->getAttribute('type') === 'math/tex') {
                $script->parentNode->removeChild($script);
            }
        }

        $rootsize = 15; // In px.
        $pdfcleaner->fix_relative_units($dom, $rootsize, $rootsize);

        // Save the cleaned off of script tags HTML to avoid brut text formulas display inside PDF.
        $cleanedcontent = $dom->saveHTML();

        // Return content.
        return '<div>' . $cleanedcontent . '</div>';
    }
}
