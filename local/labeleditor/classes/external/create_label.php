<?php
namespace local_labeleditor\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use moodle_exception;
use context_course;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * External API for creating labels in course sections
 */
class create_label extends external_api {
    
    /**
     * Parameters for create_label function
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'sectionnum' => new external_value(PARAM_INT, 'Section number (0, 1, 2, etc.)'),
            'name' => new external_value(PARAM_TEXT, 'Label name/title'),
            'content' => new external_value(PARAM_RAW, 'HTML content for the label'),
            'visible' => new external_value(PARAM_INT, 'Label visibility (1=visible, 0=hidden)', VALUE_DEFAULT, 1),
            'position' => new external_value(PARAM_INT, 'Position in section (0=end)', VALUE_DEFAULT, 0)
        ]);
    }
    
    /**
     * Create a new label in a course section
     * 
     * @param int $courseid Course ID
     * @param int $sectionnum Section number
     * @param string $name Label name
     * @param string $content HTML content
     * @param int $visible Label visibility (1=visible, 0=hidden)
     * @param int $position Position in section
     * @return array Success status and details
     */
    public static function execute($courseid, $sectionnum, $name, $content, $visible = 1, $position = 0) {
        global $DB, $CFG;
        
        require_once($CFG->dirroot . '/course/modlib.php');
        require_once($CFG->dirroot . '/mod/label/lib.php');
        
        // Validate parameters
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'sectionnum' => $sectionnum,
            'name' => $name,
            'content' => $content,
            'visible' => $visible,
            'position' => $position
        ]);
        
        try {
            // Validate course exists
            $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
            
            // Check permissions
            $context = context_course::instance($course->id);
            self::validate_context($context);
            require_capability('local/labeleditor:edit', $context);
            require_capability('moodle/course:manageactivities', $context);
            
            // Get the target section
            $section = $DB->get_record('course_sections', [
                'course' => $params['courseid'], 
                'section' => $params['sectionnum']
            ], '*', MUST_EXIST);
            
            // Get label module ID
            $labelmodule = $DB->get_record('modules', ['name' => 'label', 'visible' => 1], '*', MUST_EXIST);
            
            // Prepare label data
            $moduleinfo = new \stdClass();
            $moduleinfo->course = $params['courseid'];
            $moduleinfo->section = $params['sectionnum']; // Use section number, not section ID
            $moduleinfo->module = $labelmodule->id;
            $moduleinfo->modulename = 'label';
            $moduleinfo->name = clean_param($params['name'], PARAM_TEXT);
            $moduleinfo->intro = clean_text($params['content'], FORMAT_HTML);
            $moduleinfo->introformat = FORMAT_HTML;
            $moduleinfo->visible = $params['visible'] ? 1 : 0;
            $moduleinfo->visibleoncoursepage = $params['visible'] ? 1 : 0;
            
            // Create the label
            $moduleinfo = add_moduleinfo($moduleinfo, $course, null);
            
            // Handle positioning within section if specified
            if ($params['position'] > 0) {
                try {
                    // Get current section sequence
                    $section = $DB->get_record('course_sections', ['id' => $section->id], '*', MUST_EXIST);
                    $sequence = empty($section->sequence) ? array() : explode(',', $section->sequence);
                    
                    // Remove the new module from its current position (it's at the end)
                    $newcmid = $moduleinfo->coursemodule;
                    $key = array_search($newcmid, $sequence);
                    if ($key !== false) {
                        unset($sequence[$key]);
                        $sequence = array_values($sequence); // Re-index array
                    }
                    
                    // Insert at the specified position (1-based, so subtract 1 for 0-based array)
                    $insertpos = min($params['position'] - 1, count($sequence));
                    $insertpos = max(0, $insertpos); // Ensure it's not negative
                    
                    array_splice($sequence, $insertpos, 0, $newcmid);
                    
                    // Update the section sequence
                    $section->sequence = implode(',', $sequence);
                    $DB->update_record('course_sections', $section);
                    
                    // Rebuild course cache
                    rebuild_course_cache($params['courseid'], true);
                    
                } catch (\Exception $e) {
                    // If positioning fails, log it but don't fail the entire operation
                    error_log("Label created successfully but positioning failed for cmid: " . $moduleinfo->coursemodule . " - " . $e->getMessage());
                }
            }
            
            // Get the created label instance for verification
            $label = $DB->get_record('label', ['id' => $moduleinfo->instance]);
            
            return [
                'success' => true,
                'cmid' => (int)$moduleinfo->coursemodule,
                'labelid' => (int)$moduleinfo->instance,
                'name' => $label->name,
                'sectionnum' => $params['sectionnum'],
                'sectionid' => $section->id,
                'visible' => (bool)$params['visible'],
                'message' => get_string('create_label_success', 'local_labeleditor'),
                'timestamp' => time()
            ];
            
        } catch (\Exception $e) {
            throw new moodle_exception('error_creating_label', 'local_labeleditor', '', null, $e->getMessage());
        }
    }
    
    /**
     * Return structure for create_label function
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'labelid' => new external_value(PARAM_INT, 'Label instance ID'),
            'name' => new external_value(PARAM_TEXT, 'Label name'),
            'sectionnum' => new external_value(PARAM_INT, 'Section number'),
            'sectionid' => new external_value(PARAM_INT, 'Section ID'),
            'visible' => new external_value(PARAM_BOOL, 'Label visibility'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
            'timestamp' => new external_value(PARAM_INT, 'Creation timestamp')
        ]);
    }
}
