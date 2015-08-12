<?php

/*
  Plugin Name: ElderLawAnswers Post Importer
  Plugin URI: http://attorney.elderlawanswers.com/home/wordpress-plugin
  Description: This plugin allows you to import posts from the ElderLawAnswers RSS feed for use as content in your Wordpress site
  Author: elderlawanswers
  Version: 1.1.1
  Author URI: http://www.elderlawanswers.com/
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /lang/

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

defined( 'ABSPATH' ) or die( 'Nothing to see here, please move along' );

// define some constants
if (!defined('RSS_PI_PATH')) {
	define('RSS_PI_PATH', trailingslashit(plugin_dir_path(__FILE__)));
}

if (!defined('RSS_PI_URL')) {
	define('RSS_PI_URL', trailingslashit(plugin_dir_url(__FILE__)));
}

if (!defined('RSS_PI_BASENAME')) {
	define('RSS_PI_BASENAME', plugin_basename(__FILE__));
}

if (!defined('ELA_RSS_PI_VERSION')) {
  define('ELA_RSS_PI_VERSION', '1.1.1');
}

if (!defined('ELA_RSS_PI_BASE_URL')) {
  define('ELA_RSS_PI_BASE_URL', 'http://www.elderlawanswers.com/news-and-information/rss-full/');
}

if (!defined('ELA_ATTORNEY_SIGNUP_URL')) {
  define('ELA_ATTORNEY_SIGNUP_URL', 'http://attorney.elderlawanswers.com/');
}

// helper classes
include_once RSS_PI_PATH . 'app/classes/helpers/class-rss-pi-log.php';
include_once RSS_PI_PATH . 'app/classes/helpers/class-rss-pi-featured-image.php';
include_once RSS_PI_PATH . 'app/classes/helpers/class-rss-pi-parser.php';

// admin classes
include_once RSS_PI_PATH . 'app/classes/admin/class-rss-pi-admin-processor.php';
include_once RSS_PI_PATH . 'app/classes/admin/class-rss-pi-admin.php';

// Front classes
include_once RSS_PI_PATH . 'app/classes/front/class-rss-pi-front.php';

// main importers
include_once RSS_PI_PATH . 'app/classes/import/class-rss-pi-engine.php';
include_once RSS_PI_PATH . 'app/classes/import/class-rss-pi-cron.php';

// the main loader class
include_once RSS_PI_PATH . 'app/class-rss-post-importer.php';

// initialise plugin as a global var
global $rss_post_importer;

$rss_post_importer = new rssPostImporter();

$rss_post_importer->init();

