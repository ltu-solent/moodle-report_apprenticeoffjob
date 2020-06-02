<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2020060200;   // The (date) version of this plugin
$plugin->requires = 2018120304;   // Requires this Moodle version
$plugin->component = 'report_apprenticeoffjob';
$plugin->dependencies = array(
    'local_apprenticeoffjob' => 2020060200,   // The Foo activity must be present (any version).
);
