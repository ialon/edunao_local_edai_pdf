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

class page extends module_base {

    protected function get_identifier() : string {
        return 'page';
    }

    protected function get_module_name() : string {
        return 'page';
    }

    /**
     * Provides module-specific context information.
     *
     * @param \moodle_database $db
     * @param object $cm
     * @return string
     * @throws \moodle_exception
     */
    public function get_context(\moodle_database $db, object $cm): string {
        $page = $db->get_record('page', ['id' => $cm->instance], '*', MUST_EXIST);
        $context = <<<EOL
        Custom Module Information:
        - Name: {$page->name}
        - Intro: {$page->intro}
        - Content: {$page->content}
        
        EOL;
        return $context;
    }

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

        // Rewrite pluginfile URLs.
        $page->content = file_rewrite_pluginfile_urls($page->content, 'pluginfile.php', $context->id, 'mod_page', 'content', $page->revision);

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

        // Save the cleaned off of script tags HTML to avoid brut text formulas display inside PDF.
        $cleanedcontent = $dom->saveHTML();

        // Return content.
        return '<div>' . $cleanedcontent . '</div>';
    }

}
