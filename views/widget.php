<script src="https://connect.soundcloud.com/sdk/sdk-3.0.0.js"></script>
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<script>
	var currentlyPlaying = false;
	var currentIndex = 0;
	var tracks = [];
	var currentSoundObject = {};
	var playListUrl = "";

	SC.initialize({
		client_id: "<?= $client_id ?>"
	});

	var url = "";
	<?php 
		if($playlist == "allUser") {
			echo "url = '/users/".$soundcloud_user_id."/tracks.json';";
		} else {
			echo "url = '/playlists/".$playlist."/tracks.json';";
		}
	?>
	
	SC.get(url, {}).then(function(response) {
		tracks = response;
		updateSongLabel();
	});

	var skip = function(forwardBackward) {
		var startAfterChange = false;
		if(currentlyPlaying) {
			stopPlaying();
			startAfterChange = true;
		}
		if(forwardBackward === 1) {
			if(currentIndex+1==tracks.length) {
				currentIndex = 0;
			} else {
				currentIndex++;
			}
		} else {
			if(currentIndex-1==-1) {
				currentIndex = tracks.length-1;
			} else {
				currentIndex--;
			}
		}

		// wait until updateSongLabel is done, then
		// start playing again.
		updateSongLabel(function() {
			if(startAfterChange) {
				startPlaying();
			}
		});
		

			
	};


	var stopPlaying = function() {
		currentSoundObject.pause();
		currentlyPlaying = false;
	};

	var startPlaying = function() {
		currentSoundObject.play();
		currentlyPlaying = true;
	};

	var initScStream  = function(callback) {
		SC.stream("/tracks/"+tracks[currentIndex].id).then(function(player) {
			currentSoundObject = player;
			if (callback) {
				callback();
			}
		});
	};


	var getHowLongAgo = function() {
		var d = new Date(tracks[currentIndex].created_at);
		var today = new Date();

		var ret = parseInt((today.getTime()-d.getTime())/(24*3600*1000));
		if(ret === 0) {
			ret = 1;
		}
		return ret;
	};

	var getHowLongAgoString = function() {
		var hla = getHowLongAgo();
		if(hla === 1) {
			return "1 day";
		} else {
			return hla + " days";
		}
	};



	var updateSongLabel = function(callback) {
		jQuery(".trackTitle").html('<a href="'+tracks[currentIndex].permalink_url+'" target="_blank">' + tracks[currentIndex].title + '</a>');


		if(<?php if($show_play_count === 'showPlayCountTrue') {echo ' true';} else {echo 'false';} ?>) {
			if(typeof(tracks[currentIndex].playback_count) === 'undefined') {
				jQuery(".play_count").hide();
			} else {
				jQuery(".play_count").show();
				jQuery(".play_count span").html(tracks[currentIndex].playback_count);
			}
		} else {
			jQuery(".play_count").hide();
		}

		if(<?php if($show_download_count === 'showDownloadCountTrue') {echo ' true';} else {echo 'false';} ?>) {
			if(typeof(tracks[currentIndex].download_count) === 'undefined') {
				jQuery(".download_count").hide();
			} else {
				jQuery(".download_count").show();
				jQuery(".download_count span").html(tracks[currentIndex].download_count);
			}
		} else {
			jQuery(".download_count").hide();
		}

		if(<?php if($show_favoritings_count === 'showFavoritingsTrue') {echo ' true';} else {echo 'false';} ?>) {
			if(typeof(tracks[currentIndex].favoritings_count) === 'undefined') {
				jQuery(".favoritings_count").hide();
			} else {
				jQuery(".favoritings_count").show();
				jQuery(".favoritings_count span").html(tracks[currentIndex].favoritings_count);
			}
		} else {
			jQuery(".favoritings_count").hide();
		}
		
		jQuery(".howLongAgo").html(getHowLongAgoString() );
		var largeImage = tracks[currentIndex].artwork_url;
		var fullImage = largeImage.replace("large","t200x200");
		jQuery(".artWork > img").attr("src", fullImage);
		initScStream(callback);
	};

	jQuery(document).ready(function() {
		jQuery(".PlayStopButton").click( function(){
			if(currentlyPlaying) {
				stopPlaying();
			} else {
				startPlaying();
			}
			jQuery(".PlayStopButton i").toggleClass("fa-play");
			jQuery(".PlayStopButton i").toggleClass("fa-stop");
		});

		jQuery(".rightArrow").click(function() {
			skip(1);
		});
		jQuery(".leftArrow").click( function() {
			skip(0);
		});
	});
	
</script>
<h3 class="widget-title"><?= $title ?></h3>
<div class="SCWidget">
	
	<div class="middleArtistAndTrack">
		<div class="artWork">
			<img src="" />
			<div class="trackTitle"><a href=""></a></div>
			<div class="howLongAgo" style="display:none"></div>
		</div>
		
	</div>
	<div class="darkTop">
		<div class="controls">
			<div class="leftArrow"><i class="fa fa-backward"></i></div>
			<div class="PlayStopButton play"><i class="fa fa-play"></i></div>
			<div class="rightArrow"><i class="fa fa-forward"></i></div>
		</div>
	</div>
	<div class="bottomFooter">
		<div class="soundCloudLink"><a href="https://soundcloud.com/<?= $soundcloud_url ?>" target="_new"><img src="<?php echo plugins_url( '../css/images/soundcloud.png', __FILE__ ); ?>" / ></a></div>
		<div class="bottomStat play_count"><span></span></div>
		<div class="bottomStat download_count"><span></span></div>
		<div class="bottomStat favoritings_count"><span></span></div>
	</div>
</div>