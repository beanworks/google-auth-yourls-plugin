<?php
/*
Plugin Name: Google Authentication YOURLS Plugin
Plugin URI: https://github.com/beanworks/google-auth-yourls-plugin
Description: This plugin enables authentation against Google. Based on the plugin by 8thwall.
Version: 1.0
Author: Beanworks
Author URI: http://github.com/beanworks
*/

// No direct call
if (!defined('YOURLS_ABSPATH')) {
    die();
}

/* Assumes that you have already downloaded and installed the
 * Google APIs Client Library for PHP and it's in the same directory.
 * See https://github.com/google/google-api-php-client for install instructions.
 * Include your composer dependencies:
 */
require_once __DIR__ . '/../../../includes/vendor/autoload.php';

/* The function yourls_is_valid_user() in includes/functions-auth.php checks for a valid user via the login
 * form or stored cookie. The 'shunt_is_valid_user' filter allows plugins such as this one, to short-circuit
 * the entire function.
 */

/* This says: when filter 'shunt_is_valid_user' is triggered, execute function 'atomarch_google_auth'
 * and send back it's return value. Filters should always have a return value.
 */
yourls_add_filter('shunt_is_valid_user', 'atomarch_google_auth');

function atomarch_google_auth() {

    session_start();
    $client = new Google_Client();
    $client->addScope('profile');
    $client->addScope('email');
    $client->setAccessType('offline');

    // See https://developers.google.com/api-client-library/php/auth/web-app to create
    // an OAuth 2.0 client ID, and download the resulting JSON file
    // This assumes that client_secrets.json file resides in the same directory as plugin.php
    $client->setAuthConfig(dirname(__FILE__) . '/client_secrets.json');

    $client->setRedirectUri(yourls_admin_url());

    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
        // User has already authenticated against google with an approved domain, nothing to do
        return true;

    } else {

        if (!isset($_GET['code'])) {

            // Generate a URL to request access from Google's OAuth 2.0 server
            $auth_url = $client->createAuthUrl();
            // Redirect the user to $auth_url so they can enter their Google credentials
            header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));

        } else {
            // Exchange an authorization code for an access token
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

	    if (!array_key_exists('access_token', $token)) {
	    	yourls_e("invalid token");
		die();
	    }
            //Store Access Token in a session variable
            $_SESSION['access_token'] = $token;

            if (atomarch_check_domain($client) === false) {
                $client->revokeToken();
                unset($_SESSION['access_token']);
                yourls_e("User from Unauthorized Domain.");
                die();
            }

            $redirect_uri = yourls_admin_url();
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }
    }
}

function atomarch_check_domain($google_client) {
    if (APPROVED_DOMAIN == "*") {
        return true;
    }

    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {

        $google_oauthV2 = new Google_Service_Oauth2($google_client);
        $user_info = $google_oauthV2->userinfo->get();
        $user_domain = substr(strrchr($user_info['email'], "@"), 1);

        if (APPROVED_DOMAIN == $user_domain) {
            return true;
        } else {
            return false;
        }
    }
}

/*
 * Register the plugin admin page
 */
yourls_add_action( 'plugins_loaded', 'google_auth_init' );
function google_auth_init() {
    yourls_register_plugin_page( 'google_auth', 'Google Auth Settings', 'google_auth_display_page' );
}

/*
 * Draw the plugin admin page
 */
function google_auth_display_page() {
    ?>
    <h3><?php yourls_e( 'Google Auth Settings' ); ?></h3>
        <p><?php yourls_se( "Approved Google domains: %s", APPROVED_DOMAIN ); ?></p>
    <?php
}
