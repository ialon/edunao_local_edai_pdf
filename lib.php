<?php

/**
 * Serves files for PDF export.
 *
 * @package  local_edai_pdf
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function local_edai_pdf_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG;
    require_once("$CFG->libdir/resourcelib.php");

    // We should check for the capability to export PDF 
    // if (!has_capability('mod/page:view', $context)) {
    //     return false;
    // }

    // We need to extract the real component and filearea from the $filearea
    // If the real filearea contains an underscore, this code will not work
    $parts = explode('_', $filearea);
    $filearea = array_pop($parts);
    $component = implode('_', $parts);

    $arg = array_shift($args);
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/$component/$filearea/0/$relativepath";

    $file = $fs->get_file_by_hash(sha1($fullpath));

    // finally send the file
    send_stored_file($file, null, 0, $forcedownload, $options);
}