<?php

/**
 * Fired during plugin activation
 *
 * @link       https://clicksend.com/help
 * @since      1.0.0
 *
 * @package    Clicksend_Contactform7
 * @subpackage Clicksend_Contactform7/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Clicksend_Contactform7
 * @subpackage Clicksend_Contactform7/includes
 * @author     ClickSend <support@clicksend.com>
 */
class Clicksend_Contactform7_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
	    global $wpdb; 
	    $db_table_name = $wpdb->prefix.'clicksend_messages';  // table name
	    $charset_collate = $wpdb->get_charset_collate();

	     //Check to see if the table exists already, if not, then create it
	    if($wpdb->get_var( "show tables like '$db_table_name'" ) != $db_table_name ) 
	     {
	        $sql1 = "CREATE TABLE $db_table_name (
	        `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	        `admin_message` text NOT NULL,
	        `customer_message` text NOT NULL,
	        `status` enum('enabled','disabled') NOT NULL,
	        `page_id` int(11) NOT NULL,
	        `cf_form_id` int(11) NOT NULL
	        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	       require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	       dbDelta( $sql1 );
	     }
	}

}
