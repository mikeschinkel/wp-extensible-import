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
spl_autoload_register( 'wpei_autoloader', true, true );
function wpei_autoloader( $class_name ) {
	if ( is_file( $filepath = __DIR__ . "/includes/{$class_name}.php" ) ) {
		require( $filepath );
	}
	/**
	 * @TODO Make this generic
	 */
	if ( is_file( $filepath = __DIR__ . "/importers/blogger/{$class_name}.php" ) ) {
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
 * @return WPEI
 */
function wpei() {
	static $wpei;
	return isset( $wpei ) ? $wpei : ( $wpei = new WPEI( __DIR__ ) );
}
wpei();




