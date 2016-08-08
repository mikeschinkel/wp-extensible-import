<?php

/**
 * Class WP_Large_Blogger_Import
 */
class WPLBI_Import {

	public $post_ids = array();

	function parse_blogger( $xml_file ) {

		$reader = new XMLReader;

		$reader->open( $xml_file );

		while ($reader->read() && $reader->name !== 'entry' );

		$rows_imported = $this->rows_imported();
		while ( 'entry' === $reader->name ) {
			if ( 0 === $rows_imported -- ) {
				break;
			}
			$reader->next( 'entry' );
		}

		while ( 'entry' === $reader->name ) {

			do {

				$doc = new DOMDocument;

				$node = simplexml_import_dom( $doc->importNode( $reader->expand(), true ) );

				/**
				 * This converts XML objects to simple, easy to debug stdClass objects.
				 */
				$entry = json_decode( json_encode( $node ) );

				if ( empty( $entry->id  ) ) break;

				list( $entry->blog_id, $entry->entry_id ) = wplbi()->parse_id( $entry->id );

				if ( empty( $entry->entry_id ) ) break;

				switch ( wplbi()->parse_kind( $entry->category ) ) {

					case null:
						break;

					case 'post':
						$entry = new WPLBI_Post( $entry );
						if ( ! $entry->is_valid ) {
							continue;
						}
						break;

					case 'comment':
						$entry = new WPLBI_Comment( $entry );
						break;

					default;
						break;

				}

				$this->record_entry( $entry );

			} while ( false );

			$reader->next('entry');
		}

	}

	/**
	 * @return int
	 */
	private function rows_imported() {
		global $wpdb;
		$table_name = wplbi()->import_table_name();
		return $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
	}

	/**
	 * @param WPLBI_Post|WPLBI_Comment $entry
	 */
	private function record_entry( $entry ) {
		global $wpdb;
		$entry_type = $entry instanceof WPLBI_Post ? 'post' : 'comment';
		$wpdb->insert( wplbi()->import_table_name(), array(
			'entry_type' => $entry_type,
			'blogger_id' => 'post' === $entry_type ? $entry->post_id :  $entry->comment_id,
			'json' => $entry->to_json(),
		) );

	}

	private function check_off_entry( $entry_id, $entry_type, $wp_id ) {
		global $wpdb;
		$wpdb->update( wplbi()->import_table_name(), array(
			'wp_entry_id' => $wp_id,
		), array(
			'blogger_id' => $entry_id,
			'entry_type' => $entry_type,
		) );

	}

	function clean_db() {
		global $wpdb;
		$inside_import  = get_option( WPLBI::INSIDE_IMPORT_KEY, date( DATE_ATOM ) );
		if ( ! $inside_import ) {
			foreach ( WPLBI::AFFECTED_TABLES as $pk_field => $table ) {
				$table_name = wplbi()->import_table_name();
				switch ( $table ) {
					case 'terms':
					case 'term_taxonomy':
						$field = rtrim( $table, 's' );
						$sql   = "DELETE FROM {$table_name} WHERE {$field}_id > 1;";
						break;
					default:
						$sql = "TRUNCATE TABLE {$table_name};";
				}
				$wpdb->query( $sql );
			}
			update_option( WPLBI::INSIDE_IMPORT_KEY, date( DATE_ATOM ) );
		}
	}

	/**
	 * @param int $import_id
	 * @param int $wp_id
	 */
	function update_import( $import_id, $wp_id ) {
		global $wpdb;
		$wpdb->update( wplbi()->import_table_name(), array(
			'wp_id' => $wp_id,
		), array(
			'import_id' => $import_id
		) );
	}

	/**
	 * @return WPLBI_DB_Cursor
	 */
	function get_db_cursor() {
		return new WPLBI_DB_Cursor( wplbi()->import_table_name(), array(
			'where'   => 'wp_id IS NULL',
			'orderby' => 'entry_type DESC, blogger_id ASC',
		));
	}

	/**
	 *
	 */
	function import_parsed() {
		$this->
		$this->clean_db();

		$db_cursor = $this->get_db_cursor();

		try {

			while ( $row = $db_cursor->next() ) {

				$entry = (object) json_decode( $row->json );

				switch ( $row->entry_type ) {
					case 'post':
						$post = new WPLBI_Post( $entry );
						$wp_id = $post->insert_post();
						break;

					case 'comment':
						$comment = new WPLBI_Comment( $entry );
						$wp_id = $comment->insert_comment();
						break;

				}

				if ( isset( $wp_id ) ) {
					$this->update_import( $entry->import_id, $wp_id );
				}

			}

			echo 'Done.';

		} catch ( Exception $e ) {

			echo 'ERROR: ' . $e->getMessage();
		}

	}

}
