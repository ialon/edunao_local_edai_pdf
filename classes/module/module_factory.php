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

/**
 * Factory to create module instances based on module type.
 */
class module_factory {

    /**
     * Create an instance of a module.
     *
     * @param string $type
     * @return module_interface
     * @throws \InvalidArgumentException If the module type is not supported.
     */
    public function create(string $type): module_interface {
        return match (strtolower($type)) {
            'page' => new page(),
            'simplequiz' => new simplequiz(),
            'glossary' => new glossary(),
            default => throw new \InvalidArgumentException("Unsupported module type: {$type}"),
        };
    }

    /**
     * Return an array of supported module instances.
     *
     * @return module_interface[]
     */
    public function get_supported_modules(): array {
        return [
            'page' => new page(),
            'simplequiz' => new simplequiz(),
            'glossary' => new glossary(),
        ];
    }
}

