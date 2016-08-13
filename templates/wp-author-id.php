<?php
/**
 * @var WPLBI_Settings $settings
 */

$settings_name = WPLBI::SETTINGS_NAME;
wp_dropdown_users( array(
	'show_option_all'  => false,
	'selected'         => $settings->wp_author_id ? intval( $settings->wp_author_id ) : 1,
	'include_selected' => true,
	'name' => WPLBI::SETTINGS_NAME . '[wp_author_id]',
	'id' => 'wp_author_id',
));
?>
</td></tr>
<tr id="entry-count-row" class="verified-info-row" style="display:none;">
	<th scope="row"><?php _e( 'Entry Count:', 'wplbi' ); ?></th>
	<td>
		<span id="entry-count" style="display:inline;float:left;" class="verified-info">
			<span class="spinner is-active"></span>
		</span>
		<span class="wplbi-info"><?php _e( "Entry count equals the sum of posts plus the sum of comments." ); ?></span>
	</td>
</tr>
<tr id="bloger-author-url-row" class="verified-info-row" style="display:none;">
	<th scope="row"><?php _e( 'Author URL:', 'wplbi' ); ?></th>
	<td>
		<span id="bloger-author-url" style="display:inline;" class="verified-info">
			<span class="spinner is-active" style="float:left;">
		</span>
