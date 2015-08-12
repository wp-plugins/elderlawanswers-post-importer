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
 * Handles cron jobs
 *
 * @author Saurabh Shukla <saurabh@yapapaya.com>
 */
class rssPICron {

	/**
	 * Initialise
	 */
	public function init() {

		// hook up scheduled events
		add_action('wp', array(&$this, 'schedule'));

		add_action('rss_pi_cron', array(&$this, 'do_hourly'));
	}

	/**
	 * Check and confirm scheduling
	 */
	function schedule() {

		if (!wp_next_scheduled('rss_pi_cron')) {

			wp_schedule_event(time(), 'hourly', 'rss_pi_cron');
		}
	}

	/**
	 * Import the feeds on schedule
	 */
	function do_hourly() {
    //global $rss_post_importer;
    //$rss_post_importer->load_options();

		$engine = new rssPIEngine();
		$engine->import_feed();
	}

}
