<?php
/**
 * @var WPEI_Settings $settings
 */

$settings_name = WPEI::SETTINGS_NAME;
wp_dropdown_users( array(
	'show_option_all'  => false,
	'selected'         => $settings->wp_author_id ? intval( $settings->wp_author_id ) : 1,
	'include_selected' => true,
	'name' => WPEI::SETTINGS_NAME . '[wp_author_id]',
	'id' => 'wp_author_id',
));
?>
</td></tr>
<tr id="blogger-author-url-row" class="verified-info-row" style="display:none;">
	<th scope="row"><?php _e( 'Author URL:', 'wpei' ); ?></th>
	<td>
		<span id="blogger-author-url" style="display:inline;" class="verified-info">
			<span class="spinner is-active" style="float:left;">
		</span>
	</td>
</tr>
<tr id="blogger-blog-url-row" class="verified-info-row" style="display:none;">
	<th scope="row"><?php _e( 'Blog URL:', 'wpei' ); ?></th>
	<td>
		<span id="blogger-blog-url" style="display:inline;" class="verified-info">
			<span class="spinner is-active" style="float:left;">
		</span>
	</td>
</tr>
<tr id="entry-count-row" class="verified-info-row" style="display:none;">
	<th scope="row"><?php _e( 'Entry Count:', 'wpei' ); ?></th>
	<td>
		<span id="entry-count" style="display:inline;float:left;" class="verified-info">
			<span class="spinner is-active"></span>
		</span>
		<span class="wpei-info"><?php _e( "Entry count equals the sum of posts plus the sum of comments." ); ?></span>
