<?php

namespace auth_matrix\privacy;
defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for auth_matrix implementing null_provider.
 */
class provider implements \core_privacy\local\metadata\null_provider {
    public static function get_reason() : string {
        return 'privacy:metadata';
    }
}