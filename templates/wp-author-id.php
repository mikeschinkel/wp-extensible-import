<?php
/**
 * @var WPLBI_Settings $settings
 */

if ( 0 === ( $wp_author_id = intval( $settings->wp_author_id ) ) ) {
	$wp_author_id = 1;
}
$settings_name = WPLBI::SETTINGS_NAME;
?>
<input type="text" name="<?php echo $settings_name; ?>[wp_author_id]" value="<?php echo $wp_author_id; ?>" size="5">
