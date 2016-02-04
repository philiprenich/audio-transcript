(function($) {

var toggleAudioControls = function(player) {
    if(player) {
        $('.syncAudio, .syncAudioHelp, .selectedAudio').removeClass('hide');
        $('.selectAudio, .selectAudioHelp').addClass('hide');
    } else {
        $('.syncAudio, .syncAudioHelp, .selectedAudio').addClass('hide');
        $('.selectAudio, .selectAudioHelp').removeClass('hide');
    }
}

$('.selectAudio').click(function() {
    window.send_to_editor = function(html) {
        var url = $(html).attr('href');
        $('.selectedAudio > span').text( url.split('/').pop() );
        $('#at_audio_file').val( url );

        toggleAudioControls(true);

        tb_remove();
    }

    var id = $('#post_ID').val();
    tb_show('', 'media-upload.php?post_id=' + id + '&type=audio&TB_iframe=true&tab=library');
    return false;
});

$('.removeAudio').click(function(e) {
    e.preventDefault();
    $('#at_audio_file').val('');
    toggleAudioControls(false);
});

$('.syncAudio').click(function() {
    var content = $('#content-textarea-clone').text();
    if( $('#wp-content-wrap').hasClass('tmce-active') ) {
        content = $('#content_ifr').contents().find('body').html();
    }
    $('#syncAudioText .syncText .at-transcript').html( content );

    // Find all audio elements on page
    var audioTracks = document.querySelectorAll('.at-audio');
    // Parse each audio element
    for(var i = 0; i < audioTracks.length; ++i) {
        audioSync( audioTracks[i] );
    };
});

$('.sendSync').click(function() {
    var content = $('.syncText .at-transcript').find('.controls').remove().end().html();
    if( content.lastIndexOf('>&nbsp;') == content.length - 7 ) { // Remove trailing hard-coded space
        content = content.slice(0, -6);
    }
    if( $('#wp-content-wrap').hasClass('tmce-active') ) {
        $('#content_ifr').contents().find('body').html( content );
    } else {
        $('#content-textarea-clone').text( content );
        $('#content').text( content );
    }
    tb_remove();
});

})(jQuery);