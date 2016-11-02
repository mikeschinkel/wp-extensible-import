<?php

/**
 *
 */
class WPEI_Importer_Base extends WPEI_Base {

	/**
	 * @var WPEI_Settings
	 */
	var $settings = null;
	var $import_filepath = null;
	var $last_error = null;

	/**
	 * @var self
	 */
	private static $_instance;

	/**
	 * @return string
	 */
	static function IMPORTER_NAME() {
		return str_replace( '_', ' ', get_class( __CLASS__ ) );
	}

	/**
	 * @param string|null $class_name
	 *
	 * @return self
	 */
	static function get_instance( $class_name = null ) {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new $class_name();
		}
		return self::$_instance;
	}

	/**
	 * WPEI_Importer_Base constructor
	 */
	private function __construct() {
	}

	/**
	 * @param string $setting
	 * @return mixed|null
	 */
	function get_setting( $setting ) {
		return isset( $this->settings[ $setting ] )
			? $this->settings[ $setting ]
			: null;
	}

	/**
	 * @param WPEI_Settings|mixed[] $settings
	 */
	function set_settings( $settings ) {
		$this->settings = WPEI_Settings::get_instance( $settings );
	}

	/**
	 * @param string $url
	 */
	function set_import_url( $url ) {
		$filepath = wpei()->get_filepath_from_url( $url );
		$this->import_filepath = $filepath;
	}

	/**
	 */
	function initialize() {
	}

	/**
	 * @param WPEI_Admin $admin
	 */
	function add_settings_fields( $admin ) {
	}

	/**
	 * @return int|null
	 */
	function entry_count() {
		return 0;
	}

	/**
	 * @return bool|int
	 */
	function validate_import_file( $filepath ) {
		return true;
	}

	/**
	 * @param mixed $value
	 * @param string $field_name
	 * @return mixed
	 */
	function sanitize_fields( $value, $field_name )  {
		return $value;
	}

	/**
	 * @return string
	 */
	function get_template_dir() {
		$reflector = new ReflectionClass( $this );
		$filepath = $reflector->getFileName();
		return dirname( $filepath ) . '/templates';
	}

}