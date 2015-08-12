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
 * Main import engine
 *
 * @author Saurabh Shukla <saurabh@yapapaya.com>
 */
class rssPIEngine {

  /**
   * Whether the API key is valid
   * 
   * @var boolean
   */
  var $is_key_valid;

  /**
   * The options
   * 
   * @var array
   */
  var $options = array();

  /**
   * Start the engine
   * 
   * @global type $rss_post_importer
   */
  public function __construct() {

    global $rss_post_importer;

    // load options
    $this->options = $rss_post_importer->options;
    $this->accepted_terms = $rss_post_importer->accepted_terms;
  }

  /**
   * Import feeds
   * 
   * @return int
   */
  public function import_feed($ids = '') {
    global $rss_post_importer;

    $post_count = 0;

    // filter cache lifetime
    add_filter('wp_feed_cache_transient_lifetime', array($this, 'frequency'));

    //Check to make sure user accepted terms
    if($this->accepted_terms) {
      $f = array();

      // prepare and import each feed
      $items = $this->prepare_import($f, $ids);
      $post_count += count($items);

      // reformulate import count
      $imports = intval($this->options['imports']) + $post_count;

      // update options
      update_option('rss_pi_feeds', array(
        'feeds' => $this->options['feeds'],
        'settings' => $this->options['settings'],
        'latest_import' => date("Y-m-d H:i:s"),
        'imports' => $imports
      ));

      global $rss_post_importer;
      // reload options
      $rss_post_importer->load_options();

      remove_filter('wp_feed_cache_transient_lifetime', array($this, 'frequency'));

      // log this
      rssPILog::log($post_count);
    }
    else {
      $post_count = 0;
    }

    return $post_count;
  }

  /**
   * Dummy function for filtering because we can't use anon ones yet
   * @return string
   */
  public function frequency() {

    return $this->options['settings']['frequency'];
  }

  /**
   * Prepares arguments and imports
   * 
   * @param array $f feed array
   * @param string $ids hyphen delimited integers
   * @return arrays
   */
  public function prepare_import($fi, $ids) {
    $args = $this->options;

    return $this->_import($args, $ids);
  }

  /**
   * Import feeds from url
   * 
   * @param string $ids hyphen delimited integers
   * @param array $args Arguments for the import
   * @return null|array
   */
  private function _import($args = array(), $ids) {

    $defaults = array(
      'feed_title' => 'ElderLawAnswers',
      'max_posts' => 100,
      'ela_author_id' => 1,
      'ela_category_id' => 0,
      'asnp_author_id' => 1,
      'asnp_category_id' => 0,
      'tags_id' => array(),
      'keywords' => array(),
      'strip_html' => false,
      'save_to_db' => true,
      'ids' => "$ids"
    );

    $args = wp_parse_args($args, $defaults);

    // include the default WP feed processing functions
    include_once( ABSPATH . WPINC . '/feed.php' );

    $url = $this->url($args['ids']);

    //determine if we are importing due to the newsletter
    //and therefore should not care if the post was previously
    //deleted
    $newsletter_import = (strlen($ids) > 0);

    // fetch the feed
    $feed = fetch_feed($url);

    // save as posts
    $posts = $this->save($feed, $args, $newsletter_import);

    return $posts;
  }

  /**
   * Formulate the right url based on ids and token
   * 
   * @param string $ids
   * @return string
   */
  private function url($ids = '') {

    $key = $this->options['settings']['feeds_api_key'];

    $url = ELA_RSS_PI_BASE_URL . 'token/' . urlencode($key) . '/ids/' . urlencode($ids) . '/version/' . urlencode(ELA_RSS_PI_VERSION);

    return $url;
  }

  /**
   * Save the feed
   * 
   * @param object $feed The feed object
   * @param array $args The arguments
   * @param bool $newsletter_import whether or not this is a newsletter triggered import
   * @return boolean
   */
  private function save($feed, $args = array(), $newsletter_import) {

    if (is_wp_error($feed)) {
      return false;
    }
    // filter the feed and get feed items
    $feed_items = $this->filter($feed, $args);

    // if we are saving
    if ($args['save_to_db']) {
      // insert and return
      $saved_posts = $this->insert($feed_items, $args, $newsletter_import);
      return $saved_posts;
    }

    // otherwsie return the feed items
    return $feed_items;
  }

  /**
   * Filter the feed based on keywords
   * 
   * @param object $feed The feed object
   * @param array $args Arguments
   * @return array
   */
  private function filter($feed, $args) {

    // the current index of the items aray
    $index = 0;

    $filtered = array();

    // till we have as many as the posts needed
    while (true) {

      // get only one item at the current index
      $feed_item = $feed->get_items($index, 1);
      // if this is empty, get out of the while
      if (empty($feed_item)) {
        break;
      }
      // else be in a forever loop
      // get the content
      $content = $feed_item[0]->get_content();

      array_push($filtered, $feed_item[0]);
      // shift the index
      $index++;
    }

    return $filtered;
  }

  /**
   * Insert feed items as posts
   * 
   * @param array $items Fetched feed items
   * @param array $args arguments
   * @param bool $newsletter_import triggered (true) or scheduled (false) import
   * @return array
   */
  private function insert($items, $args = array(), $newsletter_import) {

    $saved_posts = array();

    // Initialise the content parser
    $parser = new rssPIParser($this->options);
    // Featured Image setter
    $thumbnail = new rssPIFeaturedImage();

    foreach ($items as $item) {
      if (!$this->post_exists($item, $newsletter_import)) {
        /* Code to convert tags id array to tag name array * */
        if (!empty($args['tags_id'])) {
          foreach ($args['tags_id'] as $tagid) {
            $tag_name = get_tag($tagid); // <-- your tag ID
            $tags_name[] = $tag_name->name;
          }
        } else {
          $tags_name = array();
        }
        $parser->_parse($item, $args['feed_title'], $args['strip_html']);
        $organization = $item->get_categories()[0]->get_term();

        //ELA Post
        if($organization === 'ELA') {
          $post = array(
            'post_title' => $item->get_title(),
            // parse the content
            'post_content' => htmlspecialchars_decode($parser->_parse($item, $args['feed_title'], $args['strip_html'])),
            'post_excerpt' => htmlspecialchars_decode($item->get_description()),
            'post_status' => $newsletter_import ? 'publish' : $this->options['settings']['ela_post_status'],
            'post_author' => $this->options['settings']['ela_author_id'],
            'post_category' => array($this->options['settings']['ela_category']),
            'post_name' => preg_replace('/-\d+$/', '', end(explode('/', $item->get_link()))),
            'tags_input' => $tags_name,
            'comment_status' => $this->options['settings']['ela_allow_comments'],
            'post_date' => get_date_from_gmt($item->get_date('Y-m-d H:i:s'))
          );
        }

        //ASNP Post
        if($organization === 'ASNP') {
          $post = array(
            'post_title' => $item->get_title(),
            // parse the content, decode html special chars because sometimes
            'post_content' => htmlspecialchars_decode($parser->_parse($item, $args['feed_title'], $args['strip_html'])),
            'post_excerpt' => htmlspecialchars_decode($item->get_description()),
            'post_status' => $newsletter_import ? 'publish' : $this->options['settings']['asnp_post_status'],
            'post_author' => $this->options['settings']['asnp_author_id'],
            'post_category' => array($this->options['settings']['asnp_category']),
            'post_name' => preg_replace('/-\d+$/', '', end(explode('/', $item->get_link()))),
            'tags_input' => $tags_name,
            'comment_status' => $this->options['settings']['asnp_allow_comments'],
            'post_date' => get_date_from_gmt($item->get_date('Y-m-d H:i:s'))
          );
        }


        $content = $post['post_content'];

        // catch base url and replace any img src with it
        if (preg_match('/src="\//ui', $content)) {
          preg_match('/href="(.+?)"/ui', $content, $matches);
          $baseref = (is_array($matches) && !empty($matches)) ? $matches[1] : '';
          if (!empty($baseref)) {
            $bc = parse_url($baseref);
            $scheme = (!isset($bc['scheme']) || empty($bc['scheme'])) ? 'http' : $bc['scheme'];
            $port = isset($bc['port']) ? $bc['port'] : '';
            $host = isset($bc['host']) ? $bc['host'] : '';
            if (!empty($host)) {
              $preurl = $scheme . ( $port ? ':' . $port : '' ) . '//' . $host;
              $post['post_content'] = preg_replace('/(src="\/)/i', 'src="' . $preurl . '/', $content);
            }
          }
        }

        //download images and save them locally if setting suggests so
        if ($this->options['settings']['import_images_locally'] == 'true') {

          $post = $this->download_images_locally($post);
        }

        // insert as post
        $post_id = $this->_insert($post, $item->get_permalink());

        // set thumbnail
        if ($this->options['settings']['disable_thumbnail'] == 'false') {
          $thumbnail->_set($item, $post_id);
        }

        array_push($saved_posts, $post);
      }
    }

    return $saved_posts;
  }

  /**
   * Check if a feed item is alreday imported
   * 
   * @param string $permalink
   * @param bool $newsletter_import see above
   * @return boolean
   */
  private function post_exists($item, $newsletter_import) {

    //print_r($newsletter_import);die();
    global $wpdb;
    $permalink = $item->get_permalink();
    $title = $item->get_title();
    $domain_old = $this->get_domain($item->get_permalink());

    $post_exists = 0;
    //checking if post title already exists
    if ($posts = $wpdb->get_results("SELECT ID  FROM " . $wpdb->prefix . "posts WHERE post_title = '" . $title . "' and post_status = 'publish' ", 'ARRAY_A')) {
      //checking if post source is also same 
      foreach ($posts as $post) {
        $post_id = $post['ID'];
        $source_url = get_post_meta($post_id, 'rss_pi_source_url', true);
        $domain_new = $this->get_domain($source_url);

        if ($domain_new == $domain_old) {
          $post_exists = 1;
        }
      }
    }
    //check if the post has already been imported and then deleted 
    $rss_pi_imported_posts = get_option('rss_pi_imported_posts');
    if (is_array($rss_pi_imported_posts) && in_array($permalink, $rss_pi_imported_posts) && !$newsletter_import) {
      $post_exists = 1;
    }

    return $post_exists;
  }

  private function get_domain($url) {

    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : '';
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
      return $regs['domain'];
    }
    return false;
  }

  /**
   * Insert feed item as post
   * 
   * @param array $post Post array
   * @param string $url source url meta
   * @return int
   */
  private function _insert($post, $url) {

    if ($post['post_category'][0] == "") {
      $post['post_category'] = array(1);
    } else {
      if (is_array($post['post_category'][0]))
        $post['post_category'] = array_values($post['post_category'][0]);
      else
        $post['post_category'] = array_values($post['post_category']);
    }

    $_post = apply_filters('pre_rss_pi_insert_post', $post);

    $post_id = wp_insert_post($_post);

    add_action('save_rss_pi_post', $post_id);

    add_post_meta($post_id, 'rss_pi_source_url', esc_url($url));

    //saving each post URL in option table 
    $rss_pi_imported_posts = get_option('rss_pi_imported_posts');
    if (!is_array($rss_pi_imported_posts)) {
      $rss_pi_imported_posts = array();
    }
    $rss_pi_imported_posts[] = $url;
    update_option('rss_pi_imported_posts', $rss_pi_imported_posts);

    return $post_id;
  }

  /*
   * Debugging function, pretty prints variables
   * @param array/object $arr
   */
  public function pre($arr) {

    echo '<pre>';
    print_r($arr);
    echo '</pre>';
  }

  function download_images_locally($post) {

    $post_content = $post['post_content'];
    // initializing DOMDocument to modify the img source 
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $post_content);
    $xpath = new DOMXPath($dom);
    libxml_clear_errors();
    //get all the src attribs and their values
    $doc = $dom->getElementsByTagName('html')->item(0);
    $src = $xpath->query('.//@src');
    $count = 1;
    foreach ($src as $s) {
      $url = trim($s->nodeValue);
      $attachment_id = $this->add_to_media($url, 0, $post['post_title'] . '-media-' . $count);
      $src = wp_get_attachment_url($attachment_id);
      $s->nodeValue = $src;
      $count++;
    }
    $post['post_content'] = $dom->saveXML($doc);
    return $post;
  }

  function add_to_media($url, $associated_with_post, $desc) {
    $tmp = download_url($url);
    $post_id = $associated_with_post;
    $desc = $desc;
    $file_array = array();
    // Set variables for storage
    // fix file filename for query strings
    if ( ! preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches) ) {
      return false;
    }
    $file_array['name'] = basename($matches[0]);
    $file_array['tmp_name'] = $tmp;
    // If error storing temporarily, unlink
    if (is_wp_error($tmp)) {
      @unlink($file_array['tmp_name']);
      return false;
    }
    // do the validation and storage stuff
    $id = media_handle_sideload($file_array, $post_id, $desc);
    // If error storing permanently, unlink
    if (is_wp_error($id)) {
      @unlink($file_array['tmp_name']);
      return false;
    }

    return $id;
  }

}

