<?php

/**
 * plistMaker class - Version 1.0
 * 2008 by Dennis Rochel
 * 
 * DESCRIPTION:
 * Class to parse a predefined PHP-ARRAY into a OSX-plist format
 * 
 * 
 * $plist = new plistMaker($resultArray);
 * echo $plist->getPlist();
 * 
 * SPECIAL dictionary KEY:
 * 
 * dict_{i}
 */
class PHPplistMaker {
	private $nl = "\r\n";
	private $content = FALSE;
	private $dictIdentifier = 'dict_';
	private $useUTF8encoding;
	
	public function __construct($a = null, $useUTF8encoding = TRUE) {
		if ($a) {
			$this->useUTF8encoding = $useUTF8encoding;
			$this->setArray($a);
		}
	}
	
	
	public function setArray($a) {
		if (!is_array($a)) {
			throw new Exception('parameter must be an array');
		}
	
		$this->content = '<dict>' . $this->nl.$this->generateContent($a) . '</dict>' . $this->nl;
		
		return $this;
	}
	
	
	public function getPlist() {
		if (!$this->content){
				throw new Exception('no data array was set');
		}
	
		$out = $this->getHeader();
		$out .= '<plist version="1.0">' . $this->nl;
		$out .= $this->content;
		$out .= '</plist>';
	
		return $out;
	}
	
	
	/**
	 * Returns the plist-specific header
	 *
	 * @access private
	 * @return void
	 */
	private function getHeader() {
		$plist = '<?xml version="1.0" encoding="UTF-8"?>' . $this->nl;
		$plist .= '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">' . $this->nl;
		return $plist;
	}
	
	
	/**
	 * Checks if $var is an associative array
	 * ToDo: can be removed?!
	 *
	 * @access private
	 * @param array $check
	 * @return boolean
	 */
	private function is_assoc($arr) {
        return is_array($arr) && array_diff_key($arr, array_keys(array_keys($arr)));	
   }
	
	
	/**
	 * This function generates the content-part with the help
	 * of the PHP-Array.
	 *
	 * @access private
	 * @param array $a
	 * @param boolean $insideArray (default: FALSE)
	 * @return void
	 */
	private function generateContent($a, $insideArray = FALSE) {
	
		$list = '';
		
		foreach ($a as $key => $v) {
			$key = utf8_encode($key);
	
			if ((is_string($key)) && (strstr($key, $this->dictIdentifier))) {
			
				if (!$insideArray) {
					$list .= '<key>' . str_replace($this->dictIdentifier, '', $key) . '</key>' . $this->nl;
				}
				
				$list .= '<dict>' . $this->nl;
				$list .= $this->generateContent($v);
				$list .= '</dict>' . $this->nl;
			} elseif(is_array($v)) {
			
				$list .= '<key>' . $key . '</key>' . $this->nl;
				$list .= '<array>' . $this->nl;
				$list .= $this->generateContent($v, TRUE);
				$list .= '</array>' . $this->nl;			
			} else {
				
				$v = str_replace('&', '&amp;', $v);
				
				if (!$insideArray) {
					$list .= '<key>' . $key . '</key>' . $this->nl;
				}
				
				if ($this->useUTF8encoding) {
					$v = utf8_encode($v);
				}
				
				$list .= '<string>' . $v . '</string>' . $this->nl;
			}
		}
		return $list;
	}
}