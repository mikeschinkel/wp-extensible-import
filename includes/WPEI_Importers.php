<?php

/**
 *
 */
class WPEI_Importers {

	/**
	 * @var self
	 */
	private static $_instance;

	private static $_import_types = array();

	/**
	 * @return string
	 */
	static function IMPORTER_NAME() {
		return str_replace( '_', ' ', get_class( __CLASS__ ) );
	}

	/**
	 * @return self
	 */
	static function get_instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * WPEI_Importer_Base constructor
	 */
	private function __construct() {
	}

	/**
	 * Returns array of importer class names indexed by import type slugs
	 * @return object[] {
	 *    @type string $label_text
	 *    @type string $class_name
	 * }
	 */
	function import_types() {
		return self::$_import_types;
	}

	/**
	 * Reads /importers/ directory to find importers.
	 *
	 * Every direct subdirectory is an import type slug, and then the first class in the subdirectory
	 * that extends WPEI_Importer_Base is the importer's class.
	 *
	 */
	function initialize_import_types( $plugin_dir ) {

		$root_dir = "{$plugin_dir}/importers";

		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator( $root_dir ) );
		$iterator->setMaxDepth( 1 );    // Depth starts with 0

		$root_dir_regex = '#^' . preg_quote( $root_dir ) . '(.*)$#';

		$import_type = $import_type_regex = null;

		foreach ( $iterator as $file ) {
			$relative_filepath = preg_replace( $root_dir_regex, '$1', $file->getPathname() );

			if ( preg_match( '#^/(\.|\.\.)$#', $relative_filepath ) ) {
				continue;
			}

			if ( preg_match( '#^/([^/]+)/\.$#', $relative_filepath, $match ) ) {
				$import_type = strtolower( $match[ 1 ] );
				$import_type_regex = preg_quote( $match[ 1 ] );
				self::$_import_types[ $import_type ] = false;
				continue;
			}

			if ( preg_match( '#/(\.|\.\.)$#', $relative_filepath ) ) {
				continue;
			}

			if ( ! $import_type ) {
				continue;
			}

			if ( self::$_import_types[ $import_type ] ) {
				continue;
			}

			if ( 'php' !== strtolower( $file->getExtension() ) ) {
				continue;
			}

			if ( ! preg_match( "#^/{$import_type_regex}/(.+\.php)$#i", $relative_filepath, $match ) ) {
				continue;
			}

			$importer_code = file_get_contents( $file->getRealPath() );

			if ( ! preg_match( "#\n\s*class\s*(.+?)\s*extends\s*WPEI_Importer_Base\s*\{#", $importer_code, $match ) ) {
				continue;
			}

			require( $file->getRealPath() );

			self::$_import_types[ $import_type ] = (object) array(
				'label_text' => call_user_func( array( $match[ 1 ], 'IMPORTER_NAME' ) ),
				'class_name' => $match[ 1 ],
			);

		}

	}

}