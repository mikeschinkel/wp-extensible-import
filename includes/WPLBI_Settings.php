<?php

/**
 * Class WPLBI_Settings
 */
class WPLBI_Settings extends WPLBI_Base {

	/**
	 * @return string
	 */
	public $export_file_url;

	/**
	 * @return int
	 */
	public $wp_author_id;


	/**
	 * @return string
	 */
	public $blogger_author_url;

	/**
	 * @return mixed|void
	 */
	function __construct( $settings = null ) {
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

		return (array) get_option( WPLBI::SETTINGS_NAME );

	}

	/**
	 */
	function save_settings() {

		$settings = (array) $this;
		unset( $settings['extra'] );

		foreach( $settings as $field_name => $value ) {
			$settings[$field_name] = sanitize_option( $field_name, $value );
		}

		return update_option( WPLBI::SETTINGS_NAME, $settings );

	}

}