<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_matrix/pluginname', '',
        new lang_string('auth_matrixdescription', 'auth_matrix')));

    // Display locking / mapping of profile fields.
    $authplugin = get_auth_plugin('matrix');
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
        get_string('auth_fieldlocks_help', 'auth'), false, false);
}
