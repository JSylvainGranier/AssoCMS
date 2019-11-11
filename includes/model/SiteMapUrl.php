<?php
class SiteMapUrl {
	public $loc = null;
	public $priority = null;
	/* @var $lastmod MyDateTime */
	public $lastmod = null;
	public function __construct($loc) {
		$this->loc = SITE_ROOT . "index.php" . $loc;
	}
	public function format() {
		$lastDt = "";
		if ($this->lastmod != null) {
			$lastDt = "\r\n\t<lastmod>{$this->lastmod->format("Y-m-d")}</lastmod>";
		}
		return "<url>\r\n\t<loc>{$this->loc}</loc>{$lastDt} \r\n</url>\r\n";
	}
}

?>