<?php
/**
 * class response_xml
 */
class response_xml {
	
	private $xmlroot;
	
	public function set_xml_root($xmlroot){
		$this->xmlroot = $xmlroot;
	}
	
	public function render($data) {
		$data = array_flip($data);
		$xml = new SimpleXMLElement($this->xmlroot);
		array_walk_recursive($data, array ($xml, 'addChild'));
		return $xml->asXML();
	}
}
