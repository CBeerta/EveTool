<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


define('ALE_CONFIG_DIR', APPPATH.'config');

require_once(BASEPATH.'../ale/factory.php');

class Eveapi
{

    /**
    * Ale Api Object
    **/
	public $api = Null;

	/**
	* userid + key from characters.ini
	**/
	private $api_credentials = Null;

	/**
	* All Characters loaded 
	**/
	public $characters = array();

    /**
    * Array with characters to be skipped, loaded from characters.ini
    **/
    public $skip_characters = array();

    /**
    * List of Characters located on an account
    **/
    public $accounts = array();

    /**
    * Currently Authenticated Character ID
    **/
    public $current_characterID = False;

	/**
	* Constructor, initialized Ale api, and loads configuration
	*
	* @access public
	**/
	public function __construct()
	{
		$this->api = AleFactory::get('eveapi'); 
		
		if (!is_readable(APPPATH.'config/characters.ini'))
		{
			die("Unable to read config/characters.ini");
		}
		$this->api_credentials = parse_ini_file(APPPATH.'config/characters.ini');

        if (isset($this->api_credentials['skip']))
        {
            $this->skip_characters = explode(':', $this->api_credentials['skip']);
        }
	}


    /**
    * Handles calls to Ale, always assumes 'char' context in the api
    *
    * @access public
    * @param $name name of the Function in Ale to call
    * @param $arguments arguments to pass through
    *
    * @TODO this could be used to actually load both char and corp stuff, and merge them?
    */
	public function __call($name, array $arguments)
	{
	    return $this->api->char->$name($arguments);
	}

	/**
	*
	* Convert an ale simplexml object to an array
	*
	* @access public
	* @param object Ale XML Object
	* @param string
	* @param array An Array to merge with each dataset
	* 
	**/
	public static function from_xml($xml, $to_merge = array())
	{
		$output = array();

        $children = $xml->result->children();
        $name =  $children[0]->attributes()->name;

		foreach ($xml->result->$name as $row)
		{
			$index = count($output);
			foreach ($row->attributes() as $name => $value)
			{
				$output[$index][(string) $name] = (string) $value;
				if (in_array((string) $name, array('date', 'transactionDateTime', 'sentDate', 'eventDate'))) 
				{
					$output[$index]['unix'.$name] = strtotime((string) $value);
				}
			}
			$output[$index] = array_merge($output[$index], (array) $to_merge);
		}
		
		return ($output);
	}


	/**
    * Lazy setCredentials 
    *
    * @access public
    * @param object character to authenticate as
    **/
    public function setCredentials($char)
    {
        if (!is_object($char))
        {
            die("\$char is not an object!");
        }
        
        $this->current_characterID = $char->characterID;
        return ($this->api->setCredentials($char->apiUser, $char->apiKey, $char->characterID));
    }
	

	/**
	* Load Characters from all accounts (skipping the ignored ones)
	*
	* @access public
	**/
	public function characters()
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
				// FIXME: Ignore Characters that are unreadable (for now)
				unset($this->api_credentials['apiuser'][$k]);
				unset($this->api_credentials['apikey'][$k]);
				continue;	
			}
			
			foreach ($account->result->characters as $character)
			{
                if (in_array((string) $character->name, $this->skip_characters))
                {
                    continue;
                }

                $this->accounts[(int) $v][] = $character->name;

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
		return($this->characters);
	}


    /** 
    *
    * Load assets from api for charaters, and merge them with inftypes, then cache
    *
    * @access public
    * @params object $characters Characters to pull assets fo
    * @params book $with_contents Wether to load container contents or not
    *
    **/
	public function assets($characters, $with_contents = True)
	{
	    $CI =& get_instance();
	    $cache_key = 'evetool_'.md5(implode(':', $characters)).'_'.$with_contents;
	    
		if ( ($assets = $CI->cache->get($cache_key)) !== False )
		{
		    return($assets);
	    }

        $assets = $typeidlist = array();
		foreach ($this->characters as $char)
		{
		    if (!in_array($char->name, $characters))
		    {
		        continue;
	        }
			$CI->eveapi->api->setCredentials($char->apiUser, $char->apiKey, $char->characterID);
            $_assets = $this->api->char->AssetList();

            foreach ($_assets->result->assets as $asset)
            {
                $assets[(int) $asset->itemID] = array(
                    'itemID' => (int) $asset->itemID,
                    'locationID' => (int) $asset->locationID,
                    'typeID' => (int) $asset->typeID,
                    'quantity' => (int) $asset->quantity,
                    'flag' => (int) $asset->flag,
                    'singleton' => (int) $asset->singleton,
                    'containerID' => Null,
                    'container' => Null,
                    'owner' => $char,
                    'contents' => array(),
                    );
                $typeidlist[] = (int) $asset->typeID;
                
                $container = (int) $asset->itemID;
                
                if (isset($asset->contents) )
                {
        		    foreach ($asset->contents as $content)
        		    {
                        $assets[$container]['contents'][] = (int) $content->itemID;

                        if ($with_contents)
                        {
                            $assets[(int) $content->itemID] = array(
                                'itemID' => (int) $content->itemID,
                                'typeID' => (int) $content->typeID,
                                'quantity' => (int) $content->quantity,
                                'flag' => (int) $content->flag,
                                'singleton' => (int) $content->singleton,
                                'containerID' => $container,
                                'container' => $assets[$container],
                                'locationID' => $assets[$container]['locationID'],
                                'owner' => $char,
                                );
                            $typeidlist[] = (int) $content->typeID;
                        }
        		    }
                }
            }
        }

        sort($typeidlist);
        $typeidlist = array_unique($typeidlist);
        $q = $CI->db->query("
            SELECT 
                invTypes.typeName,
                invTypes.typeID,
                invGroups.groupName,
                invTypes.groupID,
                invTypes.description,
                invTypes.volume,
                invTypes.mass,
                invCategories.categoryName,
		        (SELECT iconFile FROM eveIcons WHERE iconID=invTypes.iconID ) AS iconFile,
		        invGroups.categoryID,
			    (SELECT valueInt FROM dgmTypeAttributes WHERE typeID=invTypes.typeID AND attributeID=422) AS techlevel
            FROM 
                invTypes,
                invGroups,
                invCategories
            WHERE 
                invTypes.typeID IN (".implode(',', $typeidlist).") AND 
                invTypes.groupID=invGroups.groupID AND 
                invCategories.categoryID=invGroups.categoryID;");
        $types = array();
        
        foreach ($q->result() as $row)
        {
            $types[$row->typeID] = (array) $row;
        }

        foreach ($assets as $k => $v)
        {
            $assets[$k] = array_merge($v, $types[$v['typeID']]);
            if ($assets[$k]['container'] !== Null)
            {
                $assets[$k]['container'] = array_merge($assets[$k]['container'], $types[$assets[$k]['container']['typeID']]);
            }

        }
		$CI->cache->set($cache_key, $assets);
        return ($assets);
	}

	/**
    *
	* Load reftypes from eveapi and cache them
	*
	* @access public
	*
	**/
	public function reftypes()
	{
        $CI =& get_instance();
        
		if ( ($reftypes = $CI->cache->get('evetool_reftypes')) === False )
		{
		    $_reftypes = eveapi::from_xml($this->api->eve->RefTypes());
		
		    $reftypes = array();
		
		    foreach ($_reftypes as $reftype)
		    {
			    $reftypes[$reftype['refTypeID']] = $reftype['refTypeName'];
		    }
        }
        
		$CI->cache->set('evetool_reftypes', $reftypes);
		
		return ($reftypes);
	}
	
	/**
    *
	* Load skilltree and cache
	*
	* @access public
	*
	**/
	public function skilltree()
	{
        $CI =& get_instance();
        
		if ( ($skilltree = $CI->cache->get('evetool_skilltree')) === False )
		{
		    $_skilltree = $this->api->eve->SkillTree();
		    $skilltree = array();
		    
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
        // FIXME temp fix for the ore industrial skill
        $skilltree[3184] = array('typeName' => 'ORE Industrial', 'description' => 'ORE Industrial', 'groupID' => '257', 'rank' => '4');
        
        $CI->cache->set('evetool_skilltree', $skilltree);
	
		return ($skilltree);
	}

    /**
    *
    * Load Player Conquerable Stationlist and cache 
    *
    * @access public
    **/
	public function stationlist()
    {
        $CI =& get_instance();

        $api = $CI->eveapi->api;
        $stationlist = array();

		if ( ($stationlist = $CI->cache->get('evetool_stationlist')) === False )
		{
            $_stationlist = $api->eve->ConquerableStationList();
            foreach ($_stationlist->result->outposts as $station)
            {
                foreach (array('stationName', 'stationTypeID', 'solarSystemID', 'corporationID', 'corporationName') as $field)
                {
                    $stationlist[(int) $station->stationID][$field] = (string) $station->$field;
                }
            }
        }
        $CI->cache->set('evetool_stationlist', $stationlist);

        return ($stationlist);
    }

    /**
    * Return All available EVE Regions
    *
    * @access public
    **/
    public function eve_regions()
    {
        $CI =& get_instance();
        $q = $CI->db->query('SELECT regionID,regionName,factionID FROM mapRegions ORDER BY regionName;');
        $regions = array();
        foreach ( $q->result() as $row )
        {
            $regions[$row->regionID] = $row->regionName;
        }
        return ($regions);
    }

    /**
    * Return All available NPC Corporations
    *
    * @access public
    **/
    public function npc_corps()
    {
        $CI =& get_instance();

        if (($corps = $CI->cache->get("evetool_npc_corps")) === False)
        {
            $q = $CI->db->query('SELECT corporationID,itemName FROM crpNPCCorporations,eveNames WHERE crpNPCCorporations.corporationID=eveNames.itemID ORDER BY itemName;');
            $corps = array();
            foreach ( $q->result() as $row )
            {
                $corps[$row->corporationID] = $row->itemName;
            }
            $CI->cache->set("evetool_npc_corps", $corps);
        }
        return ($corps);
    }


	/**
	*
	* Find the skill level for the currently authenticated character
	*
    * @access public	
    * @param int $skillID ID of the skill to find
    **/
    public function skill_level($skillID)
    {
        $CI =& get_instance();

        $mckey = "evetool_skill_level_{$this->current_characterID}_{$skillID}";
        $level = 0;

        if ($this->current_characterID !== False && ($level = $CI->cache->get($mckey)) === False)
        {
	        $charsheet = $this->api->char->CharacterSheet();

		    foreach ($charsheet->result->skills as $skill)
		    {
		        if ($skill->typeID == $skillID)
		        {
		            $level = $skill->level;
                    $CI->cache->set($mckey, $level);
		            break;
	            }
            }
        }

        return ($level);
    }

	/**
	*
	* Pull Charactersheet  and enhance it with some additional info
	*
    * @access public	
    *
    **/
    public function CharacterSheet()
    {
#       print '<pre>';
        $data = array();
        
	    $charsheet = $this->api->char->CharacterSheet();
        $skilltree = $this->skilltree();

        $data['queue'] = eveapi::from_xml($this->SkillQueue());

        foreach($data['queue'] as $k => $v)
        {
            if (!empty($skilltree[$v['typeID']]))
            {
                $data['queue'][$k] = array_merge($data['queue'][$k], $skilltree[$v['typeID']]);
            }
        }


        $data['skills_in_queue'] = count($data['queue']);

	    foreach ($charsheet->result->children() as $v)
	    {
	        if (count($v->children()) > 0)
	        {
	            continue;
            }
	        $data[$v->getName()] = (string) $v;
	    }

        $data['skills_total'] = $data['skillpoints_total'] = 0;
        $data['skills_at_level'] = array_fill(0, 6, 0);
        $data['skills'] = array();

		foreach ($charsheet->result->skills as $_skill)
		{
			$skill = $_skill->attributes();
			$data['skills'][(int) $skill->typeID] = (object) array_merge($skilltree[(int) $skill->typeID], array('skillpoints' => (int) $skill->skillpoints, 'level' => (int) $skill->level));
            $data['skillpoints_total'] += (int) $skill['skillpoints'];

            $groupID = $data['skills'][(int) $skill->typeID]->groupID;
            
            if (!isset($groups[$groupID]))
            {
                $groups[$groupID] = array('groupSP' => 0, 'skillCount' => 0);
            }
            $groups[$groupID]['groupSP'] += (int) $skill->skillpoints;
            $groups[$groupID]['skillCount'] ++;
            $groups[$groupID]['skills'][] = (int) $skill->typeID;

            $data['skills_total'] ++;
          	$data['skills_at_level'][(int) $skill['level']] ++;
		}
		$data['groups'] = $groups;

        $attributes = array(
            'intelligence' => array(3377,12376),
            'charisma' => array(3376,12383),
            'perception' => array(3379,12387),
            'memory' => array(3378,12385),
            'willpower' => array(3375,12386));

		foreach (array_keys($attributes)  as $k)
		{
            $data['attributes'][$k] = (string) $charsheet->result->attributes->$k;
		}


		$training = $this->SkillInTraining();
		if ((string) $training->result->skillInTraining > 0)
		{
			foreach (array('trainingTypeID', 'trainingToLevel', 'trainingStartTime', 'trainingEndTime' ) as $n)
			{
				$data[$n] = (string) $training->result->$n;
			}
			$data['trainingTypeName'] = $skilltree[(string) $training->result->trainingTypeID]['typeName'];
		    $data['isTraining'] = True;
		}
		else
		{
		    $data['isTraining'] = False;
		    $data['trainingTypeID'] = -1;
	    }

/* 
        //FIXME is this even worh adding? will be gone soonish anyhow

        foreach ($attributes as $attribute => $se)
        {
            $sll = isset($data[267]['skills'][$se[0]]['level']) ? $data[267]['skills'][$se[0]]['level'] : 0; // Learnings
            $shl = isset($data[267]['skills'][$se[1]]['level']) ? $data[267]['skills'][$se[1]]['level'] : 0; // Advanced Learnings
            //$enhancer = isset($charsheet['enhancers'][$attribute.'Bonus']['augmentatorValue']) ? $charsheet['enhancers'][$attribute.'Bonus']['augmentatorValue'] : 0;

            $enhancer = 0;
            $data['attributes'][$attribute] = number_format(($data['attributes'][$attribute] + $enhancer + $sll + $shl) * $learning, 2);

            if ($enhancer > 0)
            {
                $data['attributes'][$attribute] .= " (+{$enhancer})";
            }
        }
*/
		
		if ($data['gender'] == 'Male')
		{
			$data['sex'] = 'He';
			$data['sex2'] = 'His';
		}
		else
		{
			$data['sex'] = 'She';
			$data['sex2'] = 'Her';
		}

        return ($data);
    }

}



?>
