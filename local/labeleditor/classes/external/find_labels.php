<?php
namespace local_labeleditor\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * External API for finding labels in a course
 */
class find_labels extends external_api {
    /**
     * Parameters for find_labels function
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'namefilter' => new external_value(PARAM_TEXT, 'Filter labels by name (optional)', VALUE_DEFAULT, ''),
            'sectionid' => new external_value(PARAM_INT, 'Specific section ID (optional)', VALUE_DEFAULT, 0)
        ]);
    }

    /**
     * Find labels in a course
     *
     * @param int $courseid Course ID
     * @param string $namefilter Filter by label name
     * @param int $sectionid Specific section ID
     * @return array Array of label information
     */
    public static function execute($courseid, $namefilter = '', $sectionid = 0) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'namefilter' => $namefilter,
            'sectionid' => $sectionid
        ]);

        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
        $context = \context_course::instance($course->id);
        self::validate_context($context);
        require_capability('local/labeleditor:view', $context);

        $sql = "SELECT cm.id as cmid, cm.course, cm.section, cm.instance, cm.visible,
                       l.id as labelid, l.name, l.intro, l.timemodified,
                       cs.section as sectionnumber, cs.name as sectionname
                FROM {course_modules} cm
                JOIN {modules} m ON cm.module = m.id
                JOIN {label} l ON cm.instance = l.id
                LEFT JOIN {course_sections} cs ON cm.section = cs.id
                WHERE cm.course = :courseid AND m.name = 'label'";
        $sqlparams = ['courseid' => $params['courseid']];
        if ($params['sectionid'] > 0) {
            $sql .= " AND cm.section = :sectionid";
            $sqlparams['sectionid'] = $params['sectionid'];
        }
        $sql .= " ORDER BY cs.section, cm.id";
        $labels = $DB->get_records_sql($sql, $sqlparams);
        $result = [];
        foreach ($labels as $label) {
            if (!empty($params['namefilter']) && 
                stripos($label->name, $params['namefilter']) === false) {
                continue;
            }
            $result[] = [
                'cmid' => (int)$label->cmid,
                'labelid' => (int)$label->labelid,
                'name' => $label->name,
                'content' => $label->intro,
                'visible' => (bool)$label->visible,
                'sectionnumber' => (int)$label->sectionnumber,
                'sectionname' => $label->sectionname,
                'timemodified' => (int)$label->timemodified
            ];
        }
        return $result;
    }

    /**
     * Return structure for find_labels function
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                'labelid' => new external_value(PARAM_INT, 'Label instance ID'),
                'name' => new external_value(PARAM_TEXT, 'Label name'),
                'content' => new external_value(PARAM_RAW, 'Label HTML content'),
                'visible' => new external_value(PARAM_BOOL, 'Label visibility'),
                'sectionnumber' => new external_value(PARAM_INT, 'Section number'),
                'sectionname' => new external_value(PARAM_TEXT, 'Section name'),
                'timemodified' => new external_value(PARAM_INT, 'Last modified timestamp')
            ])
        );
    }
}
