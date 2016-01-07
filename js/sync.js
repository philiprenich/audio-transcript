String.prototype.toHHMMSS = function () {
    var sec_num = parseInt(this, 10); // don't forget the second param
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    // var time    = hours+':'+minutes+':'+seconds;
    var time    = minutes+':'+seconds;
    return time;
}

// Function to pass each audio element to
var audioSync = function(audio) {
	var $ = jQuery;

	var startTimes = [], // When each new paragraph or highlighted piece starts
		transcript, snippets, nonSnippets, // The matching text for the audio, and it's individual pieces for highlighting, and those without time data
		interval, // timer interval
		edit = true; //Edit or play mode

	// Match the audio and text
	var name = audio.getAttribute('data-at-transcript');
	transcript = document.querySelector('.at-transcript[data-name="' + name + '"]');

	// Process p and blockquote that don't have data-time-start yet
	nonSnippets = transcript.querySelectorAll( 'p:not([data-time-start]), blockquote:not([data-time-start])' );
	for(var i = 0; i < nonSnippets.length; i++) {
		nonSnippets[i].setAttribute( 'data-time-start', Math.floor(audio.duration) );
	}

	// Pull all the marked start times into an array
	snippets = transcript.querySelectorAll('[data-time-start]');
	var getStartTimes = function() {
		var times = [];
		for(var i = 0; i < snippets.length; ++i) {
			times.push( snippets[i].getAttribute('data-time-start') );
		};
		return times;
	}
	startTimes = getStartTimes();

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

	var seekTrack = function(el) {
		el = el || this;
		var seekTime = el.getAttribute('data-time-start');
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

	var displayTime = function(el) {
		var time = el.getAttribute( 'data-time-start' );
		el.querySelector( '.controls .start-time' ).textContent = time.toHHMMSS();
		startTimes = getStartTimes();
	}
	var onClickSnippet = function(e) {
		if(e.target.className == 'start-time') return;
		if(!edit) {
			seekTrack(this);
			return;
		}

		this.setAttribute('data-time-start', Math.floor( audio.currentTime ));

		displayTime(this);
	}
	var onAdjustClick = function(e) {
		e.stopPropagation();
		if(!edit) return;

		var snippet = this.parentElement.parentElement;
		var math = 1;
		if( $(this).hasClass( 'backwards' ) ) {
			math = -1;
		}
		var time = parseFloat(snippet.getAttribute( 'data-time-start' ));
		time = time + math;
		snippet.setAttribute( 'data-time-start', time );
		displayTime(snippet);
	}

	var onClickEditSwitch = function(e) {
		e.preventDefault();
		edit = !edit;

		if(edit) {
			$( allControls ).show();
			this.textContent = 'Stop editing';
		} else {
			$( allControls ).hide();
			this.textContent = 'Start editing';
		}
	}

	// Setup event listeners
	audio.addEventListener('playing', function() {
		interval = window.setInterval(followTrack, 500);
	});
	audio.addEventListener('ended', trackEnded);
	audio.addEventListener('pause', stopTrack);
	audio.addEventListener('seeking', followTrack);

	// Setup snippets for editing
	var controls = document.createElement('div');
	controls.setAttribute('class', 'controls');
	controls.innerHTML = '<span class="adjust backwards">&lt;</span><span class="start-time"></span><span class="adjust forwards">&gt;</span>';
	for(i = 0; i < snippets.length; ++i) {
		snippets[i].appendChild( controls.cloneNode( true ) );
		if(snippets[i].hasAttribute( 'data-time-start' )) {
			displayTime(snippets[i]);
		}
		snippets[i].addEventListener('click', onClickSnippet);
		[].forEach.call(snippets[i].querySelectorAll('.adjust'), function(el) { el.addEventListener('click', onAdjustClick); });
	}
	var allControls = document.querySelectorAll('#syncAudioText .controls');

	document.querySelector('#syncAudioText .edit').addEventListener('click', onClickEditSwitch);
};
