<?php

/**
 * Class WPLBI_Admin
 */
class WPLBI_Admin {

	/**
	 * @var array 
	 */
	private $_settings;

	/**
	 */
	function __construct() {

		add_action( 'admin_menu', array( $this, '_admin_menu' ) );
		add_action( 'admin_init', array( $this, '_admin_init' ) );
		//add_action( 'wp_check_filetype_and_ext', array( $this, '_wp_check_filetype_and_ext' ), 10, 4 );
		add_action( 'upload_mimes', array( $this, '_upload_mimes' ) );
		add_action( 'media_send_to_editor', array( $this, '_media_send_to_editor' ), 10, 3 );
	}

	/**
	 * @param string $html       HTML markup for a media item sent to the editor.
	 * @param int    $send_id    The first key from the $_POST['send'] data.
	 * @param array  $attachment Array of attachment metadata.
	 * @return string
	 */
	function _media_send_to_editor( $html, $send_id, $attachment ) {

		do {

			$attachment = get_post( $send_id );

			if ( 'attachment' !== $attachment->post_type ) {
				break;
			}

			$filepath = wplbi()->get_local_filepath_from_url( $attachment->guid );

			if ( ! wplbi()->verify_atom_xml( $filepath ) ) {
				$html = "The uploaded file is not a valid Blogger Export file.";
				break;
			}

			$html = $attachment->guid;

		} while ( false );

		return $html;

	}


//	/**
//	 * @param array $types
//	 * @return array
//	 */
//	function _wp_check_filetype_and_ext( $details, $filepath, $filename, $mime_types ) {
//
//		if ( 'xml' === strtolower( pathinfo($filename, PATHINFO_EXTENSION) ) ) {
//			$details['ext'] =  'xml';
//			$details['type'] =  'application/xml';
//			$details['proper_filename'] =  $filename;
//		}
//		return $details;
//	}
//
	/**
	 * @param array $types
	 * @return array
	 */
	function _upload_mimes( $types ) {
		$types['xml' ] = 'application/xml';
		return $types;
	}

	function _admin_init() {

		add_settings_section(
			'wplbi_import_details',
			__( 'Import Parameters', 'wplbi' ),
			array( $this, '_section_header' ),
			'large-blogger-import'
		);

		$this->add_settings_field( 'wp_author_id', __( 'WP Author ID for Posts:', 'wplbi' ) );

		$this->add_settings_field( 'export_file_url',  __( 'URL of Blogger Export File:', 'wplbi' ) );

		$this->add_settings_field( 'blogger_author_url', __( 'Author\'s Blogger URL:', 'wplbi' ) );

		register_setting(
			'large-blogger-import',
			'export_file_url',
			array( $this, '_validate' )
		);

	}

	function add_settings_field( $field_name, $label ) {
		add_settings_field(
			$field_name,
			$label,
			array( $this, '_section_option' ),
			'large-blogger-import',
			'wplbi_import_details',
			$field_name
		);
	}

	/**
	 *
	 */
	function _validate( $value ) {
		return $value;
	}

	/**
	 *
	 */
	function _section_option( $var_name ) {
		$this->_require_template( $var_name );
	}

	/**
	 *
	 */
	function _section_header() {
		echo '<p>Details required for your Import</p>';
	}

	/**
	 *
	 */
	function _admin_menu() {

		add_management_page(
			__( 'Large Blogger Import', 'wplbi' ),
			__( 'Large Blogger Import', 'wplbi' ),
			'manage_options',
			'large-blogger-import',
			array( $this, '_options_page' )
		);

	}

	/**
	 *
	 */
	function _options_page() {
		$this->_require_template( 'options-page' );
	}

	function settings(){
		if ( ! $this->_settings ) {
			$this->_settings = new WPLBI_Settings();
		}
		return $this->_settings;
	}

	/**
	 * @var string $template
	 */
	private function _require_template( $template ) {
		$settings = $this->settings();
		$template = str_replace( array( ' ', '_' ), '-', $template );
		require dirname( __DIR__ ) . "/templates/{$template}.php";
	}



}