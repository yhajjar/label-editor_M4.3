<?php
namespace local_labeleditor\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_course;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * External API for getting course sections for label placement
 */
class get_sections extends external_api {
    
    /**
     * Parameters for get_sections function
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID')
        ]);
    }
    
    /**
     * Get course sections for label placement
     * 
     * @param int $courseid Course ID
     * @return array Array of section information
     */
    public static function execute($courseid) {
        global $DB;
        
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid
        ]);
        
        // Validate course and permissions
        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
        $context = context_course::instance($course->id);
        self::validate_context($context);
        require_capability('local/labeleditor:view', $context);
        
        // Get course sections
        $sections = $DB->get_records('course_sections', 
            ['course' => $params['courseid']], 
            'section ASC'
        );
        
        $result = [];
        foreach ($sections as $section) {
            // Count existing labels in section
            $labelcount = 0;
            $totalmodules = 0;
            
            if (!empty($section->sequence)) {
                $moduleids = explode(',', $section->sequence);
                $totalmodules = count($moduleids);
                
                // Count labels specifically
                if ($totalmodules > 0) {
                    $placeholders = implode(',', array_fill(0, count($moduleids), '?'));
                    $sql = "SELECT COUNT(*) FROM {course_modules} cm 
                            JOIN {modules} m ON cm.module = m.id 
                            WHERE cm.id IN ($placeholders) 
                            AND m.name = 'label'";
                    $labelcount = $DB->count_records_sql($sql, $moduleids);
                }
            }
            
            $result[] = [
                'id' => (int)$section->id,
                'section' => (int)$section->section,
                'name' => $section->name ?: "Section {$section->section}",
                'summary' => $section->summary ?: '',
                'visible' => (bool)$section->visible,
                'labelcount' => $labelcount,
                'totalmodules' => $totalmodules
            ];
        }
        
        return $result;
    }
    
    /**
     * Return structure for get_sections function
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Section ID'),
                'section' => new external_value(PARAM_INT, 'Section number'),
                'name' => new external_value(PARAM_TEXT, 'Section name'),
                'summary' => new external_value(PARAM_RAW, 'Section summary'),
                'visible' => new external_value(PARAM_BOOL, 'Section visibility'),
                'labelcount' => new external_value(PARAM_INT, 'Number of labels in section'),
                'totalmodules' => new external_value(PARAM_INT, 'Total modules in section')
            ])
        );
    }
}
