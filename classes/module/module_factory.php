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

class module_factory {
    /**
     * Creates an instance of a module based on the given module type.
     *
     * @param string $type The type of module to create (e.g., 'page', 'simplequiz', 'multichoice', 'truefalse', ...).
     * @return module_base An instance of a module class.
     * @throws \InvalidArgumentException If the module type is not supported.
     */
    public function create(string $type): module_base {
        return match (strtolower($type)) {
            'page' => new page(),
            'glossary' => new glossary(),
            default => throw new \InvalidArgumentException("Unsupported module type: {$type}"),
        };
    }

    /**
     * Retrieves all supported modules and their instances.
     *
     * @return array Associative array with module types as keys and module instances as values.
     */
    public function get_supported_modules(): array {
        $supported = [
            'page' => new page(),
            'glossary' => new glossary(),
        ];

        return $supported;
    }
}
