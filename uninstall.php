<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://clicksend.com/help
 * @since      1.0.0
 *
 * @package    Clicksend_Contactform7
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}



function delete_clicksend_table_on_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix.'clicksend_messages';

    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
        // Table exists, so drop it
        $wpdb->query("DROP TABLE $table_name");
    }
}
delete_clicksend_table_on_uninstall();