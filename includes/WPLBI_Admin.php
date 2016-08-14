<?php

/**
 * Class WPLBI_Admin
 */
class WPLBI_Admin {

	/**
	 * @var WPLBI_Settings
	 */
	private $_settings;

	/**
	 */
	function __construct() {

		add_action( 'admin_menu', array( $this, '_admin_menu' ) );
		add_action( 'admin_init', array( $this, '_admin_init' ) );
		//add_action( 'wp_check_filetype_and_ext', array( $this, '_wp_check_filetype_and_ext' ), 10, 4 );
		add_action( 'upload_mimes', array( $this, '_upload_mimes' ) );
		add_action( 'media_send_to_editor', array( $this, '_media_send_to_editor' ), 10, 2 );

		$this->_settings = new WPLBI_Settings();

		add_action( 'wp_ajax_verify_export', array( $this, '_wp_ajax_verify_export' ) );
	}

	/**
	 *
	 */
	function _wp_ajax_import_content() {

		$export_url = esc_url_raw( $_POST[ 'export_url' ], array( 'http','https' ) );
		if ( trim( $this->_settings->export_file_url ) !== trim( $export_url ) ) {
			$result = array(
				'result' => 'error',
				'message' => __( 'Export file not yet verified', 'wplbi' ),
			);

		} else {

			$this->import_content();

			$result = array(
				'result'           => 'success',
				'entryCount'       => $entry_count,
				'bloggerBlogUrl'   => $blogger_blog_url,
				'bloggerAuthorUrl' => $blogger_author_url,
			);

		}
		echo json_encode( $result );
		die();

	}


	/**
	 *
	 */
	function import_content() {
		$importer = new WPLBI_Import();
		return $importer->import_content( wplbi()->export_filepath() );
	}


	/**
	 *
	 */
	function _wp_ajax_verify_export() {

		$export_url = esc_url_raw( $_POST[ 'export_url' ], array( 'http','https' ) );

		if ( is_null( $entry_count = wplbi()->entry_count() ) ) {

			$result = array(
				'result' => 'error',
				'message' => __( 'Invalid Blogger Export File', 'wplbi' ),
			);

		} else {

			$blogger_blog_url = $this->blogger_blog_url();
			$blogger_author_url = $this->blogger_author_url();

			$this->_settings->update_settings( array(
				'entry_count'        => $entry_count,
				'blogger_blog_url'   => $blogger_blog_url = $this->blogger_blog_url(),
				'blogger_author_url' => $blogger_author_url = $this->blogger_author_url(),
			));

			$result = array(
				'result'           => 'success',
				'entryCount'       => $entry_count,
				'bloggerBlogUrl'   => $blogger_blog_url,
				'bloggerAuthorUrl' => $blogger_author_url,
			);

		}
		echo json_encode( $result );
		die();

	}

	/**
	 * @return string|null
	 */
	function blogger_blog_url() {
		$reader = new XMLReader;
		$blog_url = null;
		if ( is_file( $xml_file = wplbi()->export_filepath() ) ) {
			$reader->open( $xml_file );
			while ( $reader->read() ) {
				if ( 'link' !== $reader->name ) {
					continue;
				}
				if ( 'alternate' !== $reader->getAttributeNo( 0 ) ) {
					continue;
				}
				$blog_url = $reader->getAttribute( 'href' );
				break;
			}
		}
		return $blog_url;
	}

	/**
	 * @return string|null
	 */
	function blogger_blog_subdomain() {
		$reader = new XMLReader;
		$blog_subdomain = null;
		if ( is_file( $xml_file = wplbi()->export_filepath() ) ) {
			$reader->open( $xml_file );
			while ( $reader->read() ) {
				$value = $reader->value;
				if ( false !== strpos( $value, '.settings.BLOG_SUBDOMAIN' ) ) {
					break;
				}
			}
			while ( $reader->read() ) {
				if ( 'content' !== $reader->name ) {
					continue;
				}
				$reader->read();
				$blog_subdomain = (string) $reader->value;
				break;
			}
		}
		return $blog_subdomain;
	}

	function blogger_author_url() {
		$reader = new XMLReader;
		$author_url = null;
		if ( is_file( $xml_file = wplbi()->export_filepath() ) ) {
			$reader->open( $xml_file );
			while ( $reader->read() ) {
				if ( preg_match( '#^https://www.blogger.com/profile/#', $reader->value, $match ) ) {
					$author_url = $reader->value;
					break;
				}
			}
		}
		return $author_url;
	}

	/**
	 * @param string $html       HTML markup for a media item sent to the editor.
	 * @param int    $send_id    The first key from the $_POST['send'] data.
	 * @return string
	 */
	function _media_send_to_editor( $html, $send_id ) {

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
			WPLBI::SETTINGS_NAME,
			__( 'Import Parameters', 'wplbi' ),
			array( $this, '_section_header' ),
			WPLBI::PAGE_NAME
		);

		$this->add_settings_field( 'export_file_url',  __( 'URL of Blogger Export File:', 'wplbi' ) );

		$this->add_settings_field( 'wp_author_id', __( 'Author for Posts:', 'wplbi' ) );

		//$this->add_settings_field( 'blogger_author_url', __( 'Author\'s Blogger URL:', 'wplbi' ) );


	}

	function add_settings_field( $field_name, $label ) {
		add_settings_field(
			$field_name,
			$label,
			array( $this, '_section_option' ),
			WPLBI::PAGE_NAME,
			WPLBI::SETTINGS_NAME,
			$field_name
		);
		register_setting(
			WPLBI::SETTINGS_NAME,
			$field_name,
			array( $this, '_sanitize' )
		);
	}

	/**
	 *
	 */
	function _sanitize( $value, $field_name ) {
		switch ( $field_name ) {
			case 'export_file_url':
			case 'blogger_author_url':
				$value = esc_url( $value );
				break;
			case 'wp_author_id':
				$value = intval( $value );
				break;
		}
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