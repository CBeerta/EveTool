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
				if (in_array((string) $name, array('date', 'transactionDateTime', 'sentDate'))) 
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
	
	public function get_skilltree()
	{
        $CI =& get_instance();

		$_skilltree = $this->api->eve->SkillTree();
		$skilltree = array();

		if ( ($this->skilltree = $CI->cache->get('evetool_skilltree')) === False )
		{
		    foreach ($_skilltree->result->skillGroups as $group)
		    {
		        foreach ($group->skills as $skill)
		        {
		            $skilltree[(string) $skill->typeID]['groupName'] = (string) $group->groupName;
				    foreach (array('typeName', 'description', 'groupID', 'rank') as $field)
				    {
					    $skilltree[(string) $skill->typeID][$field] = (string) $skill->$field;
				    }
		        }
		    }
        }
		$CI->cache->set('evetool_skilltree', $this->skilltree);
	
		return ($skilltree);
	}
	
	public static function charsheet_extra_info($charsheet)
	{
        $data['skillsTotal'] = $data['skillPointsTotal'] = 0;
        $data['skillsAtLevel'] = array_fill(0, 6, 0);
        
		$skillTree = array();        
		
        //print '<pre>';
		foreach ($charsheet->result->skills as $_skill)
		{
			$skill = $_skill->attributes();
			//print_r($skill);
            $data['skillPointsTotal'] += (int) $skill['skillpoints'];
			/*            
            $s = $this->eveapi->skilltree[$skill['typeID']];
            if (!isset($skillTree[$s['groupID']]))
            {
                $skillTree[$s['groupID']] = array('groupSP' => 0, 'skillCount' => 0);
            }

            $skillTree[$s['groupID']]['skills'][$skill['typeID']] = array(
                'typeID' => $skill['typeID'],
                'skillpoints' => $skill['skillpoints'],
                'rank' => $s['rank'],
                'typeName' => $s['typeName'],
                'description' => $s['description'],
                'level' => $skill['level']);
            $skillTree[$s['groupID']]['groupSP'] += $skill['skillpoints'];
            $skillTree[$s['groupID']]['skillCount'] ++;
            */
            $data['skillsTotal'] ++;
          	$data['skillsAtLevel'][(int) $skill['level']] ++;
		}
		
		//print_r($data);
        
        //die();
		return ($data);
	}
}



?>
