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

        // Emoji replacement.
        $page->content = $this->replace_emoji_with_images($page->content);

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

    public function replace_emoji_with_images($content) {
        global $CFG;

        // Regex to detect emojis
        $emojiregex = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2B50}\x{2B55}\x{2934}\x{2935}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F0CF}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}-\x{1F251}\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}\x{1F5FB}-\x{1F5FF}\x{1F600}-\x{1F64F}\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}\x{1F6F4}-\x{1F6F9}\x{1F910}-\x{1F93A}\x{1F93C}-\x{1F93E}\x{1F940}-\x{1F945}\x{1F947}-\x{1F94C}\x{1F950}-\x{1F96B}\x{1F980}-\x{1F997}\x{1F9C0}\x{1F9D0}-\x{1F9E6}\x{1F9F0}-\x{1F9FF}\x{1FA70}-\x{1FA73}\x{1FA78}-\x{1FA7A}\x{1FA80}-\x{1FA82}\x{1FA90}-\x{1FA95}\x{1FA96}-\x{1FAA8}\x{1FAB0}-\x{1FAB6}\x{1FAC0}-\x{1FAC2}\x{1FAD0}-\x{1FAD6}\x{1FAD7}-\x{1FAD9}\x{1FAE0}-\x{1FAE7}\x{1FAF0}-\x{1FAF6}]/u';

        // Find all matches
        preg_match_all($emojiregex, $content, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $match) {
            $emoji = $match[0];

            // Convert emoji to Unicode code point
            $unicode = strtolower(ltrim(bin2hex(mb_convert_encoding($emoji, 'UTF-32', 'UTF-8')), '0'));

            // Check if file exists
            $file = $CFG->dirroot . '/local/edai_pdf/pix/emoji/emoji_u' . $unicode . '.svg';
            if (!file_exists($file)) {
                $image = '';
            } else {
                // Prepare image
                $src = $CFG->wwwroot . '/local/edai_pdf/pix/emoji/emoji_u' . $unicode . '.svg';
                $attrs = [
                    'class' => 'img-fluid align-top',
                    'width' => 20,
                    'height' => 20
                ];
                $image = \html_writer::img($src, '', $attrs);
            }

            // Replace emoji with image
            $content = str_replace($emoji, $image, $content);
        }

        return $content;
    }
}
