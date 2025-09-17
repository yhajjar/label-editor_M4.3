<?php
defined('MOODLE_INTERNAL') || die();

$plugin->type = 'local';
$plugin->component = 'local_labeleditor';
$plugin->version = 2025072004;
$plugin->requires = 2024042200; // Moodle 4.5.0
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.1.3';
$plugin->dependencies = array(
    'mod_label' => 2024042200
);
