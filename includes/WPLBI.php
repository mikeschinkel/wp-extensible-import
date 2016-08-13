<?php

/**
 * Class WPLBI
 */
class WPLBI {

	const SETTINGS_NAME = 'wplbi_settings';

	const PAGE_NAME = 'large-blogger-import';

	const TABLE_NAME = 'large_blogger_import';

	const INSIDE_IMPORT_KEY = 'wplbi_inside_import';

	const AFFECTED_TABLES = array(
		'posts',
		'postmeta',
		'comments',
		'commentmeta',
		'terms',
		'termmeta',
		'term_taxonomy',
		'term_relationships',
	);
	/**
	 * @var string
	 */
	public $plugin_dir;

	/**
	 * @var string
	 */
	private $plugin_url;

	/**
	 * @var WPLBI_Admin
	 */
	private $_admin;

	/**
	 * @var WPLBI_Settings
	 */
	private $_settings;

	/**
	 * WPLBI constructor.
	 *
	 * @param string $plugin_dir
	 */
	function __construct( $plugin_dir ) {

		$this->plugin_name = __( 'Large Blogger Import', 'wplbi' );
		$this->plugin_dir = $plugin_dir;
		$this->plugin_url = plugin_dir_url( "{$plugin_dir}/x" );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		if ( is_admin() ) {
			$this->_admin = new WPLBI_Admin();
			$this->_settings = new WPLBI_Settings();
		}

		add_action( 'admin_enqueue_scripts', array( $this, '_admin_enqueue_scripts' ) );
		add_action( 'admin_print_styles', array( $this, '_admin_print_styles' ) );
		add_action( 'load-tools_page_' . self::PAGE_NAME, array( $this, '_load_tools_page' ) );


	}

	/**
	 *
	 */
	function _load_tools_page() {

		do {
			if ( empty( $_POST[ self::SETTINGS_NAME ] ) ) {
				break;
			}

			if ( empty( $_POST[ '_wp_http_referer' ] ) ) {
				break;
			}

			$referer = 'wp-admin/tools.php?page=' . self::PAGE_NAME;

			if ( false === strpos( $_POST[ '_wp_http_referer' ], $referer ) ) {
				break;
			}

			if ( empty( $_POST[ 'action' ] ) ) {
				break;
			}

			if ( 'update' !== $_POST[ 'action' ] ) {
				break;
			}

			if ( empty( $_POST[ 'option_page' ] ) ) {
				break;
			}

			check_admin_referer( "{$_POST[ 'option_page' ]}-options" );

			$settings = new WPLBI_Settings( $_POST[ self::SETTINGS_NAME ] );

			$settings->save_settings();

		} while ( false );

	}

	function entry_count() {
		$reader      = new XMLReader;
		$entry_count = null;
		if ( is_file( $xml_file = $this->export_filepath() ) ) {
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
	 *
	 */
	function _admin_print_styles() {
		wp_enqueue_style('thickbox');
		wp_enqueue_style( 'wplbi-style', "{$this->plugin_url}/assets/css/wplbi-admin.css",array(),null );
	}

	/**
	 *
	 */
	function _admin_enqueue_scripts() {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_enqueue_script( 'wplbi-script', "{$this->plugin_url}/assets/js/wplbi-admin.js",array(),null,true );
		wp_localize_script( 'wplbi-script', 'WPLBI', array(
			'entry_count' => $this->entry_count()
		));

	}

	/**
	 * @param int $blogger_id
	 *
	 * @return int|WP_Error
	 */
	function make_new_page( $blogger_id ) {
		$page_id = wp_insert_post( array(
			'post_type' => 'page',
			// @TODO Do we need more than just post_type?
		));
		update_post_meta( $page_id, 'bloggger_id', $blogger_id );
		return $page_id;
	}

	function home_root_dir() {

		$upload_dir = wp_upload_dir();
		$regex = '#^' . preg_quote( home_url() ) . '(.*)$#';
		if ( ! preg_match( $regex, $upload_dir[ 'baseurl' ], $match ) ) {
			$root_dir = null;
		} else {
			$regex = '#^(.*)' . preg_quote( $match[ 1 ] ) . '$#';
			$root_dir = preg_replace( $regex, '$1', $upload_dir[ 'basedir' ] );
		}
		return $root_dir;

	}

	function get_local_filepath_from_url( $url ) {

		$regex = '#^' . preg_quote( home_url() ) . '(.*)$#';
		if ( ! preg_match( $regex,  $url , $match ) ) {
			$filepath = null;
		} else {
			$root_dir = $this->home_root_dir();
			$filepath = "{$root_dir}{$match[ 1 ]}";
		}
		return $filepath;

	}

	/**
	 * @return string
	 */
	function export_filepath() {
		return $this->get_local_filepath_from_url( $this->export_file_url() );
	}

	/**
	 * @return string
	 */
	function export_file_url() {
		return $this->_settings->export_file_url;
	}

	/**
	 * @return string
	 */
	function author_uri() {
		return $this->_settings->blogger_author_url;
	}

	/**
	 * @return int
	 */
	function default_author() {
		return $this->_settings->wp_author_id;
	}

	/**
	 * @params string $date
	 *
	 * @return bool|string
	 */
	function format_date( $date ) {
		return date( 'Y-m-d H:i:s', DateTime::createFromFormat( 'Y-m-d\TH:i:s.uP', $date )->getTimestamp() );
	}

	/**
	 * @params string $date
	 *
	 * @return bool|string
	 */
	function format_date_gmt( $date ) {
		return preg_replace( '#^(.+)T(.+)\.\d{3}-\d{2}:\d{2}$#', '$1 $2', $date );
	}

	/**
	 * @param array $links
	 *
	 * @return null
	 */
	function parse_url( $links ) {
		$url = null;
		foreach( $links as $link ) {
			$link = (array)$link;
			$attributes = $link['@attributes'];
			if ( ! isset( $attributes->type ) ) {
				continue;
			}
			if ( 'application/atom+xml' === $attributes->type ) {
				continue;
			}
			if ( ! isset( $attributes->rel ) ) {
				continue;
			}
			switch ( $attributes->rel ) {
				case 'replies':
				case 'related':
				case 'edit':
				case 'self':
					break;
				case 'alternate':
					$url = $attributes->href;
					break;
				default:
					break;
			}
			if ( ! is_null( $url ) ) {
				break;
			}

		}
		return $url;
	}

	/**
	 * @param string $id
	 *
	 * @return int[]
	 */
	function parse_id( $id ) {

		$regex = "tag:blogger.com,1999:blog-(\d+).post-(.+)";
		if ( ! preg_match( "#^{$regex}$#", $id, $match ) ) {
			$blog_id = $post_id = 0;
		} else {
			$blog_id = $match[1];
			$post_id = $match[2];
		}
		return array( $blog_id, $post_id );
	}

	/**
	 * @param array|string $categories
	 * @return string
	 */
	function parse_kind( $categories ) {

		$kind = null;
		if ( $categories instanceof stdClass ) {
			$categories = array( $categories );
		}
		foreach ( $categories as $category ) {
			$category = (array)$category;
			$attributes = $category['@attributes'];
			if ( ! isset( $attributes->term ) ) {
				continue;
			}
			$regex = '~^http://schemas.google.com/blogger/2008/kind#(.+)$~';
			if ( preg_match( $regex, $attributes->term, $match ) ) {
				$kind = $match[1];
				break;
			}
		}
		return $kind;
	}

	/**
	 *
	 */
	function activate() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $this->import_table_name();

		$sql = <<<SQL
CREATE TABLE {$table_name} (
  import_id int(11) NOT NULL,
  blogger_id varchar(24) NOT NULL,
  entry_type varchar(7) NOT NULL,
  json longtext NOT NULL,
  wp_id int(11) DEFAULT NULL,
  PRIMARY KEY (import_id),
  UNIQUE KEY wp_id (wp_id),
  KEY entry_type (entry_type),
  KEY blogger_id (blogger_id)
) {$charset_collate};
SQL;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	}

	/**
	 * @return string
	 */
	function import_table_name() {
		global $wpdb;
		return $wpdb->prefix . WPLBI::TABLE_NAME;
	}

	/**
	 * @param string $filepath
	 *
	 * @return bool|int
	 */
	function verify_atom_xml( $filepath ) {
		do {

			$is_atom = false;

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

		} while ( false );

		return $is_atom;

	}

	/**
	 * @return string
	 */
	function postback_url() {

		//return esc_url( site_url( '/wp-admin/options.php?page=' . self::SETTINGS_NAME ) );
		return esc_url( $_SERVER['REQUEST_URI'] );

	}

	/**
	 * Changes the accepted args of hooks that are added by other code.
	 *
	 * @param int $accepted_args
	 * @param string $filter
	 * @param callable $callback
	 * @param int $priority
	 *
	 * @return array
	 */
	function change_accepted_args( $accepted_args, $filter, $callback, $priority = 10 ) {
		global $wp_filter;

		foreach( $wp_filter[ $filter ][ $priority ] as $index => $function ) {

			if ( is_string( $function ) ) {
				if ( $callback === $function ) {
					$wp_filter[ $filter ][ $priority ][ $index ]['accepted_args'] = $accepted_args;
					break;
				}
			} else if ( is_array( $function ) ) {
				$function = $function['function'];
				if ( $callback[0] === $function[0] && $callback[1] === $function[1] ) {
					$wp_filter[ $filter ][ $priority ][ $index ]['accepted_args'] = $accepted_args;
					break;
				}
			}

		}

	}

	/**
	 * @return WPLBI_Admin
	 */
	function admin() {
		return $this->_admin;
	}



}

