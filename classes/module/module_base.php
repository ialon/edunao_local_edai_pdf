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

/**
 * Abstract base class for all modules.
 *
 * Provides common functionality and enforces the implementation of specific methods
 * required by each module type.
 */
abstract class module_base {

    /**
     * Gets the unique identifier for the module.
     *
     * @return string The module identifier.
     */
    abstract protected function get_identifier(): string;

    /**
     * Gets the name of the module.
     *
     * @return string The module name.
     */
    abstract protected function get_module_name(): string;

    /**
     * Abstract method to retrieve module-specific contextual information.
     *
     * @param \moodle_database $db
     * @param object $cm
     * @return string
     */
    abstract public function get_context(\moodle_database $db, object $cm): string;

    /**
     * Exports the module content to HTML suitable for PDF generation.
     *
     * @param \moodle_database $db The Moodle database instance.
     * @param object $cm The course module record.
     * @return string HTML content representing the module.
     */
    abstract public function export_to_pdf(\moodle_database $db, object $cm): string;
}
