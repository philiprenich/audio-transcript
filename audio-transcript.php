<?php
/*
* Plugin Name: Audio Transcript
* Plugin URI: http://philiprenich.com
* Description: Sync an audio file with a text transcript
* Version: 0.1
* Author: Philip Renich
* Author URI: http://philiprenich.com
*/

defined( 'ABSPATH' ) or die();

function my_admin_scripts() {
	wp_enqueue_script('at-editor', plugins_url() . '/audio-transcript/js/editor.js', array('jquery','media-upload','thickbox'), false, true);
	wp_enqueue_script('at-sync', plugins_url() . '/audio-transcript/js/sync.js', ['jquery'], false, true);
}
function my_admin_styles() {
	wp_enqueue_style('thickbox');
	wp_enqueue_style( 'audio-transcript-editor-css', plugins_url() . '/audio-transcript/css/at-editor.css');
}
add_action('admin_print_scripts', 'my_admin_scripts');
add_action('admin_print_styles', 'my_admin_styles');

function at_scripts() {
	wp_enqueue_style( 'audio-transcript-css', plugins_url() . '/audio-transcript/css/audio-transcript.css');
	wp_enqueue_script( 'audio-transcript-js', plugins_url() . '/audio-transcript/js/audio-transcript.js', ['jquery'], false, true );
}
function at_upload_meta_box() {
	add_meta_box( 'at_upload', 'Audio Transcript', 'at_upload_meta_box_cb', 'post', 'side', 'high' );
}

add_action( 'wp_enqueue_scripts', 'at_scripts' );
add_action( 'add_meta_boxes', 'at_upload_meta_box' );


function at_upload_meta_box_cb( $post ) {
	wp_nonce_field('at_meta_box_nonce', 'meta_box_nonce');
	$value = get_post_meta( $post->ID, 'at_audio_file', true );
	$player = at_build_player($post);
	?>
	<p class="selectAudio <?php echo $player ? 'hide' : '' ?>">
		<input class="button" type="button" value="Select audio" name="at_audio_file_button" id="at_audio_file_button" />
	</p>

	<p class="howto <?php echo $player ? 'hide' : '' ?> selectAudioHelp">Select or record an audio transcript of this post.</p>
	<!-- <p class="selectedAudio"><?php echo array_pop( explode('/', $value) ) ?></p> -->
	<p><a href="#TB_inline?width=800&height=500&inlineId=syncAudioText" title="Sync audio and text" class="thickbox syncAudio <?php echo $player ? '' : 'hide' ?>">Sync audio and text</a></p>
	<p class="howto <?php echo $player ? '' : 'hide' ?> syncAudioHelp">Set the audio timestamp for each paragraph.</p>

	<p class="selectedAudio <?php echo $player ? '' : 'hide' ?>">
		<span><?php echo array_pop( explode('/', $value) ) ?></span> <a href="#" class="removeAudio"></a>
		<!--or
		<input class="recordAudio button" type="button" value="Record audio" name="at_audio_record_button" id="at_audio_record_button" />-->
	</p>

	<input type="hidden" value="<?php echo $value ?>" name="at_audio_file" id="at_audio_file" />
	<div id="syncAudioText" style="display: none">
		<div class="syncInfo">
			<button class="edit">Stop editing</button>
			<button class="sendSync">Save</button>
		</div>
	<?php
		echo $player['player'];
	?>
		<div class="syncText">
			<div class="at-transcript" data-name="<?php echo $player['name'] ?>">
				<?php echo apply_filters( 'the_content', $post->post_content ); ?>
			</div>
		</div>
	</div>
	<?php
}

add_action( 'save_post', 'at_upload_meta_box_save' );

function at_upload_meta_box_save( $ID ) {
	// Quit for auto saves
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	// Quit if nonce doesn't verify
	if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'at_meta_box_nonce' ) ) return;
	// Quit if user disallowed
	if( !current_user_can( 'edit_post' ) ) return;

	update_post_meta( $ID, 'at_audio_file', $_POST['at_audio_file'] );
}


function url_exists($url) {
	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_exec($ch);
	$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	// $retcode >= 400 -> not found, $retcode = 200, found.
	curl_close($ch);

	if($retcode >= 400) {
		return false;
	}
	return true;
}

function at_build_player($post) {
	$audio = get_post_meta( $post->ID, 'at_audio_file', true );
	if(!$audio) return;

	$name = array_pop( explode('/', $audio) );
	$ogg = substr($audio, 0, -4) . '.ogg';
	if(url_exists($ogg)) {
		$addOgg = '<source src="' . $ogg . '" type="audio/ogg">';
	}

	$player = '<audio preload="metadata" controls data-at-transcript="' . $name . '" class="at-audio">' .
	 				'<source src="' . $audio . '" type="audio/mp3">' .
	 				$addOgg .
				"</audio>\n\r";

	return array( 'player' => $player, 'name' => $name );

}
function at_front_end($content) {
	$post = get_post( get_the_ID() );
	$player = at_build_player($post);

	return $player ? $player['player'] . '<div class="at-transcript" data-name="' . $player['name'] . '">' . $content . '</div>' : $content;
}

add_filter( 'the_content', 'at_front_end' );
?>