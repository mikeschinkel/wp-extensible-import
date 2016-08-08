<?php
/**
 * @var WPLBI_Settings $settings
 */

if ( 0 === ( $wp_author_id = intval( $settings->wp_author_id ) ) ) {
	$wp_author_id = 1;
}

?>
<input type="text" name="wplbi_settings[wp_author_id]" value="<?php echo $wp_author_id; ?>" size="5">
