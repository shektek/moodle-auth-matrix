<?php
// This project is based on auth_none, which is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Login via the .net Matrix router
 *
 * @package auth_matrix
 * @author Shektek
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

/**
 * Plugin for no authentication.
 */
class auth_plugin_matrix extends auth_plugin_base {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'matrix';
        $this->config = get_config('auth_matrix');
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function auth_plugin_matrix() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    /**
    * Send the login request to the .net router
    * @returns the login request or FALSE
    */
    function send_login_request ($username, $password) {
        list($username_only, $homeserver) = explode(":", $username);
        
        echo $username_only;
        echo $homeserver;

        $data = array(
            'token'       => null,
            'password'    => $password,
            'homeserver'  => $homeserver,
            'username'    => $username_only
        );

        //normal http request
        $opts = array(
            'http' => array(
                'method'=>"POST",
                'header'=>"Accept-language: en\r\n" .
                        "Content-Length " . strlen($data) . "\r\n",
                'content' => json_encode($data)
            )
        );

        $url = 'http://localhost:5000/api/User';

        //if https gets set up, switch to this
        /*
        $opts = array (
            'http' => array (
                'method' => 'POST',
                'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
                    . "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data
            )
        );

        $url = 'http://localhost:5001/api/User';
        */

        $context = stream_context_create($opts);

        /* Sends an http request to www.example.com
           with additional headers shown above */
        $result = file_get_contents($url, false, $context);

        return $result;
	}

    /**
     * Returns true if the username and password work or don't exist and false
     * if the user exists and the password is wrong.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {
        global $CFG, $DB;

        $result = send_login_request($username, $password);

        if($result !== false)
        {
            if($result['user_id'] === $username_only)
            {
                echo($username . ' logged in');
                return true;
            }
		}
        
        echo('error logging in ' . $username);

        return false;
    }

    //TODO: deprecate
    /**
     * Updates the user's password.
     *
     * called when the user password is updated.
     *
     * @param  object  $user        User table object
     * @param  string  $newpassword Plaintext password
     * @return boolean result
     *
     */
    function user_update_password($user, $newpassword) {
        //$user = get_complete_user_data('id', $user->id);
        // This will also update the stored hash to the latest algorithm
        // if the existing hash is using an out-of-date algorithm (or the
        // legacy md5 algorithm).
        //return update_internal_user_password($user, $newpassword);
    }

    //don't let moodle store the hashed password
    function prevent_local_passwords() {
        return true;
    }


    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     * Since this uses Matrix to authenticate, it always returns false
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     * Since this uses Matrix to authenticate, this always returns false
     *
     * @return bool
     */
    function can_reset_password() {
        return false;
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    function can_be_manually_set() {
        return true;
    }

}


