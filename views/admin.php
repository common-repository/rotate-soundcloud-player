<div class="rotate-soundcloud-player-styles-namespace">
	<h3>Display:</h3>
	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', $widget_slug); ?></label> 
		<input class="full" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('soundcloud_username'); ?>"><?php _e('Soundcloud Username:', $widget_slug); ?></label> 
		<input class="full" id="<?php echo $this->get_field_id('soundcloud_username'); ?>" name="<?php echo $this->get_field_name('soundcloud_username'); ?>" type="text" value="<?php echo esc_attr( $instance['soundcloud_username'] ); ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('client_id'); ?>"><?php _e('Client ID:', $widget_slug); ?></label> 
		<input class="full" id="<?php echo $this->get_field_id('client_id'); ?>" name="<?php echo $this->get_field_name('client_id'); ?>" type="text" value="<?php echo esc_attr( $instance['client_id'] ); ?>" />
		<a href="http://soundcloud.com/you/apps">Create Here</a> (<a href="https://wordpress.org/plugins/rotate-soundcloud-player/installation/" target="_new">Help</a>)
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('playlist'); ?>"><?php _e('Show:', $widget_slug); ?></label> 
		<?php 
			if( !empty($instance) && $instance['client_id'] != '' && $instance['soundcloud_user_id'] != ''):
		?>
			<select  id="<?php echo $this->get_field_id('playlist'); ?>" name="<?php echo $this->get_field_name('playlist'); ?>">
				<option value="allUser" <?php echo ( $instance['playlist'] == 'allUser' ? 'selected' : '' ); ?>>All Tracks for User</option> 
				<?php 
					if( !empty($instance) && $instance['client_id'] != '' && $instance['soundcloud_user_id'] != '') {
						// first get user id
						$cId =  $instance['client_id'];
						// $sUn =  $instance['soundcloud_username'];
						$userId = $instance['soundcloud_user_id'];

						// get user's playlists
						$url = "http://api.soundcloud.com/users/$userId/playlists.json?client_id=$cId";
						$ch2 = curl_init($url);
						curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false); // most hosts do not include list of ssl valids
						$response_body = curl_exec($ch2);
						$status = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
						if (intval($status) != 200) throw new Exception("HTTP $status\n$response_body");
						$response = json_decode($response_body);

						foreach($response as $section): ?>
							<option value="<?php echo $section->id; ?>" <?php if($instance['playlist']==$section->id) {echo 'selected';} ?> >Playlist: <?php echo $section->title; ?></option> 
						<?php endforeach;
					}
				?>
			</select>
		<?php else: ?>
			(please set Client ID and Username and click "Save")<input type="hidden" id="<?php echo $this->get_field_id('playlist'); ?>" value="allUser"></input>
		<?php endif; ?>
	</p>
	<h4>Display These Stats:</h4>
	<p>
		<label for="<?php echo $this->get_field_id('show_play_count'); ?>"><?php _e('Show Play Count:', $widget_slug); ?></label> 
		<input class="checkbox" id="<?php echo $this->get_field_id('show_play_count'); ?>" name="<?php echo $this->get_field_name('show_play_count'); ?>" type="checkbox" value="showPlayCountTrue" <?php if(esc_attr( $instance['show_play_count'] ) == "showPlayCountTrue") { echo 'checked'; } ?> />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('show_download_count'); ?>"><?php _e('Show Download Count:', $widget_slug); ?></label> 
		<input class="checkbox" id="<?php echo $this->get_field_id('show_download_count'); ?>" name="<?php echo $this->get_field_name('show_download_count'); ?>" type="checkbox" value="showDownloadCountTrue" <?php if(esc_attr( $instance['show_download_count'] ) == "showDownloadCountTrue") { echo 'checked'; } ?> />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('show_favoritings_count'); ?>"><?php _e('Show Favoritings Count:', $widget_slug); ?></label> 
		<input class="checkbox" id="<?php echo $this->get_field_id('show_favoritings_count'); ?>" name="<?php echo $this->get_field_name('show_favoritings_count'); ?>" type="checkbox" value="showFavoritingsTrue" <?php if(esc_attr( $instance['show_favoritings_count'] ) == "showFavoritingsTrue") { echo 'checked'; } ?> />
	</p>
</div>