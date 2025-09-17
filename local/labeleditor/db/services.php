<?php
// Web service functions and service definition for local_labeleditor

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_labeleditor_update_label' => array(
        'classname'   => 'local_labeleditor\\external\\update_label',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Update label module content',
        'type'        => 'write',
        'capabilities' => 'local/labeleditor:edit',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'loginrequired' => true,
    ),
    'local_labeleditor_find_labels' => array(
        'classname'   => 'local_labeleditor\\external\\find_labels',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Find labels in a course',
        'type'        => 'read',
        'capabilities' => 'local/labeleditor:view',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'loginrequired' => true,
    ),
    'local_labeleditor_create_label' => array(
        'classname'   => 'local_labeleditor\\external\\create_label',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Create a new label in a course section',
        'type'        => 'write',
        'capabilities' => 'local/labeleditor:edit',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'loginrequired' => true,
    ),
    'local_labeleditor_get_sections' => array(
        'classname'   => 'local_labeleditor\\external\\get_sections',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Get course sections for label placement',
        'type'        => 'read',
        'capabilities' => 'local/labeleditor:view',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'loginrequired' => true,
    )
);

$services = array(
    'Label Editor Service' => array(
        'functions' => array(
            'local_labeleditor_update_label',
            'local_labeleditor_find_labels',
            'local_labeleditor_create_label',
            'local_labeleditor_get_sections',
            'core_course_get_contents'
        ),
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'labeleditor',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    )
);
