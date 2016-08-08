<?php

class WPLBI_Post extends WPLBI_Base {

	/**
	 * @var int
	 */
	public $blog_id;

	/**
	 * @var int
	 */
	public $post_id;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $published;

	/**
	 * @var string
	 */
	public $updated;

	/**
	 * @var string
	 */
	public $content;

	/**
	 * @var string
	 */
	public $original_url;

	/**
	 * @var string
	 */
	public $link;

	/**
	 * @var string
	 */
	public $category;

	/**
	 * @var int
	 */
	public $author;

	/**
	 * @var int
	 */
	public $status = 0;

	/**
	 * @var bool
	 */
	public $is_valid = false;

	/**
	 * WPLBI_Post constructor.
	 *
	 * @param stdClass|string $post XML post to be parsed or pre-parsed JSON string
	 */
	function __construct( $post ) {

		do {

			if ( is_string( $post ) ) {
				if ( $post = json_decode( $post ) ) {
					$this->assign( $post );
				}
				break;
			}

			if ( ! is_object( $post ) ) {
				break;
			}

			if ( ! isset( $post->entry_id ) ) {
				break;
			}
			$this->post_id = $post->entry_id;

			if ( ! $this->is_valid_post( $post ) ) {
				break;
			}
			$this->is_valid = true;
			
			$this->assign( $post );

			$this->original_url = wplbi()->parse_url( $this->link );

			$this->category     = $this->parse_categories( $this->category );

			$this->author    = wplbi()->default_author();
			
		} while ( false );


	}

	function is_valid_post( $post ) {

		$is_valid_post = false;

		do {
			if ( $post->title instanceof stdClass ) {
				/*
				 * These are apparently deleted posts
				 */
				$this->status = 1;
				break;
			}
			if ( $post->content instanceof stdClass ) {
				/*
				 * These are apparently deleted posts
				 */
				$this->status = 2;
				$post->content = '';
				break;
			}

			$is_valid_post = true;

		} while ( false );

		return $is_valid_post;
	}

	function parse_categories( $categories ) {
		$url = null;

		if ( $categories instanceof stdClass ) {
			$categories = array( $categories );
		}

		$topics = array();
		foreach( $categories as $category ) {
			$category = (array)$category;
			$attributes = $category['@attributes'];
			switch ( $attributes->term ) {
				case 'http://schemas.google.com/blogger/2008/kind#post';
					continue;
				default:
					if ( 'http://www.blogger.com/atom/ns#' !== $attributes->scheme ) {
						continue;
					}
					$topics[] = $attributes->term;
					break;
			}
		}
		return $topics;
	}

	function insert_args() {
		return array(
			'post_author'           => $this->author,
			'post_date'             => wplbi()->format_date( $this->published ),
			'post_date_gmt'         => wplbi()->format_date_gmt( $this->published ),
			'post_modified'         => wplbi()->format_date( $this->updated ),
			'post_modified_gmt'     => wplbi()->format_date_gmt( $this->updated ),
			'post_content'          => $this->content,
			'post_content_filtered' => wp_kses_post( $this->content ),
			'post_title'            => $this->title,
			'post_status'           => $this->post_status( $this->content ),
			'post_type'             => 'post',
		);
	}

	function post_status( $content ) {
		return 50 <= strlen( strip_tags( $content ) ) ? 'publish' : 'draft';
	}

	function insert_post() {
		$post_id = wp_insert_post( $this->insert_args() );

		$categories = array();
		foreach( $this->category as $category ) {
			if ( ! get_category( $category ) ) {
				$categories[] = wp_insert_category( "cat_name={$category}" );
			}
		}
		if ( count( $categories ) ) {
			wp_set_post_categories( $post_id, $categories );
		}

		update_post_meta( $post_id, 'blogger_id', $this->ID );
		update_post_meta( $post_id, 'blogger_url', $this->original_url );

	}
	
}

