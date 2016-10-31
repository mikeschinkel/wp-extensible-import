<?php

/**
 * Class WPEI_DB_Cursor
 */
class WPEI_DB_Cursor extends WPEI_Base{

	public $table_name;

	public $orderby = 'import_id ASC';

	public $where = '1=1';

	public $rows;

	public $cursor = 0;

	public $cursor_size = 100;

	/**
	 * WPEI_DB_Cursor constructor.
	 *
	 * @param string $table_name
	 * @param array $args {
	 *      @type string $orderby
	 *      @type string $where
	 * }
	 */
	function __construct( $table_name, $args ) {
		$this->table_name = $table_name;
		$this->assign( $args );
	}

	/**
	 * @return object|bool
	 */
	function current() {

		if ( ! isset( $this->rows ) ) {
			$this->load_rows();
		}

		return isset( $this->rows[ $this->cursor ] )
			? $this->rows[ $this->cursor ]
			: false;
	}

	/**
	 * @return object|bool
	 */
	function next() {

		if ( $this->cursor++ === count( $this->rows ) ) {
			$this->load_rows();
			$this->cursor = 0;

		}

		return $this->current();
	}

	/**
	 *
	 */
	function load_rows() {
		global $wpdb;
		$sql = "SELECT * FROM {$this->table_name} WHERE {$this->where}  ORDER BY {$this->orderby} LIMIT {$this->cursor_size};";
		$this->rows = $wpdb->get_results( $sql );
	}

}