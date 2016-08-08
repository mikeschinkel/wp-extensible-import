<?php

class WPLBI_Base {
	public $extra = array();
	function assign( $properties ) {
		if ( ! is_array( $properties ) ) {
			$properties = array();
		}
		foreach( $properties as $property => $value ) {
			if ( property_exists( $this, $property ) ) {
				$this->$property = $value;
			} else {
				$this->extra[ $property ] = $value;
			}
		}
	}
	/**
	 * @return array
	 */
	function to_json() {
		return json_encode( (array) $this );
	}

}