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
 * Parses content according to settings
 *
 * @author Saurabh Shukla <saurabh@yapapaya.com>
 */
class rssPIParser {

	/**
	 * Parse content
	 * 
	 * @global object $rss_post_importer
	 * @param object $item Feed item
	 * @param string $feed_title Feed title
	 * @param boolean $strip_html whether to strio html tags
	 * @return type
	 */
	function _parse($item, $feed_title, $strip_html) {

		global $rss_post_importer;

		// get the saved template
		$post_template = ($rss_post_importer->options['settings']['post_template'] == false) ? '{$content}' : $rss_post_importer->options['settings']['post_template'];

		// get the content
		$c = $item->get_content() != "" ? $item->get_content() : $item->get_description();

		$c = apply_filters('pre_rss_pi_parse_content', $c);

		$c = $this->escape_backreference($c);

		// do all the replacements
		$parsed_content = preg_replace('/\{\$content\}/i', $c, $post_template);
		$parsed_content = preg_replace('/\{\$feed_title\}/i', $feed_title, $parsed_content);
		$parsed_content = preg_replace('/\{\$title\}/i', $item->get_title(), $parsed_content);

		// check if we need an excerpt
		$parsed_content = $this->_excerpt($parsed_content, $c);

		// strip html, if needed
		if ($strip_html == 'true') {
      $parsed_content = strip_tags($parsed_content);
		}

		$parsed_content = preg_replace('/\{\$permalink\}/i', '<a href="' . esc_url($item->get_permalink()) . '" target="_blank">' . $item->get_title() . '</a>', $parsed_content);
		$parsed_content = apply_filters('after_rss_pi_parse_content', $parsed_content);

		return $parsed_content;
	}

	/*
	 *
	 * 	Escape $n backreferences
	 */
	function escape_backreference($x) {

		return preg_replace('/\$(\d)/', '\\\$$1', $x);
	}

	/**
	 * Checks and creates an excerpts
	 * 
	 * @param string $content Content
	 * @return string
	 */
	private function _excerpt($content, $c) {

		// if there's an excerpt placeholder
		preg_match('/\{\$excerpt\:(\d+)\}/i', $content, $matches);

		// if there's a wordcount
		$e_size = (is_array($matches) && !empty($matches)) ? $matches[1] : 0;

		// cut it down and replace the placeholder
		if ($e_size) {
			$trimmed_c = preg_replace('/<!--(.|\s)*?-->/', '', $c);
			// compulsorily strip html otherwise there'll be broken html all over
			$stripped_c = strip_tags($trimmed_c);
			$content = preg_replace('/\{\$excerpt\:\d+\}/i', wp_trim_words($stripped_c, $e_size), $content);
		}

		return $content;
	}

}
