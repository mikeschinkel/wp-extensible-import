<?php

/**
 * Class WP_Large_Blogger_Import
 */
class WPLBI_Import {

	public $post_ids = array();

	/**
	 * @param $xml_file
	 */
	function import_content( $xml_file ) {
		$result = array(
			'post'    => (object) array(
				'imported'    => 0,
				'post_errors' => 0,
			),
			'comment' => (object) array(
				'imported'    => 0,
				'post_errors' => 0,
			),
		);

		$reader = new XMLReader;

		$reader->open( $xml_file );

		while ($reader->read() && $reader->name !== 'entry' );

		$entries_imported = $this->entries_imported();
		$result[ 'post' ]->imported = $entries_imported['post'];
		$result[ 'comment' ]->imported = $entries_imported['comment'];
		$entries_imported = intval( $entries_imported['post'] ) + intval( $entries_imported['comment'] );

		while ( 'entry' === $reader->name ) {
			if ( 0 === $entries_imported-- ) {
				break;
			}
			$reader->next( 'entry' );
		}
		$counter = 0;
		while ( 'entry' === $reader->name && $counter < 1000 ) {

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

				switch ( $kind = wplbi()->parse_kind( $entry->category ) ) {

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

				if ( ! is_wp_error( $item_id = $this->import_entry( $entry ) ) ) {
					$result[ $kind ]->imported++;
				} else {
					$result[ $kind ]->errors++;
				};

			} while ( false );

			$reader->next('entry');
		}

		return $result;

	}

	/**
	 * @return int
	 */
	private function entries_imported() {
		global $wpdb;
		$table_name = wplbi()->import_table_name();
		$results = $wpdb->get_results( "SELECT entry_type, type_count AS COUNT(*) FROM {$table_name} GROUP BY entry_type" );
		$entries_imported = array();
		foreach( $results as $result ) {
			$entries_imported[ $result->entry_type ] = $results->type_count;
		}
		return $entries_imported;

	}

	/**
	 * @param WPLBI_Post|WPLBI_Comment $entry
	 * @return int
	 */
	private function import_entry( $entry ) {
		global $wpdb;
		$entry_type = $entry instanceof WPLBI_Post ? 'post' : 'comment';
		return $wpdb->insert( wplbi()->import_table_name(), array(
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
