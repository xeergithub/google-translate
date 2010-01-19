<?php
// Author: Tomasz Kapusta 2009
// First version: 30.05.2009
// Version: 14.01.2010

// Licence: MIT
// http://de77.com
// http://code.google.com/p/google-translate/

class GoogleTranslate
{
	public $langIn	= 'pl';
	public $langOut	= 'en';
	
	private $cache = array();
	private $cacheDir = 'gt_cache/';
	                           
	//languages available on GoogleTranslate on 30.05.2009
	public $outLangs = array(
		"pl" => "Polski",	
		"en" => "English",
		
		"ar" => "العربية",
		"bg" => "Български",
		"ca" => "Català",		
		"cs" => "Čeština",				
		"da" => "Dansk",
		"de" => "Deutsch",
		"el" => "ελληνικά",
		"es" => "Español",		
		"et" => "Eesti",
		"fi" => "Suomi",
		"fr" => "Français",		
		"gl" => "Galego",		
		"hi" => "हिन्दी",		
		"hr" => "Hrvatski",
		"hu" => "Magyar",
		"id" => "Bahasa Indonesia",
		"it" => "Italiano",		
		"iw" => "עברית",
		"ja" => "日本語",
		"ko" => "한국어",				
		"lt" => "Lietuvių",
		"lv" => "Latviešu",
		"mt" => "Malti",
		"nl" => "Nederlands",
		"no" => "Norsk",
		"pt" => "Português",
		"ro" => "Români",		
		"ru" => "Русский",
		"sk" => "Slovenský",
		"sl" => "Slovenski",
		"sq" => "Shqipe",
		"sr" => "Српски",				
		"sv" => "Svenska",
		"th" => "ไทย",
		"tl" => "Philippine Wika",		
		"tr" => "Türkçe",
		"uk" => "Українська",		
		"vi" => "Tiếng Việt",										
		"zh-CN" => "繁体字",  //chinese simplified
		"zh-TW" => "繁體字" //chinese
	);
	
	//TODO: translate these
	public $inLangs = array( 
		"sq" => "albański",
		"en" => "angielski",
		"ar" => "arabski",
		"bg" => "bułgarski",
		"zh-CN" => "chiński",
		"hr" => "chorwacki",
		"cs" => "czeski",
		"da" => "duński",
		"et" => "estoński",
		"tl" => "filipiński",
		"fi" => "fiński",
		"fr" => "francuski",
		"gl" => "galicyjski",
		"el" => "grecki",
		"iw" => "hebrajski",
		"hi" => "hindi",
		"es" => "hiszpański",
		"nl" => "holenderski",
		"id" => "indonezyjski",
		"ja" => "japoński",
		"ca" => "kataloński",
		"ko" => "koreański",
		"lt" => "litewski",
		"lv" => "łotewski",
		"mt" => "maltański",
		"de" => "niemiecki",
		"no" => "norweski",
		"pl" => "polski",
		"pt" => "portugalski",
		"ru" => "rosyjski",
		"ro" => "rumuński",
		"sr" => "serbski",
		"sk" => "słowacki",
		"sl" => "słoweński",
		"sv" => "szwedzki",
		"th" => "tajski",
		"tr" => "turecki",
		"uk" => "ukraiński",
		"hu" => "węgierski",
		"vi" => "wietnamski",
		"it" => "włoski"
	);

	public function browserLang()
	{
		return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}
	
	public function codeToLang($code, $input = true)
	{
		if ($input) return $this->inLangs[$code];
		else		return $this->outLangs[$code];
	}
	
	public function langToCode($lang, $input = true)
	{
		if ($input) return array_search($lang, $this->inLangs);
		else		return array_search($lang, $this->outLangs);
	} 
	
	private function loadCache()
	{
		chdir(__DIR__);
		if (!isset($this->cache[$this->langIn][$this->langOut]))
		{
			$cacheFile = $this->cacheDir . $this->langIn . '_' . $this->langOut . '.gtc'; 
			if (file_exists($cacheFile))
			{ 
				$data = unserialize(file_get_contents($cacheFile));
				$this->cache[$this->langIn][$this->langOut] = $data;
			}
			else
			{
				$this->cache[$this->langIn][$this->langOut] = array();
			}
		}
	}
	
	private function saveCache()
	{
		chdir(__DIR__);
		$data = serialize($this->cache[$this->langIn][$this->langOut]);
		$cacheFile = $this->cacheDir . $this->langIn . '_' . $this->langOut . '.gtc'; 
		file_put_contents($cacheFile, $data);			
	}
	
	private function getCached($text)
	{
		$this->loadCache();
		
		if (isset($this->cache[$this->langIn][$this->langOut][$text]))
		{
			return $this->cache[$this->langIn][$this->langOut][$text];
		}
		return false;
	}
		
	public function translate($text)
	{
		if ($this->langIn == $this->langOut) return $text;
	
		if ($res = $this->getCached($text))
		{
			return $res;
		}
			
		$url = 'http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q=' .
				urlencode($text) . 
				'&langpair=' . $this->langIn . '%7C' .
				$this->langOut;
				
		$json_data = file_get_contents($url);
		
		$j = json_decode($json_data);
		   
		if (isset($j->responseStatus) and $j->responseStatus == 200)
		{
			$t = $j->responseData->translatedText;
			$this->cache[$this->langIn][$this->langOut][$text] = $t;
			return $t;
		}
		else return false;
	}	
	
	public function __destruct()
	{
		$this->saveCache();
	}
}


// Simple JSON decoder
// in case json_decode is not available..
if ( !function_exists('json_decode') )
{
	function json_decode($json) 
	{  		
		$comment = false;
		$out = '$x=';
		
		for ($i=0; $i<strlen($json); $i++)
		{
			if (!$comment)
			{
				if ($json[$i] == '{')		$out .= ' array(';
				else if ($json[$i] == '}')	$out .= ')';
				else if ($json[$i] == ':')	$out .= '=>';
				else 						$out .= $json[$i];			
			}
			else $out .= $json[$i];
			if ($json[$i] == '"')	$comment = !$comment;
		}
		eval($out . ';');
		return $x;
	}  
}
?>