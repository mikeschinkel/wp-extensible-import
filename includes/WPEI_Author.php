<?php

/**
 * Class WPEI_Author
 */
class WPEI_Author {

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $url;

	/**
	 * @var string
	 */
	public $email;

	/**
	 * @param stdClass $author
	 */
	function __construct( $author ) {

		$this->name  = $author->name;
		$this->url   = $author->url;
		$this->email = 'noreply@blogger.com' !== $author->email
			? $author->email
			: null;
	}

}