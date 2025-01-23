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

use moodle_database;
use local_edai_pdf\module\module_factory;
use Exception;
use TCPDF;

class pdf_exporter {
    private const COVER_TITLE_COLOR = [0, 0, 145]; // RGB array for #000091
    private const COVER_SEPARATOR_COLOR = [87, 87, 87]; // RGB array for #575757
    private const COVER_AUTHORING_COLOR = [0, 0, 0]; // RGB array for #000000
    private const SECTION_TITLE_COLOR = [46, 134, 193]; // RGB array for #2E86C1
    private const MODULE_TITLE_COLOR = [93, 173, 226];  // RGB array for #5DADE2
    private const FONT_FAMILY = 'helvetica';
    private const FONT_SIZE = 12;
    private const TITLE_FONT_SIZE = 25;
    private const SECTION_FONT_SIZE = 22;
    private const MODULE_FONT_SIZE = 16;
    private const CONTENT_MARGIN_TOP = 10; // in mm
    private const INDENTATION = 10; // in mm
    private moodle_database $db;
    private module_factory $modulefactory;
    private int $courseid;
    private \stdClass $course;
    private TCPDF $tcpdf;

    public function __construct(
        moodle_database $db,
        int $courseid
    ) {
        $this->db = $db;
        $this->courseid = $courseid;
        $this->course = $this->db->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $this->modulefactory = new module_factory();
        $this->initialize_tcpdf();
    }

    /**
     * Initializes the TCPDF instance with desired configurations.
     */
    private function initialize_tcpdf(): void {
        // Create new PDF document.
        $this->tcpdf = new custom_tcpdf(
            PDF_PAGE_ORIENTATION,
            PDF_UNIT,
            PDF_PAGE_FORMAT,
            true,
            'UTF-8',
            false
        );

        // Set document information.
        $this->tcpdf->SetCreator(PDF_CREATOR);
        $this->tcpdf->SetAuthor($this->course->fullname);
        $this->tcpdf->SetTitle($this->course->fullname);
        $this->tcpdf->SetSubject('Course Content Export');
        $this->tcpdf->SetKeywords('Moodle, PDF, Export');

        // Set default header data.
        $this->tcpdf->SetHeaderData('', 0, '', '');

        // Set header and footer fonts.
        $this->tcpdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $this->tcpdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

        // Set default monospaced font.
        $this->tcpdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Set margins.
        $this->tcpdf->SetMargins(20, 20, 20); // Increased margins for better whitespace
        $this->tcpdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->tcpdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // Set auto page breaks.
        $this->tcpdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // Set image scale factor.
        $this->tcpdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Set default font.
        $this->tcpdf->SetFont(self::FONT_FAMILY, '', self::FONT_SIZE);

        // Remove default header and footer.
        $this->tcpdf->setPrintHeader(false);
        $this->tcpdf->setPrintFooter(false);

        // Start counting pages from zero.
        $this->tcpdf->setStartingPageNumber(0);
    }

    /**
     * Generates the PDF for the specified course.
     *
     * @return string The path to the generated PDF file.
     * @throws Exception If PDF generation fails.
     */
    public function generate_pdf(): string {
        // Add a page.
        $this->tcpdf->AddPage();

        // Add Background Image.
        // $this->add_background_image();

        // Add Course Title.
        $this->add_course_title();

        // Add Authoring Data.
        $this->add_authoring_data();

        // Add Course Enrolment QR Code
        $this->add_enrolment_qr();

        // Bookmark for the course.
        $this->tcpdf->Bookmark($this->course->fullname, 0, 0, '', 'B', array(0,0,0));

        // Fetch Course Sections.
        $sections = $this->db->get_records('course_sections', ['course' => $this->courseid], 'section ASC');

        // Initialize section numbering.
        $sectionnumber = 1;

        foreach ($sections as $section) {
            // Skip general section.
            if ($section->section == 0) {
                continue;
            }

            // Fetch Modules in the Section.
            $modules = $this->db->get_records('course_modules', ['course' => $this->courseid, 'section' => $section->id], 'id ASC');
            // Check if the section has at least one supported module.
            $supportedmodules = [];
            foreach ($modules as $cm) {
                $module = $this->db->get_record('modules', ['id' => $cm->module], '*', MUST_EXIST);
                try {
                    $moduleinstance = $this->modulefactory->create($module->name);
                    $supportedmodules[] = $cm;
                } catch (Exception $ex) {
                    // Module not supported, skip.
                    continue;
                }
            }

            // If no supported modules, skip the section.
            if (empty($supportedmodules)) {
                continue;
            }

            // Start each section on a new page.
            $this->tcpdf->AddPage();

            // Enable page footer for the rest of the pages.
            $this->tcpdf->setPrintFooter(true);

            // Add Section Title with numbering.
            $this->add_section_title($section, $sectionnumber);

            // Bookmark for the section with numbering.
            $this->tcpdf->Bookmark($sectionnumber . '. ' . $section->name, 1, 0, '', '', array(0,0,0));

            // Add Section Summary if exists.
            if (!empty($section->summary)) {
                $this->add_section_summary($section);
            }

            // Initialize module numbering within the section.
            $modulenumber = 1;
            foreach ($supportedmodules as $cm) {
                $module = $this->db->get_record('modules', ['id' => $cm->module], '*', MUST_EXIST);
                $moduleinstance = $this->modulefactory->create($module->name);

                // Fetch the module instance.
                $cminstance = $this->db->get_record($module->name, ['id' => $cm->instance], '*', MUST_EXIST);

                // Add Module Title with numbering.
                // Commented out temporarily
                /*
                $this->add_module_title($cm, $cminstance, $sectionnumber, $modulenumber);
                */

                // Bookmark for the module with numbering.
                $this->tcpdf->Bookmark(
                    $sectionnumber . '.' . $modulenumber . ' ' . htmlspecialchars($cminstance->name),
                    2,
                    0,
                    '',
                    '',
                    array(0,0,0)
                );

                // Add Module intro if exists.
                // Commented out temporarily
                /*
                if (!empty($cminstance->intro)) {
                    $this->add_module_intro($cminstance, $cm->id);
                }
                */

                // Add Module Content.
                $modulehtml = $moduleinstance->export_to_pdf($this->db, $cm);
                $this->add_module_content($modulehtml);

                // Increment module numbering.
                $modulenumber++;
            }

            // Increment section numbering.
            $sectionnumber++;
        }

        // Output PDF to a temporary file.
        $tempdir = make_temp_directory('edai_pdf_exports');
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
     * Adds the course title and logo to the PDF.
     */
    private function add_course_title(): void {
        // Logo URL.
        $logopath = $this->get_site_logo_url();

        // Add the Logo image to the PDF cover page.
        $this->tcpdf->Image($logopath, 90, 20, '', 30, 'PNG', '', 'T', false, 300, 'C', false, false, 0, false, false, false);

        // Adjust the position for the course title.
        $this->tcpdf->SetY(60 + self::CONTENT_MARGIN_TOP); // Added margin

        // Add Course Title.
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'B', self::TITLE_FONT_SIZE);
        $this->tcpdf->SetTextColor(...self::COVER_TITLE_COLOR);
        $this->tcpdf->MultiCell(0, 0, $this->course->fullname ?? '', 0, 'C', 0, 1);

        // Add a horizontal line.
        $this->tcpdf->Ln(5); // Line break.
        $this->tcpdf->SetDrawColor(...self::COVER_SEPARATOR_COLOR);
        $this->tcpdf->SetLineWidth(0.5);
        $this->tcpdf->Line(20, $this->tcpdf->GetY(), 190, $this->tcpdf->GetY());

        // Reset the text color to black (default) for other content.
        $this->tcpdf->SetTextColor(0, 0, 0);

        // Add some vertical space.
        $this->tcpdf->Ln(10);
    }

    /**
     * Adds the course author and date of export data to the cover page of the PDF.
     */
    private function add_authoring_data(): void {
        global $DB, $USER;

        // Get the course context.
        $context = \context_course::instance($this->course->id);

        // Get the 'editingteacher' role ID.
        $role = $DB->get_record('role', ['shortname' => 'editingteacher']);

        // Get all users with the 'editingteacher' role in this course context.
        $teachers = \get_role_users($role->id, $context);

        // Set position and styling for teacher's full name.
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'B', 16);
        $this->tcpdf->SetTextColor(...self::COVER_AUTHORING_COLOR);

        // Add some vertical space.
        $this->tcpdf->Ln(20);

        // Add the teacher's full name under horizontal line.
        $publishedby = get_string('publishedby', 'local_edai_pdf');
        $this->tcpdf->Cell(0, 10, $publishedby, 0, 1, 'C');

        foreach ($teachers as $teacher) {
            $this->tcpdf->Cell(0, 10, fullname($teacher), 0, 1, 'C');
            // Add some vertical space.
            $this->tcpdf->Ln(-2);
        }

        // Set position and styling for export date.
        $this->tcpdf->Ln(20);
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'R', 14);
        $exportdate = userdate(time(), get_string('strftimedaydate', 'langconfig'));

        // Add the export date under the teacher's full name.
        $this->tcpdf->Cell(0, 10, $exportdate, 0, false, 'C', 0, '', 0, false, 'T', 'M');

        // Reset the text color to black (default) for other content.
        $this->tcpdf->SetTextColor(0, 0, 0);

        // Add some vertical space.
        $this->tcpdf->Ln(40);
    }

    /**
     * Adds a QR code for Course Enrolment.
     */
    private function add_enrolment_qr(): void {
        Global $CFG;

        /// Get the course enrollment URL.
        $enrolurl = $CFG->wwwroot . '/enrol/index.php?id=' . $this->course->id;

        // Set position and styling for text above QR Code.
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'R', 12);

        // Add Text above QR Code.
        $this->tcpdf->Cell(0, 10, get_string('scantoenrol', 'local_edai_pdf'), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        // Get the last Y position and add to it
        $newyposition = $this->tcpdf->GetY() + 10;

        // Add the Enrolment QR Code.
        $this->tcpdf->write2DBarcode($enrolurl, 'QRCODE', '93', $newyposition, 25, 25, null, 'C');

        // Reset the text color to black (default) for other content.
        $this->tcpdf->SetTextColor(0, 0, 0);
    }

    /**
     * Adds the section title to the PDF with numbering.
     *
     * @param object $section The course section record.
     * @param int $sectionnumber The current section number.
     */
    private function add_section_title(object $section, int $sectionnumber): void {
        // Set font for section title
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'B', self::SECTION_FONT_SIZE);
        // Set color for section title
        $this->tcpdf->SetTextColor(...self::SECTION_TITLE_COLOR);
        // Add section title
        $this->tcpdf->MultiCell(0, 0, $section->name ?? '', 0, 'L', 0, 1);
        // Reset text color to black
        $this->tcpdf->SetTextColor(0, 0, 0);
        // Add spacing after section title
        $this->tcpdf->Ln(5);
    }

    /**
     * Adds the module title to the PDF with numbering.
     *
     * @param object $cm The course module.
     * @param object $cminstance The module instance.
     * @param int $sectionnumber The current section number.
     * @param int $modulenumber The current module number within the section.
     */
    private function add_module_title(object $cm, object $cminstance, int $sectionnumber, int $modulenumber): void {
        // Set font for module title
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'B', self::MODULE_FONT_SIZE);
        // Set color for module title
        $this->tcpdf->SetTextColor(...self::MODULE_TITLE_COLOR);
        // Add module title
        $this->tcpdf->MultiCell(0, 0, $cminstance->name ?? '', 0, 'L', 0, 1);
        // Reset text color to black
        $this->tcpdf->SetTextColor(0, 0, 0);
        // Add spacing after module title
        $this->tcpdf->Ln(3);
    }

    /**
     * Adds the module content to the PDF.
     *
     * @param string $modulehtml The HTML content of the module.
     */
    private function add_module_content(string $modulehtml): void {
        // Set font for content
        $this->tcpdf->SetFont(self::FONT_FAMILY, '', self::FONT_SIZE);
        // Write HTML content
        $this->tcpdf->writeHTML($modulehtml, true, false, true, false, '');
        // Add spacing after content
        $this->tcpdf->Ln(5);
    }

    /**
     * Adds the module intro to the PDF.
     *
     * @param object $cminstance The module instance containing the intro.
     * @param int $cmid The course module ID.
     */
    private function add_module_intro(object $cminstance, int $cmid): void {
        // Check if 'introformat' exists; if not, default to FORMAT_HTML.
        $introformat = property_exists($cminstance, 'introformat') ? $cminstance->introformat : FORMAT_HTML;

        // Process the intro based on its format.
        $formattedintro = format_text($cminstance->intro, $introformat, ['context' => \context_module::instance($cmid)]);

        // Set font for intro.
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'I', self::FONT_SIZE);
        // Write the formatted intro.
        $this->tcpdf->writeHTMLCell(0, 0, '', '', $formattedintro, 0, 1, false, true, 'L', true);
        // Add spacing after intro.
        $this->tcpdf->Ln(3);
    }

    /**
     * Adds the section summary to the PDF.
     *
     * @param object $section The course section record.
     */
    private function add_section_summary(object $section): void {
        // Check if 'summaryformat' exists; if not, default to FORMAT_HTML.
        $summaryformat = property_exists($section, 'summaryformat') ? $section->summaryformat : FORMAT_HTML;

        // Process the summary based on its format.
        $formattedsummary = format_text($section->summary, $summaryformat, ['context' => \context_course::instance($this->courseid)]);

        // Set font for summary.
        $this->tcpdf->SetFont(self::FONT_FAMILY, 'R', self::FONT_SIZE);
        // Write the formatted summary.
        $this->tcpdf->writeHTMLCell(0, 0, '', '', $formattedsummary, 0, 1, false, true, 'L', true);
        // Reset text color to black if changed.
        $this->tcpdf->SetTextColor(0, 0, 0);
        // Add spacing after summary.
        $this->tcpdf->Ln(5);
    }

    /**
     * Safely adds a new page if the current Y position exceeds a threshold.
     */
    private function safe_add_page(): void {
        if ($this->tcpdf->getPage() > 0) {
            $topmargin = 10;
            if ($this->tcpdf->getY() > $topmargin) {
                $this->tcpdf->AddPage();
            }
        }
    }

    /**
     * Retrieves the URL to the plugin's logo.
     *
     * @return string The URL to the logo image.
     */
    private function get_site_logo_url(): string {
        global $OUTPUT;
        return $OUTPUT->get_logo_url();
    }

}
