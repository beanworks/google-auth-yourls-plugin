google-auth-yourls-plugin
=========================

This plugin for [YOURLS](https://github.com/YOURLS/YOURLS) allows the use of Google OAuth login to filteraccess based on domain (i.e. only @mydomain.com addresses)

Configuration
------------
1. Install the plugin in user/plugins in a folder called google-auth
2. Download and configure Google APIs Client Library for PHP in includes/vendor/. See https://github.com/google/google-api-php-client for install instructions.
3. Create an OAuth 2.0 client ID. Download the resulting JSON file and save it in the google-auth/ plugin directory with the name client_secrets.json
4. Define an array of allowed domains in user/config called domainlimit_list. For example, `$domainlimit_list = array( 'mydomain.com', 'otherdomain.com' );`
5. Add 'define('APPROVED_DOMAIN', "mydomain.com");' to user/config.php with the desired Google login domain. Optionally, allow anyone with a Google account to get in by setting the domain to '\*'. For example, 'define('APPROVED_DOMAIN', "*");'
6. Optionally defined a list of usernames that are exempt from this restriction. For example, `domainlimit_exempt_users = array( 'bobadmin' );`
7. Activate the plugin with the plugin manager in the admin interface.

<sub>Based on plugin by 8thwall</sub>
