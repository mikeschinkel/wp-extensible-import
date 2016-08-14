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
	public $blogger_blog_url;

	/**
	 * @return string
	 */
	public $blogger_author_url;

	/**
	 * @return string
	 */
	public $entry_count;

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
	 * @return array
	 */
	function settings() {
		global $wp_settings_fields;
		$settings = array_intersect_key(
			(array) $this,
			$wp_settings_fields[ WPLBI::PAGE_NAME ][ WPLBI::SETTINGS_NAME ]
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
	}

	/**
	 */
	function save_settings() {
		global $wp_filter;
		$save_filter = $wp_filter;
		$function = array( wplbi()->admin(), '_sanitize' );
		foreach( $settings = (array) $this as $field_name => $value ) {
			if ( has_filter( "sanitize_option_{$field_name}", $function ) ) {
				wplbi()->change_accepted_args( 2, "sanitize_option_{$field_name}", $function );
			}
			$settings[ $field_name ] = sanitize_option( $field_name, $value );
		}

		$wp_filter = $save_filter;
		return update_option( WPLBI::SETTINGS_NAME, $settings );

	}

}