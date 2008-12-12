<?php
/**************************************************************************
	PHP Api Lib, v0.22, 2008-07-19

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
	public $debug = false;
	private $msg = array();
	private $usecache = true;
	private $timetolerance = 5; // minutes to wait after cachedUntil, to allow for the server's time being fast

	public function setCredentials($userid, $apikey, $charid = null)
	{
		if (empty($userid) || empty ($apikey))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","setCredentials: userid and apikey must not be empty");
			}
			return false;
		}

		if (!is_numeric($userid))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","setCredentials: userid must be a numeric value");
			}
			return true;
		}
		
		if (!is_string($apikey))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","setCredentials: apikey must be a string value");
			}
			return false;
		}
		
		if ($charid != null && !is_numeric($charid))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","setCredentials: charid must be a numeric value");
			}
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
		} else {
			$this->charid = null;
		}
		
		return true;
	}
	
	public function debug($bool)
	{
		if (is_bool($bool))
		{
			$this->debug = $bool;
			return true;
		}
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Error","debug: parameter must be present and boolean");
			}
			return false;
		}
	}
	
	public function cache($bool)
	{
		if (is_bool($bool))
		{
			$this->usecache = $bool;
			return true;
		}
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Error","cache: parameter must be present and boolean");
			}
			return false;
		}
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
			if ($this->debug)
			{
				$this->addMsg("Error","setCacheDir: parameter must be present and a string");
			}
			return false;
		}
	}
	
	public function setTimeTolerance($tolerance)
	{
		if (is_int($tolerance))
		{
			$this->timetolerance = $tolerance;
			return true;
		} else {
			if ($this->debug)
				$this->addMsg("Error","setTimeTolerance: parameter must be present and an integer");
			return false;
		}

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
			if ($this->debug)
			{
				$this->addMsg("Error","addMsg: type and message must not be empty");
			}
			return 0;
		}
	}
	
	/**********************
		Retrieve an XML File
		$path	path relative to the $apisite url
		$timeout	amount of time to keep the cached data before re-requesting it from the API, in minutes
		$cachePath	optional array of string values . These can be indizes into $params, or arbitrary strings, 
				and will be used to build the relative path to the cache file
		$params	optional array of paramaters (exclude apikey and userid, and charid)
				$params['characterID'] = 123456789;
	***********************/
	public function retrieveXml($path, $timeout = null, $cachePath = null, $params = null)
	{
		if ($cachePath != null && !is_array($cachePath))
		{			
			if ($this->debug)
			{
				$this->addMsg("Error","retrieveXml: Non-array value of cachePath param, reverting to default value");
			}
			$cachePath = null;
		}
		
		if ($params != null && !is_array($params))
		{			
			if ($this->debug)
			{
				$this->addMsg("Error","retrieveXml: Non-array value of params param, reverting to default value");
			}
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
			
			// continue when not cached
			if (!$this->usecache || !$this->isCached($path, $params, $cachePath, $timeout))
			{
				// Presumably, if it's not set to '&', they might have had a reason for that - be a good citizen
				$sep = ini_get('arg_separator.output');
				// Necessary so that http_build_query does not spaz and give us '&amp;' as a separator on certain hosting providers
				ini_set('arg_separator.output','&');
				// poststring
				if (count($params) > 0)
					$poststring = http_build_query($params); // which has been forced to use '&' by ini_set, at the end of this file
				else
					$poststring = "";
				// And set it back to whatever sensical or non-sensical value it was in the 1st place
				ini_set('arg_separator.output','&');

				// open connection to the api
				// Note some free PHP5 servers block fsockopen() - in that case, find a different hosting provider, please
				$fp = fsockopen($this->apisite, 80, $errno, $errstr, 30);

				if (!$fp)
				{
					if ($this->debug)
						$this->addMsg("Error", "retrieveXml: Could not connect to API URL at $this->apisite, error $errstr ($errno)");
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
						fputs ($fp, $poststring."\r\n");
					
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
						
						// check if there's an error or not
						$xml = new SimpleXMLElement($contents);
						
						$error = (string) $xml->error;
						if (!empty($error))
						{
							if ($this->debug)
							{
								$this->addMsg("Api", $error);
							}
							
							if ($this->isCached($path, $params, $cachePath, $timeout))
							{
								return $this->loadCache($path, $params, $cachePath);
							}
							
							return null;
						}
						
						unset ($xml); // reduce memory footprint

						if (!$this->isCached($path, $params, $cachePath, $timeout))
						{
							$this->store($contents, $path, $params, $cachePath);
						}
						
						return $contents;
					}
					
					if ($this->debug)
					{
						$this->addMsg("Error", "retrieveXml: Could not parse contents");
					}
					
					return null;
				}
			}
			else
			{
				return $this->loadCache($path, $params, $cachePath);
			}
		}
		elseif ($this->debug)
		{
			$this->addMsg("Error", "retrieveXml: path is empty");
		}
		
		return null;
	}
	
	private function getCacheFile($path, $params, $cachePath)
	{
		$realpath = $this->cachedir;
		
		if ($cachePath != null)
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
		
		$realpath .= $path;
				
		return $realpath;
	}
	
	private function store($contents, $path, $params, $cachePath)
	{
		$file = $this->getCacheFile($path, $params, $cachePath);

		if (!file_exists(dirname($file)))
		{
			mkdir(dirname($file), 0777, true);
		}
		
		$fp = fopen($file, "w");
		
		if ($fp)
		{
			fwrite($fp, $contents);
			fclose($fp);
			
			if ($this->debug)
			{
				$this->addMsg("Info","store: Created cache file:" . $file);
			}
		}
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Error", "store: Could not open cache file for writing: " . $file);
			}
		}
		
	}
	
	private function loadCache($path, $params, $cachePath)
	{
		// its cached, open it and use it
		$file = $this->getCacheFile($path, $params, $cachePath);
		
		$fp = fopen($file, "r");
		if ($fp)
		{
			$contents = fread($fp, filesize($file));
			fclose($fp);

			if ($this->debug)
			{
				$this->addMsg("Info","loadCache: Fetched cache file:" . $file);
			}
		}
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Error", "loadCache: Could not open cache file for reading: " . $file);
			}
		}

		return $contents;
	}
	
	// checking if the cache expired or not based on TQ time
	private function isCached($path, $params, $cachePath, $timeout)
		{
		$file = $this->getCacheFile($path, $params, $cachePath);

		if (file_exists($file))
		{
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

				// get GMT time
				$timenow = time();
				$now = $timenow - date('Z', $timenow);

// Uncomment in case we need some deep-dive debug. There's a TODO here - have levels of debug				
//				if ($this->debug) {
//				   $this->addMsg("Info","Got this at ".$time.", keep it until ".$until.", it is now ".$now);
//				   $this->addMsg("Info","Formatted: Got this at ".strftime("%b %d %Y %X",$time).", keep it until ".strftime("%b %d %Y %X",$until).", it is now ".strftime("%b %d %Y %X",$now));
//				}

				if (!$timeout) // no explicit timeout given, use the cachedUntil time CCP gave us
				{
					if (($until + $this->timetolerance * 60) < $now) // time to fetch again, with some minutes leeway
						return false;
				} else {
					// if now is $timeout minutes ahead of the cached time, pretend this file is not cached
					$minutes = ($timeout + $this->timetolerance) * 60;
					if ($now >= $time + $minutes)
						return false;
				}

				return true; // default fall-through - cache is still valid
			}
			else
			{
				if ($this->debug)
				{
					$this->addMsg("Error", "isCached: Could not open cache file for reading: " . $file);
				}
				return false;
			}
		}
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Info", "isCached: Cache file does not (yet?) exist: " . $file);
			}
			return false;
		}
	}
	
	public function printErrors()
	{
		foreach ($this->msg as $msg)
		{
			echo ("<b>" . $msg['type'] . "</b>: " . $msg['msg'] . "</br>\n");
		}
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Functions to retrieve data
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getAccountBalance($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getAccountBalance: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = null;
		}

		if (!is_bool($corp))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getAccountBalance: Non-bool value of corp param, reverting to default value");
			}
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
			if ($this->debug)
			{
				$this->addMsg("Error","getSkillInTraining: Non-numeric value of timeout param, reverting to default value");
			}
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
			if ($this->debug)
			{
				$this->addMsg("Error","getCharacterSheet: Non-numeric value of timeout param, reverting to default value");
			}
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
			if ($this->debug)
			{
				$this->addMsg("Error","getCharacters: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = null;
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
	
		$contents = $this->retrieveXml("/account/Characters.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}
	
	public function getSkillTree($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getSkillTree: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = null;
		}

		$contents = $this->retrieveXml("/eve/SkillTree.xml.aspx", $timeout);
		
		return $contents;
	}
	
	public function getRefTypes($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getRefTypes: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = null;
		}

		$contents = $this->retrieveXml("/eve/RefTypes.xml.aspx", $timeout);
		
		return $contents;
	}
	
	public function getMemberTracking($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getMemberTracking: Non-numeric value of timeout param, reverting to default value");
			}
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
			if ($this->debug)
			{
				$this->addMsg("Error","getStarbaseList: Non-numeric value of timeout param, reverting to default value");
			}
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
			if ($this->debug)
			{
				$this->addMsg("Error","getStarbaseDetail: Non-numeric value of timeout param, reverting to default value");
			}
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
			if ($this->debug)
			{
				$this->addMsg("Error","getStarbaseDetail: Non-numeric value of id param, returning null");
			}
			return null;
		}
	}
	
	public function getWalletTransactions($transid = null, $corp = false, $accountkey = 1000, $timeout = 65)
	// BUGBUG $timeout is hard-coded because of a bug in the EvE API, see http://myeve.eve-online.com/ingameboard.asp?a=topic&threadID=802053
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getWalletTransactions: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 65;
		}

		if (!is_bool($corp))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getWalletTransactions: Non-bool value of corp param, reverting to default value");
			}
			$corp = false;
		}
		
		if ($transid != null && !is_numeric($transid))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getWalletTransactions: Non-numeric value of transid param, reverting to default value");
			}
			$transid = null;
		}

		$params = array();
		
		// accountKey
		if (is_numeric($accountkey))
			$params['accountKey'] = $accountkey;
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getWalletTransactions: Non-numeric value of accountkey param, defaulting to '1000'");
			}
			$params['accountKey'] = 1000;
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$cachePath[2] = 'accountKey';
		
		// beforeRefID
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
			if ($this->debug)
			{
				$this->addMsg("Error","getWalletJournal: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 65;
		}

		if (!is_bool($corp))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getWalletJournal: Non-bool value of corp param, reverting to default value");
			}
			$corp = false;
		}
		
		if ($refid != null && !is_numeric($refid))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getWalletJournal: Non-numeric value of refid param, reverting to default value");
			}
			$refid = null;
		}

		$params = array();
		
		// accountKey
		if (is_numeric($accountkey))
			$params['accountKey'] = $accountkey;
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getWalletJournal: Non-numeric value of accountkey param, defaulting to '1000'");
			}
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
			if ($this->debug)
			{
				$this->addMsg("Error","getCorporationSheet: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = null;
		}
		
		if ($corpid != null && !is_numeric($corpid))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getCorporationSheet: Non-numeric value of corpid param, reverting to default value");
			}
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
			if ($this->debug)
			{
				$this->addMsg("Error","getAllianceList: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = null;
		}

		$contents = $this->retrieveXml("/eve/AllianceList.xml.aspx", $timeout);

 		return $contents;
	}
	
	public function getAssetList($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getAssetList: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = null;
		}

		if (!is_bool($corp))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getAssetList: Non-bool value of corp param, reverting to default value");
			}
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
			if ($this->debug)
			{
				$this->addMsg("Error","getIndustryJobs: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = null;
		}

		if (!is_bool($corp))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getIndustryJobs: Non-bool value of corp param, reverting to default value");
			}
			$corp = false;
		}
		
		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		if ($corp == true)
		{
			$contents = $this->retrieveXml("/corp/IndustryJobs.xml.aspx", $timeout, $cachePath, $cachethis);
		}
		else
		{
			$contents = $this->retrieveXml("/char/IndustryJobs.xml.aspx", $timeout, $cachePath, $cachethis);
		}
		return $contents;
	}

public function getFactionalOccupancy($timeout = null)
	{
		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getFactionalOccupancy: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}

		$contents = $this->retrieveXml("/map/FacWarSystems.xml.aspx", $timeout, null, $cachethis);
		
		return $contents;
	}

	public function getFactionalStats($corp = false, $timeout = null)
	{
		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getFactionalStats: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}

	 if (!is_bool($corp))
		{
		if ($this->debug)
		{
		$this->addMsg("Error","getFactionalStats: Non-bool value of corp param, reverting to default value");
		}
	$corp = false;
	}
	   $cachePath = array();
	   $cachePath[0] = 'userID';
	   $cachePath[1] = 'characterID';
		if($corp == true)
			{
			$contents = $this->retrieveXml("/corp/FacWarStats.xml.aspx", $timeout, $cachePath, $cachethis);
			}
		else
			{
			$contents = $this->retrieveXml("/char/FacWarStats.xml.aspx", $timeout, $cachePath, $cachethis);
			}
		return $contents;
	}

	public function getFactionalTop100($timeout = null)
	{
		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getFactionalTop100: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}

		$contents = $this->retrieveXml("/eve/FacWarTopStats.xml.aspx", $timeout, null, $cachethis);
		
		return $contents;
	}

	public function getMapJumps($timeout = null)
	{
		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getMapJumps: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}

		$contents = $this->retrieveXml("/map/Jumps.xml.aspx", $timeout, null, $cachethis);
		
		return $contents;
	}

	public function getMapSovereignty($timeout = null)
	{
		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getMapSovereignty: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}
		$contents = $this->retrieveXml("/map/Sovereignty.xml.aspx", $timeout, null, $cachethis);
		return $contents;
	}

	public function getMapKills($timeout = null)
	{
		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getMapKills: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}
		$contents = $this->retrieveXml("/map/Kills.xml.aspx", $timeout, null, $cachethis);
		return $contents;
	}

	public function getKillLog($corp = false, $timeout = null)
	{
		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getKillLog: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}
	 
	 if (!is_bool($corp))
		{
		if ($this->debug)
		{
		$this->addMsg("Error","getKillLog: Non-bool value of corp param, reverting to default value");
		}
	$corp = false;
	}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		if($corp == true)
			{
			$contents = $this->retrieveXml("/corp/Killlog.xml.aspx", $timeout, $cachePath, $cachethis);
			}
		else
			{
			$contents = $this->retrieveXml("/char/KillLog.xml.aspx", $timeout, $cachePath, $cachethis);
			}
		return $contents;
	}

	public function getMarketOrders($corp = false, $timeout = null)
	{
		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getMarketOrders: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}
	 if (!is_bool($corp))
		{
		if ($this->debug)
		{
		$this->addMsg("Error","getMarketOrders: Non-bool value of corp param, reverting to default value");
		}
	$corp = false;
	}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		if($corp == true)
			{
			$contents = $this->retrieveXml("/corp/MarketOrders.xml.aspx", $timeout, $cachePath, $cachethis);
			}
		else
			{
			$contents = $this->retrieveXml("/char/MarketOrders.xml.aspx", $timeout, $cachePath, $cachethis);
			}
		return $contents;
	}

	public function getConquerableStations($timeout = null)
	{
		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getConquerableStations: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}
		$contents = $this->retrieveXml("/eve/ConquerableStationList.xml.aspx", $timeout, null, $cachethis);
		
		return $contents;
	}

	public function getStandings($corp = false,$timeout = null)
	{

		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getConquerableStations: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		if($corp == true)
		{
		$contents = $this->retrieveXml("/corp/Standings.xml.aspx", $timeout, $cachePath, $cachethis);
		}
		else 
		{
		$contents = $this->retrieveXml("/char/Standings.xml.aspx", $timeout, $cachePath, $cachethis);
		}
		return $contents;
	}

	public function getContainerLog($timeout = null)
	{

		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getContainerLog: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/ContainerLog.xml.aspx", $timeout, $cachePath, $cachethis);
		return $contents;
	}

	public function getShareHolders($timeout = null)
	{

		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getShareHolders: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/shareholders.xml.aspx", $timeout, $cachePath, $cachethis);
		return $contents;
	}

}
?>