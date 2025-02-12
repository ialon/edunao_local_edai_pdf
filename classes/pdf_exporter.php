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

namespace local_course_exporter;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/tcpdf/tcpdf.php');

use moodle_database;
use local_course_exporter\module\module_factory;
use TCPDF;
use Exception;

/**
 * Class pdf_exporter
 *
 * Exports a Moodle course to a PDF using TCPDF.
 */
class pdf_exporter {

    private const COVER_TITLE_COLOR = [0, 0, 145];
    private const COVER_SEPARATOR_COLOR = [87, 87, 87];
    private const COVER_AUTHORING_COLOR = [0, 0, 0];
    private const SECTION_TITLE_COLOR = [46, 134, 193];
    private const MODULE_TITLE_COLOR = [93, 173, 226];
    private const FONT_FAMILY = 'helvetica';
    private const FONT_SIZE = 12;
    private const TITLE_FONT_SIZE = 25;
    private const SECTION_FONT_SIZE = 22;
    private const MODULE_FONT_SIZE = 16;
    private const CONTENT_MARGIN_TOP = 10;
    private const INDENTATION = 10;

    private moodle_database $db;
    private module_factory $modulefactory;
    private int $courseid;
    private \stdClass $course;
    private TCPDF $tcpdf;

    /**
     * Constructor.
     *
     * @param moodle_database $db
     * @param int $courseid
     * @throws Exception if course record is not found.
     */
    public function __construct(moodle_database $db, int $courseid) {
        $this->db = $db;
        $this->courseid = $courseid;
        $this->course = $this->db->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $this->modulefactory = new module_factory();
        $this->initialize_tcpdf();
    }

    /**
     * Initialize the TCPDF object with proper settings.
     */
    private function initialize_tcpdf(): void {
        $this->tcpdf = new custom_tcpdf(
            PDF_PAGE_ORIENTATION,
            PDF_UNIT,
            PDF_PAGE_FORMAT,
            true,
            'UTF-8',
            false
        );
        $this->tcpdf->SetCreator(PDF_CREATOR);
        $this->tcpdf->SetAuthor($this->course->fullname);
        $this->tcpdf->SetTitle($this->course->fullname);
        $this->tcpdf->SetSubject('Course Content Export');
        $this->tcpdf->SetKeywords('Moodle, PDF, Export');
        $this->tcpdf->SetHeaderData('', 0, '', '');
        $this->tcpdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $this->tcpdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $this->tcpdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->tcpdf->SetMargins(20, 20, 20);
        $this->tcpdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->tcpdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->tcpdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $this->tcpdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $this->tcpdf->SetFont(self::FONT_FAMILY, '', self::FONT_SIZE);
        $this->tcpdf->setPrintHeader(false);
        $this->tcpdf->setPrintFooter(false);
        $this->tcpdf->setStartingPageNumber(0);
    }

    /**
     * Generate the PDF file and return its path.
     *
     * @return string Path to the generated PDF file.
     * @throws Exception if PDF generation fails.
     */
    public function generate_pdf(): string {
        $this->tcpdf->AddPage();
        $this->add_course_title();
        $this->add_authoring_data();
        $this->add_enrolment_qr();
        $this->tcpdf->Bookmark($this->course->fullname, 0, 0, '', 'B', [0,0,0]);

        $sections = $this->db->get_records('course_sections', ['course' => $this->courseid], 'section ASC');
        $sectionnumber = 1;
        foreach ($sections as $section) {
            if ($section->section == 0) {
                continue;
            }
            $modules = $this->db->get_records('course_modules', ['course' => $this->courseid, 'section' => $section->id], 'id ASC');
            $supportedmodules = [];
            foreach ($modules as $cm) {
                $module = $this->db->get_record('modules', ['id' => $cm->module], '*', MUST_EXIST);
                try {
                    // If module type is supported, create its instance.
                    $this->modulefactory->create($module->name);
                    $supportedmodules[] = $cm;
                } catch (Exception $ex) {
                    // Skip unsupported modules.
                    continue;
                }
            }
            if (empty($supportedmodules)) {
                continue;
            }
            $this->tcpdf->AddPage();
            $this->tcpdf->setPrintFooter(true);
            $this->add_section_title($section, $sectionnumber);
            $this->tcpdf->Bookmark($sectionnumber . '. ' . $section->name, 1, 0, '', '', [0,0,0]);
            if (!empty($section->summary)) {
                $this->add_section_summary($section);
            }
            $modulenumber = 1;
            foreach ($supportedmodules as $cm) {
                $module = $this->db->get_record('modules', ['id' => $cm->module], '*', MUST_EXIST);
                $moduleinstance = $this->modulefactory->create($module->name);
                $cminstance = $this->db->get_record($module->name, ['id' => $cm->instance], '*', MUST_EXIST);

                // Add Module Title with numbering.
                $this->add_module_title($cm, $cminstance, $sectionnumber, $modulenumber);

                $this->tcpdf->Bookmark(
                    $sectionnumber . '.' . $modulenumber . ' ' . htmlspecialchars($cminstance->name),
                    2,
                    0,
                    '',
                    '',
                    [0,0,0]
                );

                // Add Module intro if exists.
                if (!empty($cminstance->intro)) {
                    $this->add_module_intro($cminstance, $cm->id);
                }

                // Optionally add a module title header.
                $modulehtml = $moduleinstance->export_to_pdf($this->db, $cm);
                $this->add_module_content($modulehtml);
                $modulenumber++;
            }
            $sectionnumber++;
        }
        $tempdir = make_temp_directory('local_course_exporter');
        $filename = 'course_' . $this->courseid . '_' . time() . '.pdf';
        $filepath = $tempdir . '/' . $filename;
        try {
            $this->tcpdf->Output($filepath, 'F');
        } catch (Exception $e) {
            throw new Exception('Failed to generate PDF: ' . $e->getMessage());
        }
        return $filepath;
    }

    /**
     * Adds a background image to the PDF.
     */
    private function add_background_image(): void {
        // Disable AutoPageBreak
        $this->tcpdf->SetAutoPageBreak(false, 0);

        // Print background image
        $width = $this->tcpdf->getPageWidth();
        $height = $this->tcpdf->getPageHeight();
        $imgurl = "https://marketplace.canva.com/EAGVTVfRE_I/1/0/1131w/canva-bue-and-white-watercolor-background-document-a4-0ziHZDR-m-Y.jpg";
        $this->tcpdf->Image($imgurl, 0, 0, $width, $height, '', '', '', false, 300, '', false, false, 0);

        // Reset the starting point for the page content
        $this->tcpdf->setPageMark();

        // Reset AutoPageBreak.
        $this->tcpdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    }

    /**
     * Add the course title and logo to the PDF.
     */
    private function add_course_title(): void {
        $logopath = $this->get_site_logo_url();
        $this->tcpdf->Image($logopath, 90, 20, '', 30, 'PNG', '', 'T', false, 300, 'C', false, false, 0, false, false, false);
        $this->tcpdf->SetY(60 + self::CONTENT_MARGIN_TOP);
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'B', self::TITLE_FONT_SIZE);
        $this->tcpdf->SetTextColor(...self::COVER_TITLE_COLOR);
        $this->tcpdf->MultiCell(0, 0, $this->course->fullname ?? '', 0, 'C', 0, 1);
        $this->tcpdf->Ln(5);
        $this->tcpdf->SetDrawColor(...self::COVER_SEPARATOR_COLOR);
        $this->tcpdf->SetLineWidth(0.5);
        $this->tcpdf->Line(20, $this->tcpdf->GetY(), 190, $this->tcpdf->GetY());
        $this->tcpdf->SetTextColor(0, 0, 0);
        $this->tcpdf->Ln(10);
    }

    /**
     * Add authoring data (published by, teacher names, export date).
     */
    private function add_authoring_data(): void {
        global $DB;
        $context = \context_course::instance($this->course->id);
        $role = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $teachers = \get_role_users($role->id, $context);
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'B', 16);
        $this->tcpdf->SetTextColor(...self::COVER_AUTHORING_COLOR);
        $this->tcpdf->Ln(20);
        $publishedby = get_string('publishedby', 'local_course_exporter');
        $this->tcpdf->Cell(0, 10, $publishedby, 0, 1, 'C');
        foreach ($teachers as $teacher) {
            $this->tcpdf->Cell(0, 10, fullname($teacher), 0, 1, 'C');
            $this->tcpdf->Ln(-2);
        }
        $this->tcpdf->Ln(20);
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'R', 14);
        $exportdate = userdate(time(), get_string('strftimedaydate', 'langconfig'));
        $this->tcpdf->Cell(0, 10, $exportdate, 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->tcpdf->SetTextColor(0, 0, 0);
        $this->tcpdf->Ln(40);
    }

    /**
     * Add a QR code for course enrolment.
     */
    private function add_enrolment_qr(): void {
        global $CFG;
        $enrolurl = $CFG->wwwroot . '/enrol/index.php?id=' . $this->course->id;
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'R', 12);
        $this->tcpdf->Cell(0, 10, get_string('scantoenrol', 'local_course_exporter'), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $newyposition = $this->tcpdf->GetY() + 10;
        $this->tcpdf->write2DBarcode($enrolurl, 'QRCODE', '93', $newyposition, 25, 25, null, 'C');
        $this->tcpdf->SetTextColor(0, 0, 0);
    }

    /**
     * Add a section title.
     *
     * @param object $section
     * @param int $sectionnumber
     */
    private function add_section_title(object $section, int $sectionnumber): void {
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'B', self::SECTION_FONT_SIZE);
        $this->tcpdf->SetTextColor(...self::SECTION_TITLE_COLOR);
        $this->tcpdf->MultiCell(0, 0, $section->name ?? '', 0, 'L', 0, 1);
        $this->tcpdf->SetTextColor(0, 0, 0);
        $this->tcpdf->Ln(5);
    }

    /**
     * Add a module title (optional header for each module).
     *
     * @param object $cm
     * @param object $cminstance
     * @param int $sectionnumber
     * @param int $modulenumber
     */
    private function add_module_title(object $cm, object $cminstance, int $sectionnumber, int $modulenumber): void {
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'B', self::MODULE_FONT_SIZE);
        $this->tcpdf->SetTextColor(...self::MODULE_TITLE_COLOR);
        $this->tcpdf->MultiCell(0, 0, $cminstance->name ?? '', 0, 'L', 0, 1);
        $this->tcpdf->SetTextColor(0, 0, 0);
        $this->tcpdf->Ln(3);
    }

    /**
     * Add module content HTML.
     *
     * @param string $modulehtml
     */
    private function add_module_content(string $modulehtml): void {
        $this->tcpdf->SetFont(self::FONT_FAMILY, '', self::FONT_SIZE);
        $this->tcpdf->writeHTML($modulehtml, true, false, true, false, '');
        $this->tcpdf->Ln(5);
    }

    /**
     * Adds the module intro to the PDF.
     *
     * @param object $cminstance The module instance containing the intro.
     * @param int $cmid The course module ID.
     */
    private function add_module_intro(object $cminstance, int $cmid): void {
        $introformat = property_exists($cminstance, 'introformat') ? $cminstance->introformat : FORMAT_HTML;
        $formattedintro = format_text($cminstance->intro, $introformat, ['context' => \context_module::instance($cmid)]);
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'R', self::FONT_SIZE);
        $this->tcpdf->writeHTMLCell(0, 0, '', '', $formattedintro, 0, 1, false, true, 'L', true);
        $this->tcpdf->Ln(3);
    }

    /**
     * Add a section summary.
     *
     * @param object $section
     */
    private function add_section_summary(object $section): void {
        $summaryformat = property_exists($section, 'summaryformat') ? $section->summaryformat : FORMAT_HTML;
        $formattedsummary = format_text($section->summary, $summaryformat, ['context' => \context_course::instance($this->courseid)]);
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'R', self::FONT_SIZE);
        $this->tcpdf->writeHTMLCell(0, 0, '', '', $formattedsummary, 0, 1, false, true, 'L', true);
        $this->tcpdf->SetTextColor(0, 0, 0);
        $this->tcpdf->Ln(5);
    }

    /**
     * Retrieve the site logo URL.
     *
     * @return string
     */
    private function get_site_logo_url(): string {
        global $OUTPUT;
        return $OUTPUT->get_logo_url();
    }
}
