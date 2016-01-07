// v0.2

(function($) {

// Find all audio elements on page
var audioTracks = document.querySelectorAll('.at-audio');

// Function to pass each audio element to
var audioSync = function(audio) {
	var startTimes = [], // When each new paragraph or highlighted piece starts
		transcript, snippets, // The matching text for the audio, and it's individual pieces for highlighting
		interval; // timer interval

	// Match the audio and text
	var name = audio.getAttribute('data-at-transcript');
	transcript = document.querySelector('.at-transcript[data-name="' + name + '"]');

	// Pull all the marked start times into an array
	snippets = transcript.querySelectorAll('[data-time-start]');
	for(var i = 0; i < snippets.length; ++i) {
		startTimes.push( snippets[i].getAttribute('data-time-start') );
	};

	// Remove any highlights
	var removeHighlights = function() {
		$(transcript).find('.audio-transcript-highlight').removeClass('audio-transcript-highlight');
	}

	// Check the current track time for any matching transcript elements
	var followTrack = function() {
		var curTime = audio.currentTime;

		var greaterThanTimes = startTimes.filter(function(val) {
			return val <= curTime ? true : false;
		});
		greaterThanTimes.sort(function(a, b) {
			return a - b;
		});
		var activeSnippet = greaterThanTimes.pop();

		var $paragraph = $( transcript.querySelector('[data-time-start="' + activeSnippet + '"]') );

		removeHighlights();
		if($paragraph.length && !$paragraph.hasClass('.audio-transcript-highlight')) {
			if($paragraph.attr('data-time-end') == undefined || $paragraph.attr('data-time-end') > curTime) {
				$paragraph.addClass('audio-transcript-highlight');
			}
		}
	}

	var stopTrack = function() {
		window.clearInterval(interval);
	}

	var seekTrack = function() {
		var seekTime = this.getAttribute('data-time-start');
		audio.currentTime = seekTime;
		if(audio.paused) {
			audio.play();
		}
		followTrack();
	}
	var trackEnded = function() {
		stopTrack();
		removeHighlights();
	}

	// Setup event listeners
	audio.addEventListener('playing', function() {
		interval = window.setInterval(followTrack, 500);
	});
	audio.addEventListener('ended', trackEnded);
	audio.addEventListener('pause', stopTrack);
	audio.addEventListener('seeking', followTrack);
	for(i = 0; i < snippets.length; ++i) {
		snippets[i].addEventListener('click', seekTrack);
	}
};

// Parse each audio element
for(var i = 0; i < audioTracks.length; ++i) {
	audioSync( audioTracks[i] );
};

})(jQuery);