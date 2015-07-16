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
 * One class to rule them all
 * 
 * @author Saurabh Shukla <saurabh@yapapaya.com>
 */
class rssPostImporter {

	/**
	 * A var to store the options in
	 * @var array
	 */
	public $options = array();

	/**
	 * A var to store the link to the plugin page
	 * @var array
	 */
	public $page_link = '';

	/**
	 * To initialise the admin and cron classes
	 * 
	 * @var object
	 */
	private $admin, $cron;

	/**
	 * Start
	 */
	function __construct() {

		// populate the options first
		$this->load_options();

		// setup this plugin options page link
		$this->page_link = admin_url('options-general.php?page=rss_pi');

		// hook translations
		//add_action('plugins_loaded', array($this, 'localize'));

		add_filter('plugin_action_links_' . RSS_PI_BASENAME, array($this, 'settings_link'));
	}

	/**
	 * Load options from the db
	 */
	public function load_options() {

		$default_settings = array(
			'enable_logging' => true,
			'feeds_api_key' => false,
			'frequency' => 0,
			'post_template' => "{\$content}",
			'post_status' => 'publish',
			'author_id' => 1,
			'allow_comments' => 'open',
			'block_indexing' => false,
			'nofollow_outbound' => false,
			'keywords' => array(),
			'import_images_locally' => false,
			'disable_thumbnail' => true
		);

		$options = get_option('rss_pi_feeds', array());
		$accepted_terms = get_option('rss_pi_feeds_accepted_terms', array());

		// prepare default options when there is no record in the database
		if (!isset($options['feeds'])) {
			$options['feeds'] = array();
		}
		if (!isset($options['settings'])) {
			$options['settings'] = array();
		}
		if (!isset($options['latest_import'])) {
			$options['latest_import'] = '';
		}
		if (!isset($options['imports'])) {
			$options['imports'] = 0;
		}

		$options['settings'] = wp_parse_args($options['settings'], $default_settings);

		if (!array_key_exists('imports', $options)) {
			$options['imports'] = 0;
		}

    $this->options = $options;
    $this->accepted_terms = $accepted_terms['accepted_terms'] || false;
	}

	/**
	 * Load translations
	 */
	public function localize() {

		load_plugin_textdomain('rss_pi', false, RSS_PI_PATH . 'app/lang/');
	}

	/**
	 * Initialise
	 */
	public function init() {

		// initialise admin and cron
		$this->cron = new rssPICron();
		$this->cron->init();

		$this->admin = new rssPIAdmin();
    $this->admin->init();

		$this->front = new rssPIFront();
		$this->front->init();

	}

	/**
   * Always returns true, the server checks API key validity
   * It is used throughout the codebase but should be removed
   * at some point
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function is_valid_key($key) {

		return true;
	}

	/**
	 * Adds a settings link
	 * 
	 * @param array $links Existing links
	 * @return type
	 */
	public function settings_link($links) {
		$settings_link = array(
			'<a href="' . $this->page_link . '">Settings</a>',
		);
		return array_merge($settings_link, $links);
	}

}
