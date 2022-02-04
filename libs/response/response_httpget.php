<?php
/**
 * class response_httpget
 */
class response_httpget {
	
	public function render($data) {
		return implode('|', $data);
	}
}
