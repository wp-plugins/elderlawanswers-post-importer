<table class="widefat rss_pi-table" id="rss_pi-table">
	<thead>
		<tr>
			<th colspan="5"><?php _e('ElderLawAnswers Settings', 'rss_pi'); ?></th>
		</tr>
	</thead>
	<tbody class="setting-rows">
		<tr class="edit-row show">
			<td colspan="4">
				<table class="widefat edit-table">
					<tr>
						<td><label for="ela_post_status"><?php _e('Post status', "rss_pi"); ?></label></td>
						<td>

							<select name="ela_post_status" id="ela_post_status">
								<?php
								$statuses = get_post_stati('', 'objects');

								foreach ($statuses as $status):?>
                  <option value="<?php echo($status->name); ?>" 
                    <?php echo ($this->options['settings']['ela_post_status'] === $status->name) ? 'selected="selected">' :  '>'; ?>
                    <?php echo($status->label); ?>
                  </option>

                <?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php _e('Author', 'rss_pi'); ?></td>
						<td>
							<?php
							$args = array(
								'id' => 'ela_author_id',
								'name' => 'ela_author_id',
								'selected' => $this->options['settings']['ela_author_id']
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
									<label><input type="radio" id="ela_allow_comments_open" name="ela_allow_comments" value="open" <?php echo($this->options['settings']['ela_allow_comments'] == 'open' ? 'checked="checked"' : ''); ?> /> <?php _e('Yes', 'rss_pi'); ?></label>
								</li>
								<li>
									<label><input type="radio" id="ela_allow_comments_false" name="ela_allow_comments" value="false" <?php echo($this->options['settings']['ela_allow_comments'] == 'false' ? 'checked="checked"' : ''); ?> /> <?php _e('No', 'rss_pi'); ?></label>
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
                $allcats = $rss_post_pi_admin->wp_category_checklist_rss_pi(0, false, $this->options['settings']['ela_category']);
                $allcats = str_replace('name="post_category[]"', 'name="ela_category_id[]"', $allcats);
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
