<?php
/**
 * class response_json
 */
class response_json {
	
	public function render($data) {
		return json_encode($data);
	}
}
