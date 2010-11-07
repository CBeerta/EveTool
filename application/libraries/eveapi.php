<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


define('ALE_CONFIG_DIR', APPPATH.'config');

require_once(BASEPATH.'../ale/factory.php');

class Eveapi
{
	public $api = Null;
	
	private $api_credentials = Null;
	
	public $characters = array();
	
	public function __construct()
	{
		$this->api = AleFactory::get('eveapi'); 
		
		if (!is_readable(APPPATH.'config/characters.ini'))
		{
			die("Unable to read config/characters.ini");
		}
		$this->api_credentials = parse_ini_file(APPPATH.'config/characters.ini');
	}
	
	public static function from_xml($xml, $type, $to_merge = array())
	{
		$output = array();

		foreach ($xml->result->$type as $row)
		{
			$index = count($output);
			foreach ($row->attributes() as $name => $value)
			{
				$output[$index][(string) $name] = (string) $value;
				if (in_array((string) $name, array('date', 'transactionDateTime'))) 
				{
					$output[$index]['unix'.$name] = strtotime((string) $value);
				}
			}
			$output[$index] = array_merge($output[$index], (array) $to_merge);
		}
		
		return ($output);
	}
	public function load_characters()
	{
		foreach ($this->api_credentials['apiuser'] as $k => $v)
		{
			if (!empty($this->api_credentials['apikey'][$k]))
			{
				$this->api->setCredentials($v, $this->api_credentials['apikey'][$k]);
			}
			else
			{
				throw new LogicException(sprintf("ApiUser [%s] doesn't have a valid ApiKey set.", $v));
			}
			
			try
			{
				$account = $this->api->account->Characters();
			}
			catch (Exception $e)
			{
				// FIXME: Ignore Characters that are unreadable  (for now)
				unset($this->api_credentials['apiuser'][$k]);
				unset($this->api_credentials['apikey'][$k]);
				continue;	
			}
			
			foreach ($account->result->characters as $character)
			{
				$this->characters[(string) $character->name] = (object) array(
					'name' => (string) $character->name,
					'apiUser' => (int) $v,
					'apiKey' => (string) $this->api_credentials['apikey'][$k],
					'characterID' => (int) $character->characterID,
					'corporationName' => (string) $character->corporationName,
					'corporationID' => (int) $character->corporationID,
					);
			}
		}
		ksort($this->characters);			
		return (array_keys($this->characters));
	}
	
	public function get_reftypes()
	{
		$_reftypes = eveapi::from_xml($this->api->eve->RefTypes(), 'refTypes');
		
		$reftypes = array();
		
		foreach ($_reftypes as $reftype)
		{
			$reftypes[$reftype['refTypeID']] = $reftype['refTypeName'];
		}
	
		return ($reftypes);
	}
}



?>