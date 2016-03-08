<?php
defined( 'ABSPATH' ) or die();

add_filter( 'media_upload_tabs', 'at_media_tab' );

function at_media_tab( $tabs ) {
    if(isset($_GET['at_tab'])) {
        $newtab = array('at_insert_media_tab' => __('Audio Files'));
        // unset($tabs['library']);
        return array_merge($tabs, $newtab);
    }
    return $tabs;
}


add_action('media_upload_at_insert_media_tab', 'media_upload_ell_gmap_tab');

function media_upload_ell_gmap_tab() {
    return wp_iframe('media_upload_ell_gmap_form', $errors );
}

/**
 * MODIFIED FROM wp-admin/includes/media.php media_upload_library_form()
 * Outputs the legacy media upload form for the media library.
 *
 * @since 2.5.0
 *
 * @global wpdb      $wpdb
 * @global WP_Query  $wp_query
 * @global WP_Locale $wp_locale
 * @global string    $type
 * @global string    $tab
 * @global array     $post_mime_types
 *
 * @param array $errors
 */
function media_upload_ell_gmap_form($errors) {
    global $wpdb, $wp_query, $wp_locale, $type, $tab, $post_mime_types;

    media_upload_header();

    $post_id = isset( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : 0;

    $form_action_url = admin_url("media-upload.php?type=$type&tab=at_insert_media_tab&post_id=$post_id");
    /** This filter is documented in wp-admin/includes/media.php */
    $form_action_url = apply_filters( 'media_upload_form_url', $form_action_url, $type );
    $form_class = 'media-upload-form validate';

    if ( get_user_setting('uploader') )
        $form_class .= ' html-uploader';

    $q = $_GET;
    $q['posts_per_page'] = 10;
    $q['paged'] = isset( $q['paged'] ) ? intval( $q['paged'] ) : 0;
    if ( $q['paged'] < 1 ) {
        $q['paged'] = 1;
    }
    $q['offset'] = ( $q['paged'] - 1 ) * 10;
    if ( $q['offset'] < 1 ) {
        $q['offset'] = 0;
    }

    list($post_mime_types, $avail_post_mime_types) = wp_edit_attachments_query( $q );

?>

<form id="filter" method="get">
<input type="hidden" name="at_tab" value="true" />
<input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>" />
<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />
<input type="hidden" name="post_id" value="<?php echo (int) $post_id; ?>" />
<input type="hidden" name="post_mime_type" value="<?php echo isset( $_GET['post_mime_type'] ) ? esc_attr( $_GET['post_mime_type'] ) : ''; ?>" />
<input type="hidden" name="context" value="<?php echo isset( $_GET['context'] ) ? esc_attr( $_GET['context'] ) : ''; ?>" />

<p id="media-search" class="search-box" style="float: left">
    <label class="screen-reader-text" for="media-search-input"><?php _e('Search Media');?>:</label>
    <input type="search" id="media-search-input" name="s" value="<?php the_search_query(); ?>" />
    <?php submit_button( __( 'Search Media' ), 'button', '', false ); ?>
</p>

<ul class="subsubsub" style="display: none">
<?php
$type_links = array();
$_num_posts = (array) wp_count_attachments();
$matches = wp_match_mime_types(array_keys($post_mime_types), array_keys($_num_posts));
foreach ( $matches as $_type => $reals )
    foreach ( $reals as $real )
        if ( isset($num_posts[$_type]) )
            $num_posts[$_type] += $_num_posts[$real];
        else
            $num_posts[$_type] = $_num_posts[$real];
// If available type specified by media button clicked, filter by that type
if ( empty($_GET['post_mime_type']) && !empty($num_posts[$type]) ) {
    $_GET['post_mime_type'] = $type;
    list($post_mime_types, $avail_post_mime_types) = wp_edit_attachments_query();
}
if ( empty($_GET['post_mime_type']) || $_GET['post_mime_type'] == 'all' )
    $class = ' class="current"';
else
    $class = '';
$type_links[] = '<li><a href="' . esc_url(add_query_arg(array('post_mime_type'=>'all', 'paged'=>false, 'm'=>false))) . '"' . $class . '>' . __('All Types') . '</a>';
foreach ( $post_mime_types as $mime_type => $label ) {
    $class = '';

    if ( !wp_match_mime_types($mime_type, $avail_post_mime_types) )
        continue;

    if ( isset($_GET['post_mime_type']) && wp_match_mime_types($mime_type, $_GET['post_mime_type']) )
        $class = ' class="current"';

    $type_links[] = '<li><a href="' . esc_url(add_query_arg(array('post_mime_type'=>$mime_type, 'paged'=>false))) . '"' . $class . '>' . sprintf( translate_nooped_plural( $label[2], $num_posts[$mime_type] ), '<span id="' . $mime_type . '-counter">' . number_format_i18n( $num_posts[$mime_type] ) . '</span>') . '</a>';
}
/**
 * Filter the media upload mime type list items.
 *
 * Returned values should begin with an `<li>` tag.
 *
 * @since 3.1.0
 *
 * @param array $type_links An array of list items containing mime type link HTML.
 */
echo implode(' | </li>', apply_filters( 'media_upload_mime_type_links', $type_links ) ) . '</li>';
unset($type_links);
?>
</ul>

<div class="tablenav">

<?php
$page_links = paginate_links( array(
    'base' => add_query_arg( 'paged', '%#%' ),
    'format' => '',
    'prev_text' => __('&laquo;'),
    'next_text' => __('&raquo;'),
    'total' => ceil($wp_query->found_posts / 10),
    'current' => $q['paged'],
));

if ( $page_links )
    echo "<div class='tablenav-pages'>$page_links</div>";
?>

<div class="alignleft actions">
<?php

$arc_query = "SELECT DISTINCT YEAR(post_date) AS yyear, MONTH(post_date) AS mmonth FROM $wpdb->posts WHERE post_type = 'attachment' ORDER BY post_date DESC";

$arc_result = $wpdb->get_results( $arc_query );

$month_count = count($arc_result);
$selected_month = isset( $_GET['m'] ) ? $_GET['m'] : 0;

if ( $month_count && !( 1 == $month_count && 0 == $arc_result[0]->mmonth ) ) { ?>
<select name='m'>
<option<?php selected( $selected_month, 0 ); ?> value='0'><?php _e( 'All dates' ); ?></option>
<?php
foreach ($arc_result as $arc_row) {
    if ( $arc_row->yyear == 0 )
        continue;
    $arc_row->mmonth = zeroise( $arc_row->mmonth, 2 );

    if ( $arc_row->yyear . $arc_row->mmonth == $selected_month )
        $default = ' selected="selected"';
    else
        $default = '';

    echo "<option$default value='" . esc_attr( $arc_row->yyear . $arc_row->mmonth ) . "'>";
    echo esc_html( $wp_locale->get_month($arc_row->mmonth) . " $arc_row->yyear" );
    echo "</option>\n";
}
?>
</select>
<?php } ?>

<?php submit_button( __( 'Filter &#187;' ), 'button', 'post-query-submit', false ); ?>

</div>

<br class="clear" />
</div>
</form>

<form enctype="multipart/form-data" method="post" action="<?php echo esc_url( $form_action_url ); ?>" class="<?php echo $form_class; ?>" id="library-form">

<?php wp_nonce_field('media-form'); ?>
<?php //media_upload_form( $errors ); ?>

<script type="text/javascript">
<!--
jQuery(function($){
    var preloaded = $(".media-item.preloaded");
    if ( preloaded.length > 0 ) {
        preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
        updateMediaForm();
    }
});
-->
</script>

<div id="media-items">
<?php add_filter('attachment_fields_to_edit', 'media_post_single_attachment_fields_to_edit', 10, 2); ?>
<?php echo at_get_media_items(null, $errors); ?>
</div>
<input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $post_id; ?>" />
</form>
<?php
}






/**
 * Retrieve HTML for media items of post gallery.
 *
 * The HTML markup retrieved will be created for the progress of SWF Upload
 * component. Will also create link for showing and hiding the form to modify
 * the image attachment.
 *
 * @since 2.5.0
 *
 * @global WP_Query $wp_the_query
 *
 * @param int $post_id Optional. Post ID.
 * @param array $errors Errors for attachment, if any.
 * @return string
 */
function at_get_media_items( $post_id, $errors ) {
    $attachments = array();
    if ( $post_id ) {
        $post = get_post($post_id);
        if ( $post && $post->post_type == 'attachment' )
            $attachments = array($post->ID => $post);
        else
            $attachments = get_children( array( 'post_parent' => $post_id, 'post_type' => 'attachment', 'orderby' => 'menu_order ASC, ID', 'order' => 'DESC') );
    } else {
        if ( is_array($GLOBALS['wp_the_query']->posts) )
            foreach ( $GLOBALS['wp_the_query']->posts as $attachment )
                $attachments[$attachment->ID] = $attachment;
    }

    $output = '';
    foreach ( (array) $attachments as $id => $attachment ) {
        if ( $attachment->post_status == 'trash' )
            continue;
        if ( $item = at_get_media_item( $id, array( 'errors' => isset($errors[$id]) ? $errors[$id] : null) ) )
            $output .= "\n<div id='media-item-$id' class='media-item child-of-$attachment->post_parent preloaded'><div class='progress hidden'><div class='bar'></div></div><div id='media-upload-error-$id' class='hidden'></div><div class='filename hidden'></div>$item\n</div>";
    }

    return $output;
}

/**
 * Retrieve HTML form for modifying the image attachment.
 *
 * @since 2.5.0
 *
 * @global string $redir_tab
 *
 * @param int $attachment_id Attachment ID for modification.
 * @param string|array $args Optional. Override defaults.
 * @return string HTML form for attachment.
 */
function at_get_media_item( $attachment_id, $args = null ) {
    global $redir_tab;

    if ( ( $attachment_id = intval( $attachment_id ) ) && $thumb_url = wp_get_attachment_image_src( $attachment_id, 'thumbnail', true ) )
        $thumb_url = $thumb_url[0];
    else
        $thumb_url = false;

    $post = get_post( $attachment_id );
    $current_post_id = !empty( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 0;

    $default_args = array(
        'errors' => null,
        'send' => $current_post_id ? post_type_supports( get_post_type( $current_post_id ), 'editor' ) : true,
        'delete' => true,
        'toggle' => true,
        'show_title' => true
    );
    $args = wp_parse_args( $args, $default_args );

    /**
     * Filter the arguments used to retrieve an image for the edit image form.
     *
     * @since 3.1.0
     *
     * @see get_media_item
     *
     * @param array $args An array of arguments.
     */
    $r = apply_filters( 'get_media_item_args', $args );


    $file = get_attached_file( $post->ID );
    $filename = esc_html( wp_basename( $file ) );
    $title = esc_attr( $post->post_title );

    $post_mime_types = get_post_mime_types();
    $keys = array_keys( wp_match_mime_types( array_keys( $post_mime_types ), $post->post_mime_type ) );
    $type = reset( $keys );
    $type_html = "<input type='hidden' id='type-of-$attachment_id' value='" . esc_attr( $type ) . "' />";

    $form_fields = get_attachment_fields_to_edit( $post, $r['errors'] );


    $display_title = ( !empty( $title ) ) ? $title : $filename; // $title shouldn't ever be empty, but just in case
    $display_title = $r['show_title'] ? "<div class='filename new'><span class='title'>" . wp_html_excerpt( $display_title, 60, '&hellip;' ) . "</span></div>" : '';

    $gallery = ( ( isset( $_REQUEST['tab'] ) && 'gallery' == $_REQUEST['tab'] ) || ( isset( $redir_tab ) && 'gallery' == $redir_tab ) );
    $order = '';

    foreach ( $form_fields as $key => $val ) {
        if ( 'menu_order' == $key ) {
            if ( $gallery )
                $order = "<div class='menu_order'> <input class='menu_order_input' type='text' id='attachments[$attachment_id][menu_order]' name='attachments[$attachment_id][menu_order]' value='" . esc_attr( $val['value'] ). "' /></div>";
            else
                $order = "<input type='hidden' name='attachments[$attachment_id][menu_order]' value='" . esc_attr( $val['value'] ) . "' />";

            unset( $form_fields['menu_order'] );
            break;
        }
    }

    $media_dims = '';
    $meta = wp_get_attachment_metadata( $post->ID );
    if ( isset( $meta['width'], $meta['height'] ) )
        $media_dims .= "<span id='media-dims-$post->ID'>{$meta['width']}&nbsp;&times;&nbsp;{$meta['height']}</span> ";

    /**
     * Filter the media metadata.
     *
     * @since 2.5.0
     *
     * @param string  $media_dims The HTML markup containing the media dimensions.
     * @param WP_Post $post       The WP_Post attachment object.
     */
    $media_dims = apply_filters( 'media_meta', $media_dims, $post );

    $image_edit_button = '';
    if ( wp_attachment_is_image( $post->ID ) && wp_image_editor_supports( array( 'mime_type' => $post->post_mime_type ) ) ) {
        $nonce = wp_create_nonce( "image_editor-$post->ID" );
        $image_edit_button = "<input type='button' id='imgedit-open-btn-$post->ID' onclick='imageEdit.open( $post->ID, \"$nonce\" )' class='button' value='" . esc_attr__( 'Edit Image' ) . "' /> <span class='spinner'></span>";
    }

    $attachment_url = get_permalink( $attachment_id );

    if ( $r['send'] ) {
        $r['send'] = get_submit_button( __( 'Insert into Post' ), 'button', "send[$attachment_id]", false );
    }

    $delete = empty( $r['delete'] ) ? '' : $r['delete'];
    if ( $delete && current_user_can( 'delete_post', $attachment_id ) ) {
        if ( !EMPTY_TRASH_DAYS ) {
            $delete = "<a href='" . wp_nonce_url( "post.php?action=delete&amp;post=$attachment_id", 'delete-post_' . $attachment_id ) . "' id='del[$attachment_id]' class='delete-permanently'>" . __( 'Delete Permanently' ) . '</a>';
        } elseif ( !MEDIA_TRASH ) {
            $delete = "<a href='#' class='del-link' onclick=\"document.getElementById('del_attachment_$attachment_id').style.display='block';return false;\">" . __( 'Delete' ) . "</a>
             <div id='del_attachment_$attachment_id' class='del-attachment' style='display:none;'>" .
             /* translators: %s: file name */
            '<p>' . sprintf( __( 'You are about to delete %s.' ), '<strong>' . $filename . '</strong>' ) . "</p>
             <a href='" . wp_nonce_url( "post.php?action=delete&amp;post=$attachment_id", 'delete-post_' . $attachment_id ) . "' id='del[$attachment_id]' class='button'>" . __( 'Continue' ) . "</a>
             <a href='#' class='button' onclick=\"this.parentNode.style.display='none';return false;\">" . __( 'Cancel' ) . "</a>
             </div>";
        } else {
            $delete = "<a href='" . wp_nonce_url( "post.php?action=trash&amp;post=$attachment_id", 'trash-post_' . $attachment_id ) . "' id='del[$attachment_id]' class='delete'>" . __( 'Move to Trash' ) . "</a>
            <a href='" . wp_nonce_url( "post.php?action=untrash&amp;post=$attachment_id", 'untrash-post_' . $attachment_id ) . "' id='undo[$attachment_id]' class='undo hidden'>" . __( 'Undo' ) . "</a>";
        }
    } else {
        $delete = '';
    }

    $item = "
    $type_html
    <div class='at-file-actions'>
    $r[send]
    $delete
    </div>
    $order
    $display_title

    <input type='hidden' class='text urlfield' name='attachments[$attachment_id][url]' value='$post->guid'>";


    $defaults = array(
        'input'         => 'text',
        'required'      => false,
        'value'         => '',
        'extra_rows'    => array(),
        'show_in_edit'  => true,
        'show_in_modal' => true,
    );
    $hidden_fields = array();
    foreach ( $form_fields as $id => $field ) {
        if ( $id[0] == '_' )
            continue;

        if ( !empty( $field['tr'] ) ) {
            $item .= $field['tr'];
            continue;
        }

        $field = array_merge( $defaults, $field );
        $name = "attachments[$attachment_id][$id]";

        if ( $field['input'] == 'hidden' ) {
            $hidden_fields[$name] = $field['value'];
            continue;
        }
    }

    foreach ( $hidden_fields as $name => $value )
        $item .= "\t<input type='hidden' name='$name' id='$name' value='" . esc_attr( $value ) . "' />\n";

    if ( $post->post_parent < 1 && isset( $_REQUEST['post_id'] ) ) {
        $parent = (int) $_REQUEST['post_id'];
        $parent_name = "attachments[$attachment_id][post_parent]";
        $item .= "\t<input type='hidden' name='$parent_name' id='$parent_name' value='$parent' />\n";
    }

    return $item;
}

?>