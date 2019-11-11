<?php 
function my_date_parse_from_format($format, $date) {
	// reverse engineer date formats
	$keys = array(
			'Y' => array('year', '\d{4}'),
			'y' => array('year', '\d{2}'),
			'm' => array('month', '\d{2}'),
			'n' => array('month', '\d{1,2}'),
			'M' => array('month', '[A-Z][a-z]{3}'),
			'F' => array('month', '[A-Z][a-z]{2,8}'),
			'd' => array('day', '\d{2}'),
			'j' => array('day', '\d{1,2}'),
			'D' => array('day', '[A-Z][a-z]{2}'),
			'l' => array('day', '[A-Z][a-z]{6,9}'),
			'u' => array('hour', '\d{1,6}'),
			'h' => array('hour', '\d{2}'),
			'H' => array('hour', '\d{2}'),
			'g' => array('hour', '\d{1,2}'),
			'G' => array('hour', '\d{1,2}'),
			'i' => array('minute', '\d{2}'),
			's' => array('second', '\d{2}')
	);

	// convert format string to regex
	$regex = '';
	$chars = str_split($format);
	foreach ($chars AS $n => $char) {
		$lastChar = isset($chars[$n - 1]) ? $chars[$n - 1] : '';
		$skipCurrent = '\\' == $lastChar;
		if (!$skipCurrent && isset($keys[$char])) {
			$regex .= '(?P<' . $keys[$char][0] . '>' . $keys[$char][1] . ')';
		} else if ('\\' == $char) {
			$regex .= $char;
		} else {
			$regex .= preg_quote($char);
		}
	}

	$dt = array();
	$dt['error_count'] = 0;
	// now try to match it
	if (preg_match('#^' . $regex . '$#', $date, $dt)) {
		foreach ($dt AS $k => $v) {
			if (is_int($k)) {
				unset($dt[$k]);
			}
		}
		if (!checkdate($dt['month'], $dt['day'], $dt['year'])) {
			$dt['error_count'] = 1;
		}
	} else {
		$dt['error_count'] = 1;
	}
	$dt['errors'] = array();
	$dt['fraction'] = '';
	$dt['warning_count'] = 0;
	$dt['warnings'] = array();
	$dt['is_localtime'] = 0;
	$dt['zone_type'] = 0;
	$dt['zone'] = 0;
	$dt['is_dst'] = '';
	return $dt;
}

class MyDateTime {
		public $date;
	  
		public function __construct($date = null) {
			if(is_null($date)){
				$this->date = time();
			} else {
				
				$this->date = strtotime($date);
			}
		}
	  
		public function setTimeZone($timezone) {
			return;
		}
	  
		private function __getDate() {
			return date(DATE_ATOM, $this->date);
		}
	  
		public function modify($multiplier) {
			$this->date = strtotime($this->__getDate() . ' ' . $multiplier);
		}
	  
		public function format($format) {
			return date($format, $this->date);
		}
		
		public function formatLocale($format = null) {
			if($format == null){
				$format = "%c";
			}
			if(IS_PROD){
				return utf8_encode(strftime($format, $this->date));
				
			} else {
				return strftime($format, $this->date);
			}
		}
	  
		public function formatMySql(){
			return $this->format("Y-m-d H:i:s");
			
		}
		
		/**
		 * Parse une chaine de caractère d'un format passé en paramètre,
		 * retourne un DateTime.
		 * @param string $format
		 * @param string $time
		 * @return DateTime
		 */
		public static function createFromFormat ( $format , $time){
			$a = my_date_parse_from_format($format, $time);
			if(array_key_exists("error_count", $a) && $a["error_count"] >0){
				throw new Exception("La date {$time} n'a pas pue être intermprêtée avec le format {$format} : ".print_r($a, true));
			}
			$dateTime = new MyDateTime();
			$dateTime->date = strtotime("{$a["year"]}-{$a["month"]}-{$a["day"]} {$a["hour"]}:{$a["minute"]}:00");
			return $dateTime;
		}
	}
?>