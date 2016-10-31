<?php

/**
 * Class WPEI_Admin
 */
class WPEI_Admin {

	/**
	 * @var WPEI_Settings
	 */
	private $_settings;

	/**
	 */
	function __construct() {

		add_action( 'admin_menu', array( $this, '_admin_menu' ) );
		add_action( 'admin_init', array( $this, '_admin_init' ) );
		//add_action( 'wp_check_filetype_and_ext', array( $this, '_wp_check_filetype_and_ext' ), 10, 4 );
		add_action( 'media_send_to_editor', array( $this, '_media_send_to_editor' ), 10, 2 );

		$this->_settings = WPEI_Settings::get_instance();

		add_action( 'wp_ajax_verify_import', array( $this, '_wp_ajax_verify_import' ) );
	}

	/**
	 *
	 */
	function _wp_ajax_import_content() {

		$import_url = esc_url_raw( $_POST[ 'import_url' ], array( 'http','https' ) );
		if ( trim( $this->_settings->import_file_url ) !== trim( $import_url ) ) {
			$result = array(
				'result' => 'error',
				'message' => __( 'Import file not yet verified', 'wpei' ),
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
		$importer = new WPEI_Import();
		return $importer->import_content( wpei()->import_filepath() );
	}


	/**
	 *
	 */
	function _wp_ajax_verify_import() {

		do {

			$result = array();

			$import_url = esc_url_raw( $_POST['import_url'], array( 'http', 'https' ) );

			/**
			 * @TODO use an importer factory here
			 */
			$importer = WPEI_Blogger_Importer::get_instance();

			$importer->set_settings( $_POST );

			$importer->set_import_url( $import_url );

			if ( is_null( $entry_count = $importer->entry_count() ) ) {

				break;

			}

			if ( is_null( $result = $importer->verify_result() ) ) {

				break;

			}

			$result = array_merge( array(
				'result'     => 'success',
				'entryCount' => $entry_count,
			), $result );

		} while ( false );

		if ( $importer->last_error ) {
			$result = array(
				'result'  => 'error',
				'message' => $importer->last_error,
			);
		}

		echo json_encode( $result );
		die();

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

			/**
			 * @TODO use an importer factory here
			 */
			$importer = WPEI_Blogger_Importer::get_instance();

			$filepath =  wpei()->get_filepath_from_url( $attachment->guid );

			if ( ! $importer->validate_import_file( $filepath ) ) {
				$html = $importer->last_error;
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
	function _admin_init() {

		add_settings_section(
			WPEI::SETTINGS_NAME,
			__( 'Import Parameters', 'wpei' ),
			array( $this, '_section_header' ),
			WPEI::PAGE_NAME
		);

		$this->add_settings_field( 'import_file_url',  __( 'URL of Import File:', 'wpei' ) );

		WPEI_Blogger_Importer::get_instance()->add_settings_fields( $this );


	}

	function add_settings_field( $field_name, $label ) {

		add_settings_field(
			$field_name,
			$label,
			array( $this, '_section_option' ),
			WPEI::PAGE_NAME,
			WPEI::SETTINGS_NAME,
			$field_name
		);
		register_setting(
			WPEI::SETTINGS_NAME,
			$field_name,
			array( $this, '_sanitize' )
		);
	}

	/**
	 *
	 */
	function _sanitize( $value, $field_name ) {
		switch ( $field_name ) {
			case 'import_file_url':
				$value = esc_url( $value );
				break;
		}
		$value = WPEI_Blogger_Importer::get_instance()->sanitize_fields( $value, $field_name );
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
			__( 'Extensible Import', 'wpei' ),
			__( 'Extensible Import', 'wpei' ),
			'manage_options',
			'extensible-import',
			array( $this, '_options_page' )
		);

	}

	/**
	 *
	 */
	function _options_page() {
		$this->_require_template( 'options-page' );
	}

	/**
	 * @return WPEI_Settings
	 */
	function settings(){
		if ( ! $this->_settings ) {
			$this->_settings = WPEI_Settings::get_instance();
		}
		return $this->_settings;
	}

	/**
	 * @var string $template
	 */
	private function _require_template( $template ) {

		do {
			$settings = $this->settings();

			$template = str_replace( array( ' ', '_' ), '-', $template );

			$importer = WPEI_Blogger_Importer::get_instance();

			$filepath = "{$importer->get_template_dir()}/{$template}.php";

			if ( ! is_file( $filepath ) ) {
				$filepath = dirname( __DIR__ ) . "/templates/{$template}.php";
			}
			if ( ! is_file( $filepath ) ) {
				echo "File {$filepath} not found.";
				break;
			}
			require( $filepath );

		} while ( false );

	}



}