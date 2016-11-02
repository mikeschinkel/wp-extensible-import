<?php

/**
 * Class WPEI_Settings
 */
class WPEI_Settings extends WPEI_Base {

	/**
	 * @return string
	 */
	public $import_type_name;

	/**
	 * @return string
	 */
	public $import_file_url;

	/**
	 * @return array
	 */
	public $importer_args;

	/**
	 * @return int
	 */
	public $wp_author_id;

	/**
	 * @return string
	 */
	public $entry_count;

	/**
	 * @var self
	 */
	private static $_instance;


	/**
	 * @param string $setting_name
	 *
	 * @return mixed|null
	 */
	function __get( $setting_name ) {

		if ( property_exists( $this, $setting_name ) ) {
			$value = $this->$setting_name;
		} else if ( ! isset( $this->importer_args[ $setting_name ] ) ) {
			$value = null;
		} else {
			$value = $this->importer_args[ $setting_name ];
		}
		return $value;

	}

	/**
	 * @param string $setting_name
	 * @param mixed $value
	 */
	function __set( $setting_name, $value ) {

		if ( property_exists( $this, $setting_name ) ) {
			$this->$setting_name = $value;
		} else {
			$this->importer_args[ $setting_name ] = $value;
		}

	}

	/**
	 * @param array|null $settings
	 *
	 * @return WPEI_Settings
	 */
	static function get_instance( $settings = null ) {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new WPEI_Settings( $settings );
		} else {
			self::$_instance->assign( $settings );
		}
		return self::$_instance;
	}

	/**
	 * @param mixed[] $settings
	 */
	private function __construct( $settings = null ) {

		if ( is_null( $settings ) ) {
			$settings = $this->load_settings();
		}

		if ( is_array( $settings ) ) {
			$this->assign( $settings );
		}

	}

	/**
	 * @return array
	 */
	function load_settings() {

		return (array) get_option( WPEI::SETTINGS_NAME );

	}

	/**
	 * @return array
	 */
	function settings() {
		global $wp_settings_fields;
		$settings = array_intersect_key(
			(array) $this,
			$wp_settings_fields[ WPEI::PAGE_NAME ][ WPEI::SETTINGS_NAME ]
		);
		return $settings;
	}

	/**
	 * @return array
	 */
	function update_settings( $settings ) {
		$this->assign( $this->load_settings() );
		$this->assign( $settings );
		$this->save_settings();
		return $this->settings();
	}

	/**
	 */
	function save_settings() {
		global $wp_filter;
		$save_filter = $wp_filter;
		$function = array( wpei()->admin(), '_sanitize' );
		foreach( $settings = (array) $this as $field_name => $value ) {
			if ( has_filter( "sanitize_option_{$field_name}", $function ) ) {
				wpei()->change_accepted_args( 2, "sanitize_option_{$field_name}", $function );
			}
			$settings[ $field_name ] = sanitize_option( $field_name, $value );
		}

		$wp_filter = $save_filter;
		return update_option( WPEI::SETTINGS_NAME, $settings );

	}

}