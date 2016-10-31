<?php

class WPEI_Comment extends WPEI_Base {

	public $blogger_id;
	public $blog_id;
	public $post_id;
	public $comment_id;
	public $published;
	public $content;
	public $author;
	public $original_url;

	/**
	 * WPEI_Comment constructor.
	 *
	 * @param stdClass|string $comment XML post to be parsed or pre-parsed JSON string
	 */
	function __construct( $comment ) {

		do{

			if ( is_string( $comment ) ) {
				if ( $comment = json_decode( $comment ) ) {
					$this->assign( $comment );
				}
				break;
			}

			if ( ! is_object( $comment ) ) {
				break;
			}

			$this->comment_id   = $comment->entry_id;

			$this->author    = new WPEI_Author( $comment->author );

			$this->original_url = wpei()->parse_url( $comment->link );

			$this->_parse_ids( $comment->link );

		} while ( false );

	}

	/**
	 * @return int
	 */
	function page_id() {
		$page_id = 0;
		// @todo Look up page by meta_key
		return $page_id;
	}

	/**
	 * @param array $links
	 */
	private function _parse_ids( $links ) {
		$comment_ids = null;

		if ( $links instanceof stdClass ) {
			$links = array( $links );
		}

		foreach( $links as $link ) {
			$link = (array) $link;
			$attributes = $link['@attributes'];
			if ( ! isset( $attributes->type ) ) {
				continue;
			}
			switch ( $attributes->type ) {
				case 'application/atom+xml':
					$regex = '#^https://www.blogger.com/feeds/([^/]+)/([^/]+)/comments/default/(.+)$#';
					if ( preg_match( $regex, $attributes->href, $matches ) ) {
						$this->comment_id = $matches[3];
						$this->post_id    = $matches[2];
						$this->blog_id    = $matches[1];
					}
					break;
				default:
					break;
			}
			if ( isset( $this->blog_id ) ) {
				break;
			}

		}
	}

	/**
	 * @return false|int
	 */
	function insert() {

		$comment_id = wp_insert_comment( $this->insert_args() );


		update_comment_meta( $comment_id, 'blogger_id', $this->comment_id );
		update_comment_meta( $comment_id, 'blogger_post_id', $this->post_id );
		update_comment_meta( $comment_id, 'blogger_url', $this->original_url );
		return $comment_id;

	}

	/**
	 * @return array
	 */
	function insert_args() {
		return array(
			'comment_post_ID'      => $this->post_id,
			'comment_agent'        => 'Unknown [Imported from Blogger]',
			'comment_approved'     => '1',
			'comment_author'       => $this->author->name,
			'comment_author_email' => $this->author->email,
			'comment_author_IP'    => '0.0.0.0 [Imported from Blogger]',
			'comment_author_url'   => $this->author->url,
			'comment_content'      => $this->content,
			'comment_date'         => wpei()->format_date( $this->published ),
			'comment_date_gmt'     => wpei()->format_date_gmt( $this->published ),
			'comment_type'         => 'comment',
			'user_id'              => wpei()->blogger_author_uri() === $this->author->url
				? wpei()->default_author()
				: 0,
		);
	}

	/**
	 * @return int
	 */
	function insert_comment() {

		if ( empty( $this->blogger_id ) ) {
			echo sprintf( 'No post found matching %s.',  $this->blogger_id ) . "<br>\n";
			$page_id = $this->page_id();
			if ( 0 === $page_id ) {
				$page_id = wpei()->make_new_page( $this->blogger_id );
			}
			$this->post_id = $page_id;
		}
		return $this->insert();
	}

}