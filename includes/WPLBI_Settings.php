<?php

/**
 * Class WPLBI_Settings
 */
class WPLBI_Settings extends WPLBI_Base {

	const SETTINGS_NAME = 'wplbi_settings';

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

		if ( ! $settings ) {
			$this->assign( $settings );
		}

	}

	/**
	 * @return array
	 */
	function load_settings() {

		return get_option( self::SETTINGS_NAME );

	}

	/**
	 */
	function save_settings() {

		return update_option( self::SETTINGS_NAME, (array) $this );

	}

}