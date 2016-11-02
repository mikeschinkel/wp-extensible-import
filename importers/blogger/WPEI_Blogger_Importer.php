<?php

/**
 *
 */
class WPEI_Blogger_Importer extends WPEI_Importer_Base {

	static function IMPORTER_NAME() {
		return __( 'Blogger Importer', 'wpei' );
	}

	/**
	 * @return self
	 */
	static function get_instance( $class_name = null ) {
		return parent::get_instance( __CLASS__ );
	}

	/**
	 *
	 */
	function initialize() {

		add_action( 'upload_mimes', array( $this, '_upload_mimes' ) );

	}

	/**
	 * @param array $types
	 * @return array
	 */
	function _upload_mimes( $types ) {
		$types['xml' ] = 'application/xml';
		return $types;
	}

	/**
	 * @param WPEI_Admin $admin
	 */
	function add_settings_fields( $admin ) {
		switch ( $admin->wizard_page_name ) {
			case 'info':
				$admin->add_settings_field( 'wp_author_id', __( 'Author for Posts:', 'wpei' ) );
				break;
			default:
		}
	}

	/**
	 * @return array
	 */
	function verify_result() {

		$blogger_blog_url = $this->blogger_blog_url();
		$blogger_author_url = $this->blogger_author_url();

		$this->settings->update_settings( array(
			'entry_count'        => $this->entry_count(),
			'blogger_blog_url'   => $blogger_blog_url,
			'blogger_author_url' => $blogger_author_url,
		));

		$result = array(
			'result'           => 'success',
			'bloggerBlogUrl'   => $blogger_blog_url,
			'bloggerAuthorUrl' => $blogger_author_url,
		);

		return $result;

	}

	/**
	 * @param string|null $filepath
	 * @return bool|int
	 */
	function validate_import_file( $filepath ) {
		do {

			$is_atom = false;

			$this->last_error = __( 'Not a valid Atom file' );

			if ( ! is_file( $filepath ) ) {
				break;
			}

			if ( false === (  $handle = fopen( $filepath, 'r' ) ) ) {
				break;
			}

			if ( false === ( $xml_head = fgets( $handle, 2048 ) ) ) {
				break;
			}

			fclose( $handle );

			if ( 0 !== strpos( $xml_head, "<?xml version='1.0' encoding='UTF-8'?>" ) ) {
				break;
			}

			$is_atom  = preg_match( "#\<feed xmlns='http://www.w3.org/2005/Atom'#", $xml_head );

			$this->last_error = false;

		} while ( false );

		return $is_atom;

	}

	/**
	 * @return int|null
	 */
	function entry_count() {
		$reader      = new XMLReader;
		$entry_count = null;
		if ( ! is_file( $xml_file = $this->import_filepath ) ) {
			$this->last_error = __( 'Not a valid Blogger file' );
		} else {
			$this->last_error = false;
			$entry_count = 0;
			$reader->open( $xml_file );
			while ( $reader->read() && $reader->name !== 'entry' ) {
				;
			}
			while ( 'entry' === $reader->name ) {
				$entry_count ++;
				$reader->next( 'entry' );
			}
		}
		return $entry_count;
	}

	/**
	 * @param mixed $value
	 * @param string $field_name
	 * @return mixed
	 */
	function sanitize_fields( $value, $field_name )  {
		switch ( $field_name ) {
			case 'blogger_author_url':
				$value = esc_url( $value );
				break;
			case 'wp_author_id':
				$value = intval( $value );
				break;
		}
		return parent::sanitize_fields( $value, $field_name );
	}

	/**
	 * @return string|null
	 */
	function blogger_blog_url() {
		$reader = new XMLReader;
		$blog_url = null;
		if ( is_file( $xml_file = $this->import_filepath ) ) {
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
		if ( is_file( $xml_file = $this->import_filepath ) ) {
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
		if ( is_file( $xml_file = $this->import_filepath ) ) {
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
}