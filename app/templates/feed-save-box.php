<div class="postbox">
	<div class="inside">
    <div class="misc-pub-section">
			<h3 class="version">Version <?php echo ELA_RSS_PI_VERSION; ?></h3>
			<ul>
				<li>
					<i class="icon-calendar"></i> <?php _e("Latest import:", 'rss_pi'); ?> <strong><?php echo $this->options['latest_import'] ? $this->options['latest_import'] : 'never' ; ?></strong>
				</li>
				<li><i class="icon-eye-open"></i> <a href="#" class="load-log"><?php _e("View the log", 'rss_pi'); ?></a></li>
        <li>    
          <?php add_thickbox(); ?>                                                                                                                                                                                                                                                                                               
          <div id="user-agreement-modal" style="display:none;">
            <p>This plugin will import ElderLawAnswers content as posts on your blog.</p>
            <p>These posts may contain links to <a href="http://elderlawanswers.com">ElderLawAnswers</a>, our sister site
              <a href="http://specialneedsanswers.com">SpecialNeedsAnswers</a> and other sites that our editorial
              team believe provide valuable information.
            </p>
            <button id="ela-rss-pi-button-decline">Decline</button>&nbsp;<button id="ela-rss-pi-button-accept">Accept</button>
          </div>
          <i class="icon-check"></i> <a id="ela-rss-pi-agreement-link" href="#TB_inline?width=600&height=200&inlineId=user-agreement-modal&title=UserAgreement" name="User Agreement" class="thickbox">View user agreement</a>
          <?php if(!isset($rss_post_importer->accepted_terms) || !$rss_post_importer->accepted_terms): ?>
            <input type="hidden" id="ela-rss-user-has-not-accepted" />
          <?php  endif; ?>
        </li>
			</ul>
		</div>
		<div id="major-publishing-actions">
			<input class="button button-primary button-large right" type="submit" name="info_update" value="<?php _e('Save', 'rss_pi'); ?>" />
			<input class="button button-large" type="submit" name="info_update" value="<?php _e('Save and import', "rss_pi"); ?>" id="save_and_import" />
		</div>
	</div>
</div>
<?php if ($this->options['imports'] > 10) : ?>
	<div class="rate-box">
		<h4><?php printf(__('%d posts imported and counting!', "rss_pi"), $this->options['imports']); ?></h4>
		<i class="icon-star"></i>
		<i class="icon-star"></i>
		<i class="icon-star"></i>
		<i class="icon-star"></i>
		<i class="icon-star"></i>
		<p class="description"><a href="http://wordpress.org/plugins/ela-rss-post-importer/" target="_blank">If you like this plugin let us know by rating it!</a></p>
	</div>
<?php endif; ?>

<?php $banner_url = RSS_PI_URL . "app/assets/img/rss-post-importer_280x600.jpg"; ?>
