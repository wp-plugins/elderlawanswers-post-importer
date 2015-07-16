<?php
/*

  Copyright (C) 2015  ElderLawAnswers

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along
  with this program; if not, write to the Free Software Foundation, Inc.,
  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Manipulates log files
 *
 * @author Saurabh Shukla <saurabh@yapapaya.com>
 */
class rssPILog {

	/**
	 * Initialise
	 */
	public function init() {

		// hook ajax for loading and clearing log on admin screen
		add_action('wp_ajax_rss_pi_load_log', array($this, 'load_log'));
		add_action('wp_ajax_rss_pi_clear_log', array($this, 'clear_log'));
        
    // Create log table if it doesn't exist
    global $wpdb;
      
    $db_table_name = $wpdb->prefix . 'ela_log';
    if( $wpdb->get_var( "SHOW TABLES LIKE '$db_table_name'" ) != $db_table_name ) {
      if ( ! empty( $wpdb->charset ) )
          $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
      if ( ! empty( $wpdb->collate ) )
          $charset_collate .= " COLLATE $wpdb->collate";

      $sql = "CREATE TABLE " . $db_table_name . " (
          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `date_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
          `log` varchar(100) NOT NULL DEFAULT '',
          PRIMARY KEY (`id`)
      ) $charset_collate;";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta( $sql );
    } 
	}

	/**
	 * Loads log contents
	 */
	function load_log() {

		// get the log contents
        
        global $wpdb;
        
        $db_table_name = $wpdb->prefix . 'ela_log';
        $retrieve_data = $wpdb->get_results( "SELECT * FROM $db_table_name" );
        $log = '';
        foreach ($retrieve_data as $row) {
            $log .= $row->date_time." ".$row->log."\n";
        } 

		// include the template to display it
		include( RSS_PI_PATH . 'app/templates/log.php');
		die();
	}

  function clear_log() {

    global $wpdb;

    $db_table_name = $wpdb->prefix . 'ela_log';
    $wpdb->query("TRUNCATE TABLE $db_table_name");
?>
    <div id="message" class="updated">
      <p><strong><?php _e('Log has been cleared.', "rss_pi"); ?></strong></p>
    </div>
<?php
    die();

  }

  /**
   * Static method to add log messages
   * 
   * @global object $rss_post_importer Global object
   * @param int $post_count Number of posts imported
   * @return null
   */
  static function log($post_count) {

    global $rss_post_importer;

    // prepare the log entry
    $date = date("Y-m-d H:i:s");
    $log = "\t Imported " . $post_count . " new posts.";

    global $wpdb;

    $db_table_name = $wpdb->prefix . 'ela_log';
    $wpdb->query("INSERT INTO $db_table_name VALUES ('', '$date', '$log')");
  }

}
