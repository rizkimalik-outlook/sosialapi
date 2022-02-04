<?php
interface Base_View_Render_Interface {
	public function __construct($option);
	public function assign($key, $value);
	public function isCached($template);
	public function output($template);
}
