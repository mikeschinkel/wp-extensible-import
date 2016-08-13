<?php
/**
 * Plugin Name: WP Import Blogger
 *
 * @see https://developers.google.com/blogger/docs/2.0/developers_guide_protocol#CreatingPublicEntries
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
spl_autoload_register( 'wplbi_autoloader' );
function wplbi_autoloader( $class_name ) {
	if ( is_file( $filepath = __DIR__ . "/includes/{$class_name}.php" ) ) {
		require( $filepath );
	}
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 *
 * @return WPLBI
 */
function wplbi() {
	static $wplbi;
	return isset( $wplbi ) ? $wplbi : ( $wplbi = new WPLBI( __DIR__ ) );
}
wplbi();




