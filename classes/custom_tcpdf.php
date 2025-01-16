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

namespace local_edai_pdf;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/tcpdf/tcpdf.php');

class custom_tcpdf extends \TCPDF {
    public function Footer() {
        // Position at 15 mm from the bottom.
        $this->SetY(-15);
        // Set font.
        $this->SetFont('helvetica', 'I', 8);
        // Page number.
        $this->Cell(0, 10, $this->getAliasNumPage(), 0, 0, 'R');
    }

    // Override the Image() method
    public function Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='',
                          $align='', $resize=false, $dpi=300, $palign='',
                          $ismask=false, $imgmask=false, $border=0, $fitbox=false,
                          $hidden=false, $fitonpage=false, $alt=false, $altimgs=array()) {
        // Ensure alt text is not included
        $alt = false;
        parent::Image($file, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi,
            $palign, $ismask, $imgmask, $border, $fitbox, $hidden,
            $fitonpage, $alt, $altimgs);
    }

    // Override the method that adds alt text to the PDF
    protected function _printalt($text, $altpos) {
        // Do nothing
    }
}
