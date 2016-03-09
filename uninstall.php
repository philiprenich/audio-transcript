<?php
defined( 'WP_UNINSTALL_PLUGIN' ) or die();

global $wpdb;

$posts = $wpdb->get_col( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'at_audio_file'" );
if($posts) {
    foreach($posts as $id) {
        $post = get_post( intval($id) );

        $newContent = preg_replace('/( class="")?( data-time-(start|end)="[0-9]*")?/i', '', $post->post_content);
        $result = $wpdb->update( "{$wpdb->prefix}posts", ['post_content' => $newContent], ['ID' => $post->ID] );

        if($result === false) {
            echo "error updating post " . $id . "\n\n";
            print_r($post);
        }

        $revs = wp_get_post_revisions( $post->ID );
        foreach($revs as $rev) {
            $post = get_post( intval($rev->ID) );
            $newContent = preg_replace('/( class="")?( data-time-(start|end)="[0-9]*")?/i', '', $post->post_content);
            $result = $wpdb->update( "{$wpdb->prefix}posts", ['post_content' => $newContent], ['ID' => $post->ID] );

            if($result === false) {
                echo "error updating rev " . $post->ID . "\n\n";
                print_r($post);
            }
        }
    }
}

// Remove custom field for audio transcript
$wpdb->query( "DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key = 'at_audio_file'" );
?>