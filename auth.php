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
    function send_login_request ($homeserver, $username_only, $password) {
        $homeserver = 'http://' . $homeserver;

        $data = array(
            'token'       => null,
            'password'    => $password,
            'homeserver'  => $homeserver,
            'username'    => $username_only
        );

        echo('sending request for user ' . $username_only . ' on homeserver ' . $homeserver . "\r\n");

        //normal http request
        $opts = array(
            'http' => array(
                'method'    => "POST",
                'ignore_errors' => true,
                'content'   => json_encode($data),
                'header'    => "Content-Type: application/json\r\n" .
                                "Accept: application/json\r\n"
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

        print_r($result);

        if($result !== FALSE && !empty($result)) {
            $status_line = $result[0];
            preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $matches);

            $status_code = $matches[1];

            if($status_code === "401") {
                $result = null;
		    }
            else {
                $result = json_decode($result, true);     
			}
        }
        else {
            $result = null;  
		}

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
        //first try extracting the username and homeserver from the username string
        list($username_only, $homeserver) = array_pad(explode("@", $username), 2, null);
        
        if($username_only === null) {
            $username_only = $username;  
		}

        //if no homeserver is specified, set it to matrix.org
        if($homeserver === null) {
            $homeserver = 'matrix.org';
		}

        $result = $this->send_login_request($homeserver, $username_only, $password);

        if($result !== null)
        {
            echo('user from JSON is called ' . $result['user_id'] . "\r\n");

            if(strstr($result['user_id'], $username_only) !== FALSE)
            {
                echo("Success! " . $username . " logged in.\r\n");
                return true;
            }
	    }
        
        echo('Failed logging in user ' . $username . "\r\n");
        
        return false;
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

    function is_synchronised_with_external() {
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
}


