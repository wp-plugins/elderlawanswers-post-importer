<table class="widefat rss_pi-table" id="rss_pi-table">
	<thead>
		<tr>
			<th colspan="5"><?php _e('Settings', 'rss_pi'); ?></th>
		</tr>
	</thead>
	<tbody class="setting-rows">
		<tr class="edit-row show">
			<td colspan="4">
				<table class="widefat edit-table">
					<tr>
						<td>
							<label for="feeds_api_key"><?php _e('ELA Plugin Token', "rss_pi"); ?></label>
              <?php if(!isset($this->options['settings']["feeds_api_key"])): ?>
                <p class="description"><?php _e('Let our editorial department and practice development tools work for you. - ', "rss_pi"); ?> 
                  <a href="<?php echo ELA_ATTORNEY_SIGNUP_URL; ?>" target="_blank"> Try it today!</a>
                </p>
              <?php endif; ?>
						</td>
						<td>
							<?php $feeds_api_key = isset($this->options['settings']["feeds_api_key"]) ? $this->options['settings']["feeds_api_key"] : ""; ?>
							<input type="text" name="feeds_api_key" id="feeds_api_key" value="<?php echo $feeds_api_key; ?>" />
						</td>
					</tr>

					<tr>
						<td><label for="post_status"><?php _e('Post status', "rss_pi"); ?></label></td>
						<td>

							<select name="post_status" id="post_status">
								<?php
								$statuses = get_post_stati('', 'objects');

								foreach ($statuses as $status) {
									?>
									<option value="<?php echo($status->name); ?>" <?php
									if ($this->options['settings']['post_status'] == $status->name) : echo('selected="selected"');
									endif;
									?>><?php echo($status->label); ?></option>
											<?php
										}
										?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php _e('Author', 'rss_pi'); ?></td>
						<td>
							<?php
							$args = array(
								'id' => 'author_id',
								'name' => 'author_id',
								'selected' => $this->options['settings']['author_id']
							);
							wp_dropdown_users($args);
							?> 
						</td>
					</tr>
					<tr>
						<td><?php _e('Allow comments', "rss_pi"); ?></td>
						<td>
							<ul class="radiolist">
								<li>
									<label><input type="radio" id="allow_comments_open" name="allow_comments" value="open" <?php echo($this->options['settings']['allow_comments'] == 'open' ? 'checked="checked"' : ''); ?> /> <?php _e('Yes', 'rss_pi'); ?></label>
								</li>
								<li>
									<label><input type="radio" id="allow_comments_false" name="allow_comments" value="false" <?php echo($this->options['settings']['allow_comments'] == 'false' ? 'checked="checked"' : ''); ?> /> <?php _e('No', 'rss_pi'); ?></label>
								</li>
							</ul>
						</td>
					</tr>
          <tr>
              <td><label for=""><?php _e("Category", 'rss_pi'); ?></label></td>
            <td>
              <?php
              $rss_post_pi_admin = new rssPIAdmin();
                ?>
                <div class="category_container">
                  <ul>
                <?php
                $allcats = $rss_post_pi_admin->wp_category_checklist_rss_pi(0, false, $this->options['settings']['category']);
                $allcats = str_replace('name="post_category[]"', 'name="category_id[]"', $allcats);
                echo $allcats;
                ?>
                  </ul>
                </div>
            </td>
          </tr>
        </table>
			</td>
		</tr>
	</tbody>
</table>
