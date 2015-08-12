<table class="widefat rss_pi-table" id="rss_pi-table">
	<thead>
		<tr>
			<th colspan="5"><?php _e('Account Settings', 'rss_pi'); ?></th>
		</tr>
	</thead>
	<tbody class="setting-rows">
		<tr class="edit-row show">
			<td colspan="4">
				<table class="widefat edit-table">
					<tr>
						<td>
							<label for="feeds_api_key"><?php _e('Plugin Token', "rss_pi"); ?></label>
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
        </table>
      </td>
    </tr>
  </tbody>
</table>
