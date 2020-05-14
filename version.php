<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2020020305;   // The (date) version of this plugin
$plugin->requires = 2015111603;   // Requires this Moodle version
$plugin->component = 'report_apprenticeoffjob';
$plugin->dependencies = array(
    'local_apprenticeoffjob' => 2020012303,   // The Foo activity must be present (any version).
);
