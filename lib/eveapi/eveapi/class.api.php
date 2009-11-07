<?php
/**************************************************************************
	PHP Api Lib, v0.23, 2008-09-30

	Portions Copyright (C) 2007  Kw4h
	Portions Copyright (C) 2008 Pavol Kovalik
	Portions Copyright (C) 2008 Gordon Pettey
	Portions Copyright (C) 2008 Thorsten Behrens
	Portions Copyright (C) 2008 Dustin Tinklin

	This file is part of PHP Api Lib.

	PHP Api Lib is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	PHP Api Lib is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with PHP Api Lib.  If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/

class Api
{
	private $apikey = null;
	private $userid = null;
	private $charid = null;
	private $apisite = "api.eve-online.com";
	private $cachedir = './xmlcache';
	private $debug = false;
	private $msg = array();
	private $usecache = true;
	private $cachestatus = false;
	private $timetolerance = 5; // minutes to wait after cachedUntil, to allow for the server's time being fast

	public function setCredentials($userid, $apikey, $charid = null)
	{
		if (empty($userid) || empty ($apikey))
		{
			$this->addMsg("Error","setCredentials: userid and apikey must not be empty");
			return false;
		}

		if (!is_numeric($userid))
		{
			$this->addMsg("Error","setCredentials: userid must be a numeric value");
			return true;
		}
		
		if (!is_string($apikey))
		{
			$this->addMsg("Error","setCredentials: apikey must be a string value");
			return false;
		}
		
		if ($charid != null && !is_numeric($charid))
		{
			$this->addMsg("Error","setCredentials: charid must be a numeric value");
			return false;
		}
	
		if (!empty($userid) && !empty($apikey) && is_numeric($userid) && is_string($apikey))
		{
			$this->userid = $userid;
			$this->apikey = $apikey;
		}
		
		if (!empty($charid) && is_numeric($charid))
		{
			$this->charid = $charid;
		} 
		else 
		{
			$this->charid = null;
		}
		
		return true;
	}
	
	public function getCredentials()
	{
		$result = array();
		$result['userid'] = $this->userid;
		$result['apikey'] = $this->apikey;
		$result['charid'] = $this->charid;
		
		return $result;
	}
	
	public function setDebug($bool)
	{
		if (is_bool($bool))
		{
			$this->debug = $bool;
			return true;
		}
		else
		{
			$this->addMsg("Error","debug: parameter must be present and boolean");
			return false;
		}
	}
	
	public function debug($bool)
	{ // legacy name of setDebug
		$this->setDebug($bool);
	}
	
	public function getDebug()
	{
		return $this->debug;
	}

	public function setUseCache($bool)
	{
		if (is_bool($bool))
		{
			$this->usecache = $bool;
			return true;
		}
		else
		{
			$this->addMsg("Error","cache: parameter must be present and boolean");
			return false;
		}
	}
	
	public function cache($bool)
	{ // legacy name of setUseCache
		$this->setUseCache($bool);
	}
	
	public function getUseCache()
	{
		return $this->usecache;
	}

	public function setCacheDir($dir)
	{
		if (is_string($dir))
		{
			$this->cachedir = $dir;
			return true;
		}
		else
		{
			$this->addMsg("Error","setCacheDir: parameter must be present and a string");
			return false;
		}
	}
	
	public function getCacheDir()
	{
		return $this->cachedir;
	}

	private function setCacheStatus($bool)
	{
		if (is_bool($bool))
		{
			$this->cachestatus = $bool;
			return true;
		}
		else
		{
			$this->addMsg("Error","setCacheStatus: parameter must be present and boolean");
			return false;
		}

	}

	public function getCacheStatus()
	{
		return $this->cachestatus;	
	}

	public function setTimeTolerance($tolerance)
	{
		if (is_int($tolerance))
		{
			$this->timetolerance = $tolerance;
			return true;
		} 
		else 
		{
			$this->addMsg("Error","setTimeTolerance: parameter must be present and an integer");
			return false;
		}

	}

	public function getTimeTolerance()
	{
		return $this->timetolerance;
	}
	
	public function setApiSite($site)
	{
		if (is_string($site))
		{
			$this->apisite = $site;
			return true;
		} 
		else 
		{
			$this->addMsg("Error","setApiSite: parameter must be present and a string");
			return false;
		}
	}
	
	public function getApiSite()
	{
		return $this->apisite;
	}
	
	// add error message - both params are strings and are formatted as: "$type: $message"
	private function addMsg($type, $message)
	{
		if (!empty($type) && !empty($message))
		{
			$index = count($this->msg);
			
			$this->msg[$index]['type'] = $type;
			$this->msg[$index]['msg'] = $message;
			return 1;
		}
		else
		{
			$this->addMsg("Error","addMsg: type and message must not be empty");
			return 0;
		}
	}

	public function printErrors()
	{
		foreach ($this->msg as $msg)
		{
			echo ("<b>" . $msg['type'] . "</b>: " . $msg['msg'] . "</br>\n");
		}
	}
	
	public function debugPopup()
	{
		$message = "";
		foreach ($this->msg as $msg)
		{
			$message .= "<b>" . $msg['type'] . "</b>: " . $msg['msg'] . "<br />";
		}

		$txt  = '<div style="font-size: small;">';
		$txt .= '<pre>'.strtr($message, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/')).'</pre>';
		$txt .= '</div>';
		
		$js = '
			<script type="text/javascript">
		//    <![CDATA[
				_console = window.open("","Debug Console","width=680,height=600,resizable,scrollbars=yes");
				_console.document.write(\''.$txt.'\');
				_console.document.close();
		//      ]]>
			</script>
		';
		echo $js;
	}
	
	/**********************
		Retrieve an XML File
		$path	path relative to the $apisite url
		$timeout	amount of time to keep the cached data before re-requesting it from the API, in minutes
		$cachePath	optional array of string values . These can be indizes into $params, or arbitrary strings, 
				and will be used to build the relative path to the cache file
		$params	optional array of paramaters (exclude apikey and userid, and charid)
				$params['characterID'] = 123456789;
		$binary	optional boolean - if true, treat the returned data as binary, not XML
	***********************/
	public function retrieveXml($path, $timeout = null, $cachePath = null, $params = null, $binary = false)
	{
		$this->setCacheStatus(false);
		if ($cachePath != null && !is_array($cachePath))
		{			
			$this->addMsg("Error","retrieveXml: Non-array value of cachePath param, reverting to default value");
			$cachePath = null;
		}
		
		if ($params != null && !is_array($params))
		{			
			$this->addMsg("Error","retrieveXml: Non-array value of params param, reverting to default value");
			$params = null;
		}

		if (!empty($path))
		{
			if (!is_array($params))
			{
				$params = array();
			}

			if ($this->userid != null && $this->apikey != null)
			{
				$params['userID'] = $this->userid;
				$params['apiKey'] = $this->apikey;
			}
			
			if ($this->charid != null)
			{
				$params['characterID'] = $this->charid;
			}
			
			// Save ourselves some calls and figure caching status out once for this function
			if ($this->usecache)
			{
				$iscached = $this->isCached($path,$params,$cachePath,$timeout,$binary);
			}
			// continue when not cached
			if (!$this->usecache || !$iscached)
			{
				// Presumably, if it's not set to '&', they might have had a reason for that - be a good citizen
				$sep = ini_get('arg_separator.output');
				// Necessary so that http_build_query does not spaz and give us '&amp;' as a separator on certain hosting providers
				ini_set('arg_separator.output','&');
				// poststring
				if (count($params) > 0)
				{
					$poststring = http_build_query($params); // which has been forced to use '&' by ini_set, at the end of this file
				}
				else
				{
					$poststring = "";
				}
				// And set it back to whatever sensical or non-sensical value it was in the 1st place
				ini_set('arg_separator.output',$sep);

				// open connection to the api
				// Note some free PHP5 servers block fsockopen() - in that case, find a different hosting provider, please
				$fp = fsockopen($this->apisite, 80, $errno, $errstr, 30);

				if (!$fp)
				{
					$this->addMsg("Error", "retrieveXml: Could not connect to API URL at $this->apisite, error $errstr ($errno)");
					// If we do have this in cache regardless of freshness, return it
					if ($this->usecache && $this->isCached($path,$params,$cachePath,0,$binary))
					{
						return $this->loadCache($path, $params, $cachePath,$binary);
					}
				}
				else
				{
					// request the xml
					fputs ($fp, "POST " . $path . " HTTP/1.0\r\n");
					fputs ($fp, "Host: " . $this->apisite . "\r\n");
					fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
					fputs ($fp, "User-Agent: PHPApi\r\n");
					fputs ($fp, "Content-Length: " . strlen($poststring) . "\r\n");
					fputs ($fp, "Connection: close\r\n\r\n");
					if (strlen($poststring) > 0)
					{
						fputs ($fp, $poststring."\r\n");
					}
					
					// retrieve contents
					$contents = "";
					while (!feof($fp))
					{
						$contents .= fgets($fp);
					}
					
					// close connection
					fclose($fp);
					
					$start = strpos($contents, "\r\n\r\n");
					if ($start !== FALSE)
					{
						$contents = substr($contents, $start + strlen("\r\n\r\n"));
			
						if (!$binary)
						{
							// check if there's an error or not
							$xml = new SimpleXMLElement($contents);
							
							$error = (string) $xml->error;
							if (!empty($error))
							{
								$this->addMsg("API Error", $error);

								// If we do have this in cache regardless of freshness, return it
								if ($this->usecache && $this->isCached($path, $params, $cachePath, 0))
								{
									return $this->loadCache($path, $params, $cachePath);
								}
								return null;
							}
							unset ($xml); // reduce memory footprint
						}

						if ($this->usecache && !$iscached)
						{
							$this->store($contents, $path, $params, $cachePath,$binary);
						}
						return $contents;
					}
					$this->addMsg("Error", "retrieveXml: Could not parse contents");
					return null;
				}
			}
			else // We are to use a cache and the api results are still valid in cache
			{
				return $this->loadCache($path, $params, $cachePath,$binary);
			}
		}
		else
		{
			$this->addMsg("Error", "retrieveXml: path is empty");
		}
		return null; //empty path, calling error
	}
	
	private function getCacheFile($path, $params, $cachePath, $binary = false)
	{
		$realpath = $this->cachedir;
		
		if ($cachePath != null)
		{
			if (!$binary)
			{
				foreach ($cachePath as $segment)
				{
					if (isset($params[$segment]))
					{
						$realpath .= '/'.$params[$segment];
					}
					else
					{
						$realpath .= '/'.$segment;
					}
				}
			}
			else // for binary files, we construct a file name, not a path name. Really only valid for the JPEGs I'm doing - this logic can always be changed if CCP adds more binary stuff. Which I doubt.
			{
				$realpath .= '/';
				foreach ($cachePath as $segment)
				{
					if (isset($params[$segment]))
					{
						$realpath .= $params[$segment];
					}
					else
					{
						$realpath .= $segment;
					}
				}
			}
		}
		
		if (!$binary)
		{
			$realpath .= $path;
		}
				
		return $realpath;
	}
	
	private function store($contents, $path, $params, $cachePath, $binary = false)
	{
		$file = $this->getCacheFile($path, $params, $cachePath, $binary);

		if (!file_exists(dirname($file)))
		{
			mkdir(dirname($file), 0777, true);
		}
		
		$fp = fopen($file, "w");
		
		if ($fp)
		{
			fwrite($fp, $contents);
			fclose($fp);
			
			$this->addMsg("Info","store: Created cache file:" . $file);
		}
		else
		{
			$this->addMsg("Error", "store: Could not open cache file for writing: " . $file);
		}
	}
	
	private function loadCache($path, $params, $cachePath, $binary = false)
	{
		// its cached, open it and use it
		$file = $this->getCacheFile($path, $params, $cachePath, $binary);
		
		$fp = fopen($file, "r");
		if ($fp)
		{
			$contents = fread($fp, filesize($file));
			fclose($fp);
			$this->setCacheStatus(true);
			$this->addMsg("Info","loadCache: Fetched cache file:" . $file);
		}
		else
		{
			$this->addMsg("Error", "loadCache: Could not open cache file for reading: " . $file);
		}

		return $contents;
	}
	
	// checking if the cache expired or not based on TQ time
	// $path - The API path as given in the API URL, including the actual filename
	// $params - optional array of parameters for the API URL
	// $cachePath - optional array of strings or indizes into params to build the relative path to the cache file on disk
	// $timeout - minutes to keep the cache. Special value NULL means to use CCP's cachedUntil hint, and 0 means to just check for the file, don't check for freshness
	private function isCached($path, $params, $cachePath, $timeout, $binary = false)
	{
		$file = $this->getCacheFile($path, $params, $cachePath, $binary);

		if (file_exists($file) && filesize($file) > 0) // Added filesize to catch error on 0 length files. 
		{
			if ($timeout === 0) // timeout is 0, not NULL - magic value to indicate we want to know whether the file is there, never mind the caching time
			{
				return true;
			}

			$fp = fopen($file, "r");
			
			if ($fp)
			{
				$contents = fread($fp, filesize($file));
				fclose($fp);
				
				// check cache
				$xml = new SimpleXMLElement($contents);
				
				$cachetime = (string) $xml->currentTime;
				$time = strtotime($cachetime);
				
				$expirytime = (string) $xml->cachedUntil;
				$until = strtotime($expirytime);
				
				unset($contents); // Free us some memory
				unset($xml); // and free memory for this one, too

				if ($time === $until) // currentTime and cachedUntil are equal - CCP's way of telling us "don't cache"
				{
					return false;
				}
				
				// get GMT time
				$timenow = time();
				$now = $timenow - date('Z', $timenow);

// Uncomment in case we need some deep-dive debug. There's a TODO here - have levels of debug				
//				if ($this->debug) {
//				   $this->addMsg("Info","Got this at ".$time.", keep it until ".$until.", it is now ".$now);
//				   $this->addMsg("Info","Formatted: Got this at ".strftime("%b %d %Y %X",$time).", keep it until ".strftime("%b %d %Y %X",$until).", it is now ".strftime("%b %d %Y %X",$now));
//				}

				if ($timeout === NULL) // no explicit timeout given, use the cachedUntil time CCP gave us
				{
					if (($until + $this->timetolerance * 60) < $now) // time to fetch again, with some minutes leeway
					{
						return false;
					}
				} 
				else 
				{
					// if now is $timeout minutes ahead of the cached time, pretend this file is not cached
					$minutes = ($timeout + $this->timetolerance) * 60;
					if ($now >= $time + $minutes)
					{
						return false;
					}
				}

				return true; // default fall-through - cache is still valid
			}
			else
			{
				$this->addMsg("Error", "isCached: Could not open cache file for reading: " . $file);
				return false;
			}
		}
		else
		{
			$this->addMsg("Info", "isCached: Cache file does not (yet?) exist: " . $file);
			return false;
		}
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Functions to retrieve data
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getAccountBalance($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getAccountBalance: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		if (!is_bool($corp))
		{
			$this->addMsg("Error","getAccountBalance: Non-bool value of corp param, reverting to default value");
			$corp = false;
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		if ($corp == true)
		{
			$contents = $this->retrieveXml("/corp/AccountBalance.xml.aspx", $timeout, $cachePath);
		}
		else
		{
			$contents = $this->retrieveXml("/char/AccountBalance.xml.aspx", $timeout, $cachePath);
		}
		
		return $contents;
	}
	
	public function getSkillInTraining($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getSkillInTraining: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/char/SkillInTraining.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}
	
	public function getCharacterSheet($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getCharacterSheet: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
	
		$contents = $this->retrieveXml("/char/CharacterSheet.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}
	
	public function getCharacters($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getCharacters: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
	
		$contents = $this->retrieveXml("/account/Characters.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}
	
	public function getServerStatus($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getServerStatus: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$contents = $this->retrieveXml("/Server/ServerStatus.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getSkillTree($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getSkillTree: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$contents = $this->retrieveXml("/eve/SkillTree.xml.aspx", $timeout);
		
		return $contents;
	}
	
	public function getCertificateTree($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getCertificateTree: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$contents = $this->retrieveXml("/eve/CertificateTree.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getRefTypes($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getRefTypes: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$contents = $this->retrieveXml("/eve/RefTypes.xml.aspx", $timeout);
		
		return $contents;
	}
	
	public function getMemberTracking($timeout = 60)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getMemberTracking: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/corp/MemberTracking.xml.aspx", $timeout, $cachePath);

		return $contents;
	}
	
	public function getStarbaseList($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getStarbaseList: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/corp/StarbaseList.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}
	
	public function getStarbaseDetail($id, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getStarbaseDetail: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		if (is_numeric($id))
		{
			$params = array();
			$params['itemID'] = $id;

			$cachePath = array();
			$cachePath[0] = 'userID';
			$cachePath[1] = 'characterID';
			$cachePath[2] = 'itemID';
			
			$contents = $this->retrieveXml("/corp/StarbaseDetail.xml.aspx", $timeout, $cachePath, $params);
			
			return $contents;
		}
		else
		{
			$this->addMsg("Error","getStarbaseDetail: Non-numeric value of id param, returning null");
			return null;
		}
	}
	
	public function getWalletTransactions($transid = null, $corp = false, $accountkey = 1000, $timeout = 65)
	// BUGBUG $timeout is hard-coded because of a bug in the EvE API, see http://myeve.eve-online.com/ingameboard.asp?a=topic&threadID=802053
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getWalletTransactions: Non-numeric value of timeout param, reverting to default value");
			$timeout = 65;
		}

		if (!is_bool($corp))
		{
			$this->addMsg("Error","getWalletTransactions: Non-bool value of corp param, reverting to default value");
			$corp = false;
		}
		
		if ($transid != null && !is_numeric($transid))
		{
			$this->addMsg("Error","getWalletTransactions: Non-numeric value of transid param, reverting to default value");
			$transid = null;
		}

		$params = array();
		
		// accountKey
		if (is_numeric($accountkey))
		{
			$params['accountKey'] = $accountkey;
		}
		else
		{
			$this->addMsg("Error","getWalletTransactions: Non-numeric value of accountkey param, defaulting to '1000'");
			$params['accountKey'] = 1000;
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$cachePath[2] = 'accountKey';
		
		// beforeTransID
		if ($transid != null && is_numeric($transid))
		{
			$params['beforeTransID'] = $transid;
			$cachePath[3] = 'beforeTransID';
		}

		if ($corp == true)
		{
			$contents = $this->retrieveXml("/corp/WalletTransactions.xml.aspx", $timeout, $cachePath, $params);
		}
		else
		{
			$contents = $this->retrieveXml("/char/WalletTransactions.xml.aspx", $timeout, $cachePath, $params);
		}
		
		return $contents;
	}
	
	public function getWalletJournal($refid = null, $corp = false, $accountkey = 1000, $timeout = 65)
	// BUGBUG $timeout is hard-coded because of a bug in the EvE API, see http://myeve.eve-online.com/ingameboard.asp?a=topic&threadID=802053
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getWalletJournal: Non-numeric value of timeout param, reverting to default value");
			$timeout = 65;
		}

		if (!is_bool($corp))
		{
			$this->addMsg("Error","getWalletJournal: Non-bool value of corp param, reverting to default value");
			$corp = false;
		}
		
		if ($refid != null && !is_numeric($refid))
		{
			$this->addMsg("Error","getWalletJournal: Non-numeric value of refid param, reverting to default value");
			$refid = null;
		}

		$params = array();
		
		// accountKey
		if (is_numeric($accountkey))
		{
			$params['accountKey'] = $accountkey;
		}
		else
		{
			$this->addMsg("Error","getWalletJournal: Non-numeric value of accountkey param, defaulting to '1000'");
			$params['accountKey'] = 1000;
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$cachePath[2] = 'accountKey';
		
		// beforeRefID
		if ($refid != null && is_numeric($refid))
		{
			$params['beforeRefID'] = $refid;
			$cachePath[3] = 'beforeRefID';
		}

		if ($corp == true)
		{
			$contents = $this->retrieveXml("/corp/WalletJournal.xml.aspx", $timeout, $cachePath, $params);
		}
		else
		{
			$contents = $this->retrieveXml("/char/WalletJournal.xml.aspx", $timeout, $cachePath, $params);
		}
		
		return $contents;
	}

	public function getCorporationSheet($corpid = null, $timeout = null) 
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getCorporationSheet: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		
		if ($corpid != null && !is_numeric($corpid))
		{
			$this->addMsg("Error","getCorporationSheet: Non-numeric value of corpid param, reverting to default value");
			$corpid = null;
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		if ($corpid != null && is_numeric($corpid))
		{
			$params = array();
			$params['corporationID'] = $corpid;
			$cachePath[2] = 'corporationID';
		}
		
 		$contents = $this->retrieveXml("/corp/CorporationSheet.xml.aspx", $timeout, $cachePath, $params);

 		return $contents;
	}

	public function getAllianceList($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getAllianceList: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$contents = $this->retrieveXml("/eve/AllianceList.xml.aspx", $timeout);

 		return $contents;
	}
	
	public function getAssetList($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getAssetList: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		if (!is_bool($corp))
		{
			$this->addMsg("Error","getAssetList: Non-bool value of corp param, reverting to default value");
			$corp = false;
		}
	   
		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		if ($corp == true)
		{
			$contents = $this->retrieveXml("/corp/AssetList.xml.aspx", $timeout, $cachePath);
		}
		else
		{
			$contents = $this->retrieveXml("/char/AssetList.xml.aspx", $timeout, $cachePath);
		}
		return $contents;
	}
	
	public function getIndustryJobs($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getIndustryJobs: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		if (!is_bool($corp))
		{
			$this->addMsg("Error","getIndustryJobs: Non-bool value of corp param, reverting to default value");
			$corp = false;
		}
		
		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		if ($corp == true)
		{
			$contents = $this->retrieveXml("/corp/IndustryJobs.xml.aspx", $timeout, $cachePath);
		}
		else
		{
			$contents = $this->retrieveXml("/char/IndustryJobs.xml.aspx", $timeout, $cachePath);
		}
		return $contents;
	}

	public function getFacWarSystems($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getFacWarSystems: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$contents = $this->retrieveXml("/map/FacWarSystems.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getFacWarStats($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getFacWarStats: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		if (!is_bool($corp))
		{
			$this->addMsg("Error","getFacWarStats: Non-bool value of corp param, reverting to default value");
			$corp = false;
		}
		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		if($corp == true)
		{
			$contents = $this->retrieveXml("/corp/FacWarStats.xml.aspx", $timeout, $cachePath);
		}
		else
		{
			$contents = $this->retrieveXml("/char/FacWarStats.xml.aspx", $timeout, $cachePath);
		}
		return $contents;
	}

	public function getFacWarTopStats($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getFacWarTopStats: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$contents = $this->retrieveXml("/eve/FacWarTopStats.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getJumps($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getMapJumps: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		$contents = $this->retrieveXml("/map/Jumps.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getSovereignty($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getMapSovereignty: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		$contents = $this->retrieveXml("/map/Sovereignty.xml.aspx", $timeout);
		return $contents;
	}

	public function getKills($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getMapKills: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		$contents = $this->retrieveXml("/map/Kills.xml.aspx", $timeout);
		return $contents;
	}

	public function getKillLog($killid = null, $corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getKillLog: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
	 
		if (!is_bool($corp))
		{
			$this->addMsg("Error","getKillLog: Non-bool value of corp param, reverting to default value");
			$corp = false;
		}
		
		if ($killid != null && !is_numeric($killid))
		{
			$this->addMsg("Error","getKillLog: Non-numeric value of killid param, reverting to default value");
			$killid = null;
		}
		
		$params = array();
			
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		
		//beforeKillID
		if ($killid != null && is_numeric($killid))
		{
			$params['beforeKillID'] = $killid;
			$cachePath[3] = 'beforeKillID';
		}

		if($corp == true)
		{
			$contents = $this->retrieveXml("/corp/Killlog.xml.aspx", $timeout, $cachePath,$params);
		}
		else
		{
			$contents = $this->retrieveXml("/char/KillLog.xml.aspx", $timeout, $cachePath,$params);
		}
		return $contents;
	}

	public function getMemberMedals($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getMedals: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/MemberMedals.xml.aspx", $timeout, $cachePath);
		return $contents;
	}

	public function getMedals($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getMedals: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		if (!is_bool($corp))
		{
			$this->addMsg("Error","getMedals: Non-bool value of corp param, reverting to default value");
			$corp = false;
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		if($corp == true)
		{
			$contents = $this->retrieveXml("/corp/Medals.xml.aspx", $timeout, $cachePath);
		}
		else
		{
			$contents = $this->retrieveXml("/char/Medals.xml.aspx", $timeout, $cachePath);
		}
		return $contents;
	}

	public function getMarketOrders($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getMarketOrders: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		if (!is_bool($corp))
		{
			$this->addMsg("Error","getMarketOrders: Non-bool value of corp param, reverting to default value");
			$corp = false;
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		if($corp == true)
		{
			$contents = $this->retrieveXml("/corp/MarketOrders.xml.aspx", $timeout, $cachePath);
		}
		else
		{
			$contents = $this->retrieveXml("/char/MarketOrders.xml.aspx", $timeout, $cachePath);
		}
		return $contents;
	}

	public function getConquerableStationList($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getConquerableStationList: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		$contents = $this->retrieveXml("/eve/ConquerableStationList.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getStandings($corp = false,$timeout = null)
	{

		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getStandings: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		if($corp == true)
		{
			$contents = $this->retrieveXml("/corp/Standings.xml.aspx", $timeout, $cachePath);
		}
		else 
		{
			$contents = $this->retrieveXml("/char/Standings.xml.aspx", $timeout, $cachePath);
		}
		return $contents;
	}

	public function getContainerLog($timeout = null)
	{

		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getContainerLog: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/ContainerLog.xml.aspx", $timeout, $cachePath);
		return $contents;
	}

	public function getShareHolders($timeout = null)
	{

		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getShareHolders: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/ShareHolders.xml.aspx", $timeout, $cachePath);
		return $contents;
	}
	
	public function getMemberSecurity($timeout = null)
	{

		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getMemberSecurity: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/MemberSecurity.xml.aspx", $timeout, $cachePath);
		return $contents;
	}

	public function getMemberSecurityLog($timeout = null)
	{

		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getMemberSecurityLog: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/MemberSecurityLog.xml.aspx", $timeout, $cachePath);
		return $contents;
	}

	public function getTitles($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getTitles: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/Titles.xml.aspx", $timeout, $cachePath);
		return $contents;
	}

	public function getErrorList($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getErrorList: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}
		$contents = $this->retrieveXml("/eve/ErrorList.xml.aspx", $timeout);
		return $contents;
	}

	public function getCharacterName($ids, $timeout = null )
	{
	// This is a function that should not be cached.  Unless $timeout is given, indicating a desire to cache by the user, we will turn off caching.
//		$uc = $this->usecache;

		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getCharacterName: Non-numeric value of timeout param, reverting to default value");
			$timeout = null;
		}

		if (is_string($ids) || is_numeric($ids))
		{
//			if ($uc) // caching is currently enabled, disable it for the duration
//			{
//				$this->cache(FALSE);
//			}

			$params = array();
			$params['ids'] = $ids;
			
			$cachePath = array();
			$cachePath[0] = 'ids';

			$contents = $this->retrieveXml("/eve/CharacterName.xml.aspx",$timeout,$cachePath,$params);

//			if ($uc) // caching was enabled, enable it again
//			{
//				$this->cache($uc);
//			}
			return $contents;		
		}
		else
		{
				$this->addMsg("Error","getCharacterName: Non-string/non-numeric or empty value of ids param, returning null");
				return null;
		}
	}
	
	public function getCharacterID($names, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
				$this->addMsg("Error","getCharacterID: Non-numeric value of timeout param, reverting to default value");
				$timeout = null;
		}

		if (is_string($names))
		{
			$params = array();
			$params['names'] = $names;

			$cachePath = array();
			$cachePath[0] = 'names';

			$contents = $this->retrieveXml("/eve/CharacterID.xml.aspx",$timeout,$cachePath,$params);

			return $contents;
		}
		else
		{
				$this->addMsg("Error","getCharacterID: Non-string or empty value of names param, returning null");
				return null;
		}
	}
	
	// getCharacterPortrait works quite differently from anything else. It returns a path to a JPEG file in the cache dir, not the actual data. There is no XML parsing, since there's no XML
	// Currently, there's also no real caching timeout, which needs to be changed
	public function getCharacterPortrait($id = null, $size = 64, $timeout = 1440)
	{ //  BUGBUG This will cache, but currently not set a timeout. A cleverer idea would be to cache for 24 hours, and check by file date

		if (!is_numeric($size)) // possible values are 64 and 256, but that's not checked, as CCP may change their mind
		{
			$this->addMsg("Error","getCharacterPortrait: Non-numeric value of size param, reverting to default value");
			$size = 64;
		}
		if (is_int($id))
		{
			$site = $this->getApiSite();
			$this->setApiSite('img.eve.is');
			
			$cachedir = $this->getCacheDir();
			$this->setCacheDir($cachedir."/imgcache");

			$params = array();
			$params['s'] = $size;
			$params['c'] = $id;

			$cachePath = array();
			$cachePath[0] = 'c';
			$cachePath[1] = '-';
			$cachePath[2] = 's';
			$cachePath[3] = '.jpg';

			$this->retrieveXml("/serv.asp",0,$cachePath,$params,TRUE); // optional "binary" parameter, and the timeout is BUGBUG see above

			$result = $this->getCacheFile("/serv.asp", $params, $cachePath,TRUE);
			
			$this->setApiSite($site);
			$this->setCacheDir($cachedir);

			return $result;
		}
		else
		{
				$this->addMsg("Error","getCharacterPortrait: Non-integer or empty value of id param, returning null");
				return null;
		}
	}
}
?>
