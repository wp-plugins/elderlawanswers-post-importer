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
 * The class that handles the front screen
 *
 * 
 */
class rssPIFront {

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
	var $options;

	/**
	 * Aprompt for invalid/absent API keys
	 * @var string
	 */
	var $key_prompt;

	/**
	 * Initialise and hook all actions
	 */
	public function init() {
		global $post, $rss_post_importer;
		// get post meta information, then edit the post if necessary
		add_action('wp_head', array($this, 'rss_pi_noindex_meta_tag'));
		// add options
		$this->options = $rss_post_importer->options;

    add_action('wp_head', array($this, 'ela_rss_pi_reset_canonical_if_needed'), 'top');
		// Check for block indexing
		if ($this->options['settings']['nofollow_outbound'] == 'true') {
			add_filter('the_content', array($this, 'rss_pi_url_parse'));
		}
  }

  /*
   * To be run during template_redirect, determines if $post is one of ours
   * and if so points the canonical url to the original ELA content, otherwise
   * it defers to whatever the current site is doing.
   */
  function ela_rss_pi_reset_canonical_if_needed() {
    global $post, $rss_post_importer;
    if(is_single()) {
      $current_post_id = $post->ID;
      $ela_url = get_post_meta($current_post_id, 'rss_pi_source_url', false);
      if(!empty($ela_url)) {
        remove_action('wp_head','rel_canonical');
        add_action('wp_head',array($this, 'ela_rss_pi_rel_canonical'));
      }
    }
  }

  /*
   * If the post is an ELA post, provide the canonical <link> to the original article
   */
  function ela_rss_pi_rel_canonical() {
    global $post, $rss_post_importer;
    //Simplest solution appeared to be duplicating this code, not sure why writing to $this didn't work
    $current_post_id = $post->ID;
    $ela_url = get_post_meta($current_post_id, 'rss_pi_source_url', false);
    echo '<link rel="canonical" href="' . $ela_url[0] . '">';
  }

	function rss_pi_noindex_meta_tag() {
		global $post, $rss_post_importer;

		//Add meta tag for UTF-8 character encoding.
		echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" />';

		// Check if single post
		if (is_single()) {

			// Get current post id
			$current_post_id = $post->ID;

			// add options
			$this->options = $rss_post_importer->options;

			// get value of block indexing
			$block_indexing = $this->options['settings']['block_indexing'];

			// Check for block indexing
			if ($this->options['settings']['block_indexing'] == 'true') {
				$meta_values = get_post_meta($current_post_id, 'rss_pi_source_url', false);
				// if meta value array is empty it means post is not imported by this plugin.
				if (!empty($meta_values)) {
					echo '<meta name="robots" content="noindex">';
				}
			}
		}
	}

	function rss_pi_url_parse($content) {

		$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>";
		if (preg_match_all("/$regexp/siU", $content, $matches, PREG_SET_ORDER)) {
			if (!empty($matches)) {

				$srcUrl = get_option('home');
				for ($i = 0; $i < count($matches); $i++) {

					$tag = $matches[$i][0];
					$tag2 = $matches[$i][0];
					$url = $matches[$i][0];

					$noFollow = '';

					$pattern = '/target\s*=\s*"\s*_blank\s*"/';
					preg_match($pattern, $tag2, $match, PREG_OFFSET_CAPTURE);
					if (count($match) < 1)
						$noFollow .= ' target="_blank" ';

					$pattern = '/rel\s*=\s*"\s*[n|d]ofollow\s*"/';
					preg_match($pattern, $tag2, $match, PREG_OFFSET_CAPTURE);
					if (count($match) < 1)
						$noFollow .= ' rel="nofollow" ';

					$pos = strpos($url, $srcUrl);
					if ($pos === false) {
						$tag = rtrim($tag, '>');
						$tag .= $noFollow . '>';
						$content = str_replace($tag2, $tag, $content);
					}
				}
			}
		}

		$content = str_replace(']]>', ']]&gt;', $content);
		return $content;
	}

}
