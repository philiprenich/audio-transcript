(function($) {

$('.selectAudio').click(function() {
    window.send_to_editor = function(html) {
        var url = $(html).attr('href');
        $('.selectedAudio').text( url.split('/').pop() );
        $('#at_audio_file').val( url );
        tb_remove();
    }

    tb_show('', 'media-upload.php?post_id=1&type=audio&TB_iframe=true&tab=library');
    return false;
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