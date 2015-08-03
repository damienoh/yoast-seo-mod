<?php
/**
 * Plugin Name: Yoast SEO Mod
 * Version: 2.3.2
 * Plugin URI: https://www.maketecheasier.com
 * Description: Modification for Yoast SEO.
 * Author: Damien Oh
 * Author URI: http://damienoh.com/
 * Text Domain: wordpress-seo
 * License: GPL v3
 */

add_action( 'add_meta_boxes', 'yseomod_add_meta_boxes' );
function yseomod_add_meta_boxes() {
	global $post;

	$redirect = get_post_meta($post->ID,'_yoast_wpseo_redirect',true);
	if(!empty($redirect)){
		return;
	}
	else{
		add_meta_box(
			'yseomod_meta_box',
			__( 'Yoast SEO Mod' ),
			'yseomod_render_meta_box',
			'',
			'normal',
			'default'
		);
	}
}

function yseomod_render_meta_box($post){

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'yseomod_save_meta_box_data', 'yseomod_301_redirect_nonce' );
	?>
	<table class="form-table">
		<tr>
			<th scope="row">
				<?php _e( '301 Redirect', 'wordpress-seo' ); ?>
			</th>
			<td>
				<input type="url" class="large-text ui-autocomplete-input" name="yoast_wpseo_redirect">
				<span class="description"><?php _e( 'The URL that this page should redirect to.', 'wordpress-seo' ); ?></span>
			</td>
		</tr>
	</table>
<?php
}

add_action( 'save_post', 'yseomod_save_meta_box_data' );
function yseomod_save_meta_box_data($post_id){
	if ( ! isset( $_POST['yseomod_301_redirect_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['yseomod_301_redirect_nonce'], 'yseomod_save_meta_box_data' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */

	// Make sure that it is set.
	if ( ! isset( $_POST['yoast_wpseo_redirect'] ) ) {
		return;
	}

	// Sanitize user input.
	$redirect = sanitize_text_field( $_POST['yoast_wpseo_redirect'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, '_yoast_wpseo_redirect', $redirect );
}