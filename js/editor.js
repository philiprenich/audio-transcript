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
    $('.fileUrlHelp').addClass('hide');

    var bak_send_to_editor;
    if(typeof window.send_to_editor == 'function') {
        bak_send_to_editor = window.send_to_editor;
    }
    window.send_to_editor = function(html) {
        var url = $(html).attr('href');
        var name = url.split('/').pop();

        $('.selectedAudio > span').text( name );
        $('#at_audio_file').val( url );

        var $audio = $('<audio>');
        $audio
            .attr('preload', 'metadata')
            .attr('data-at-transcript', name)
            .prop('controls', true)
            .addClass('at-audio');
        $audio.append('<source>');
        $audio.find('source')
            .attr('type', 'audio/mp3')
            .attr('src', url);
        $('#syncAudioText').find('.postbox').after( $audio ).end()
            .find('.at-transcript').attr('data-name', name);


        toggleAudioControls(true);

        tb_remove();

        window.send_to_editor = bak_send_to_editor;
    }

    var id = $('#post_ID').val();
    tb_show('', 'media-upload.php?post_id=' + id + '&type=audio&tab=at_insert_media_tab&at_tab=true&TB_iframe=true');
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