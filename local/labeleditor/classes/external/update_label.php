<?php
namespace local_labeleditor\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * External API for updating label content
 */
class update_label extends external_api {
    /**
     * Parameters for update_label function
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID of the label'),
            'content' => new external_value(PARAM_RAW, 'New HTML content for the label'),
            'name' => new external_value(PARAM_TEXT, 'New name for the label (optional)', VALUE_DEFAULT, null)
        ]);
    }

    /**
     * Update label content
     *
     * @param int $cmid Course module ID
     * @param string $content New HTML content
     * @param string|null $name New name for the label
     * @return array Success status and details
     */
    public static function execute($cmid, $content, $name = null) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'content' => $content,
            'name' => $name
        ]);

        try {
            $cm = get_coursemodule_from_id('label', $params['cmid'], 0, false, MUST_EXIST);
            $context = \context_module::instance($cm->id);
            self::validate_context($context);
            require_capability('local/labeleditor:edit', $context);
            $label = $DB->get_record('label', ['id' => $cm->instance], '*', MUST_EXIST);
            $updatedata = new \stdClass();
            $updatedata->id = $label->id;
            $updatedata->intro = clean_text($params['content'], FORMAT_HTML);
            $updatedata->timemodified = time();
            if ($params['name'] !== null) {
                $updatedata->name = clean_text($params['name'], PARAM_TEXT);
                $DB->set_field('course_modules', 'name', $updatedata->name, ['id' => $cm->id]);
            }
            $DB->update_record('label', $updatedata);
            $event = \core\event\course_module_updated::create([
                'courseid' => $cm->course,
                'context' => $context,
                'objectid' => $cm->id,
                'other' => [
                    'modulename' => 'label',
                    'instanceid' => $label->id,
                    'name' => $cm->name
                ]
            ]);
            $event->add_record_snapshot('course_modules', $cm);
            $event->add_record_snapshot('label', $label);
            $event->trigger();
            rebuild_course_cache($cm->course, true);
            return [
                'success' => true,
                'cmid' => $params['cmid'],
                'message' => get_string('update_label', 'local_labeleditor'),
                'timestamp' => time()
            ];
        } catch (\Exception $e) {
            throw new moodle_exception('error_updating_label', 'local_labeleditor', '', null, $e->getMessage());
        }
    }

    /**
     * Return structure for update_label function
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
            'timestamp' => new external_value(PARAM_INT, 'Update timestamp')
        ]);
    }
}
