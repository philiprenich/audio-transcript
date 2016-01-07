<?php
defined( 'WP_UNINSTALL_PLUGIN' ) or die();

$color = 'audio-transcript_highlight-color';
$alpha = 'audio-transcript_highlight-alpha';

delete_option( $color );
delete_option( $alpha );

// For site options in Multisite
delete_site_option( $color );
delete_site_option( $alpha );
?>