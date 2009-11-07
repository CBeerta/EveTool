<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

@set_include_path(@get_include_path() . PATH_SEPARATOR . BASEPATH.'../eveapi/eveapi/');

require_once(BASEPATH.'../eveapi/eveapi/class.api.php');
require_once(BASEPATH.'../eveapi/eveapi/class.standings.php');
require_once(BASEPATH.'../eveapi/eveapi/class.alliancelist.php');
require_once(BASEPATH.'../eveapi/eveapi/class.accountbalance.php');
require_once(BASEPATH.'../eveapi/eveapi/class.charactersheet.php');
require_once(BASEPATH.'../eveapi/eveapi/class.charselect.php');
require_once(BASEPATH.'../eveapi/eveapi/class.corporationsheet.php');
require_once(BASEPATH.'../eveapi/eveapi/class.reftypes.php');
require_once(BASEPATH.'../eveapi/eveapi/class.skilltree.php');
require_once(BASEPATH.'../eveapi/eveapi/class.marketorders.php');
require_once(BASEPATH.'../eveapi/eveapi/class.membertrack.php');
require_once(BASEPATH.'../eveapi/eveapi/class.industryjobs.php');
require_once(BASEPATH.'../eveapi/eveapi/class.wallettransactions.php');
require_once(BASEPATH.'../eveapi/eveapi/class.walletjournal.php');
require_once(BASEPATH.'../eveapi/eveapi/class.characterid.php');
require_once(BASEPATH.'../eveapi/eveapi/class.titles.php');

class EveApi Extends Api {

    public $reftypes;
    public $skilltree;
    public $stationlist;
    public $corpMembers = array();

    function __construct($params)
    {
        $CI =& get_instance();

        if (!empty($params['cachedir']))
        {
            $this->cache(true);
            $this->setCacheDir($params['cachedir']);
        }

        $this->reftypes = RefTypes::getRefTypes($this->getRefTypes());
        $skilltree = SkillTree::getSkillTree($this->getSkillTree());
        $this->stationlist = Stations::getConquerableStationList($this->getConquerableStationList());
        $this->corpMembers = array(); // this is filled by 'has_corpapi_access()

        foreach ($skilltree as $group)
        {
            foreach ($group['skills'] as $skill)
            {
                foreach (array('typeName', 'description', 'groupID', 'rank') as $field)
                {
                    $this->skilltree[$skill['typeID']][$field] = $skill[$field];
                }
            }
        }
	}


    function has_corpapi_access()
    {
		$CI =& get_instance();
		$data = CharacterSheet::getCharacterSheet($this->eveapi->getcharactersheet());
		
		if (!empty($data['corporationRoles']))
		{
			return True;
		}
        return False; 
    }
	
    /**
    * Find the Skill level for a specific skill (ie Production efficience, Connections, etc)
    *
    * @access public
    * @param int
    **/
    public function get_skill_level($typeID)
    {
        $CI =& get_instance();
        
        $charsheet = CharacterSheet::getCharacterSheet($this->getcharactersheet());
        $l = 0;
        foreach($charsheet['skills'] as $skill)
        {
            if($skill['typeID'] == $typeID)
            {
                $l = $skill['level'];
            }
        }
        
        return ($l);
    }
    
    /**
    * Return All available EVE Regions
    *
    * @access public
    **/
    public function get_eve_regions()
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
    public function get_npc_corps()
    {
        $CI =& get_instance();
        $q = $CI->db->query('SELECT corporationID,itemName FROM crpNPCCorporations,eveNames WHERE crpNPCCorporations.corporationID=eveNames.itemID ORDER BY itemName;');
        $corps = array();
        foreach ( $q->result() as $row )
        {
            $corps[$row->corporationID] = $row->itemName;
        }
        return ($corps);
    }

	
	public function getSkillQueue($timeout = null, $cachethis = null)
    {
		if ($timeout && !is_numeric($timeout))
		{
			$this->addMsg("Error","getSkillQueue: Non-numeric value of timeout param, reverting to default value");
			$timeout = 1440;
		}

		if ($cachethis != null && !is_bool($cachethis))
		{
			$this->addMsg("Error","getSkillQueue: Non-bool value of cachethis param, reverting to default value");
			$cachethis = null;
		}

        $cachePath = array();
        $cachePath[0] = 'userID';
        $cachePath[1] = 'characterID';
        $cachePath[2] = 'accountKey';

		$contents = $this->retrieveXml("/char/SkillQueue.xml.aspx", $timeout, $cachePath, $cachethis);
		return $contents;
    }

    
    public function getAssetList($timeout = 1440, $cachethis = null)
    {
		if (!is_numeric($timeout))
		{
			$this->addMsg("Error","getAssetList: Non-numeric value of timeout param, reverting to default value");
			$timeout = 1440;
		}

		if ($cachethis != null && !is_bool($cachethis))
		{
			$this->addMsg("Error","getAssetList: Non-bool value of cachethis param, reverting to default value");
			$cachethis = null;
		}

        $cachePath = array();
        $cachePath[0] = 'userID';
        $cachePath[1] = 'characterID';
        $cachePath[2] = 'accountKey';

		$contents = $this->retrieveXml("/char/AssetList.xml.aspx", $timeout, $cachePath, $cachethis);
		return $contents;
    }

	public function getConquerableStationList($timeout = 1500, $cachethis = null)
	{
		if (!is_numeric($timeout))
		{
			$this->addMsg("Error","getConquerableStationList: Non-numeric value of timeout param, reverting to default value");
			$timeout = 1500;
		}

		if ($cachethis != null && !is_bool($cachethis))
		{
			$this->addMsg("Error","getConquerableStationList: Non-bool value of cachethis param, reverting to default value");
			$cachethis = null;
		}
		$contents = $this->retrieveXml("/eve/ConquerableStationList.xml.aspx", $timeout, null, $cachethis);
		return $contents;
	}
	
	public function get_daily_walletjournal($wallet)
	{
		$data = array();
        $daily = array();
        foreach ($wallet as $entry)
        {
            $day = apiTimePrettyPrint($entry['date'], 'Y-m-d');
            if (!isset($daily[$day]))
            {
                $total[$day]['prettydate'] = apiTimePrettyPrint($entry['date'], 'l, j F Y');
                $total[$day]['expense'] = 0;
                $total[$day]['income'] = 0;
            }
            if (!isset($daily[$day][$entry['refTypeID']]))
            {
                $daily[$day][$entry['refTypeID']] = array(
                    'refTypeName' => $this->reftypes[$entry['refTypeID']],
                    );
            }
            if (!isset($daily[$day][$entry['refTypeID']]['expense']))
            {
                $daily[$day][$entry['refTypeID']]['expense'] = 0;
                $daily[$day][$entry['refTypeID']]['income'] = 0;
			}

            if ($entry['amount'] < 0)
            {
                $daily[$day][$entry['refTypeID']]['expense'] += $entry['amount'];
                $total[$day]['expense'] += $entry['amount'];
            }
            else
            {
                $daily[$day][$entry['refTypeID']]['income'] += $entry['amount'];
                $total[$day]['income'] += $entry['amount'];
            }
			
			if (!isset($balance[$day]))
			{
				/* Wallet journal is chronoligcally ordered, so we just want the topmost daily entry as that is the "last for that day" */
				$balance[$day] = $entry['balance'];
			}
        }

        $data['daily'] = $daily;
        $data['total'] = $total;
		$data['balance'] = $balance;
		
		return ($data);
	}

}

class Stations
{
    static function getConquerableStationList($contents)
    {    
		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			$output = array();
            
        	foreach ($xml->result->rowset->row as $row)
			{
				$output[(int) $row['stationID']] = (string) $row['stationName'];
			}
			return $output;
		}
		else
		{
			return null;
		}
    }
}

class SkillQueue 
{
	static function getSkillQueue($contents)
	{
        $CI =& get_instance();
	
		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			$output = array();
        	foreach ($xml->result->rowset->row as $row)
			{
				$index = count($output);
				foreach ($row->attributes() as $name => $value)
				{
					$output[$index][(string) $name] = (string) $value;
					if ($name == 'typeID')
					{
						$output[$index]['typeName'] = $CI->eveapi->skilltree[(string) $value]['typeName'];
						$output[$index]['rank'] = $CI->eveapi->skilltree[(string) $value]['rank'];
						$output[$index]['description'] = $CI->eveapi->skilltree[(string) $value]['description'];
					}
				}
			}
			return $output;
		}
		else
		{
			return null;
		}
	}
}

class MY_IndustryJobs extends IndustryJobs
{
	static function statusIDToString($status)
	{
		$mapping = array(
				0 => 'Failed',
				1 => 'Delivered',
				2 => 'Aborted',
				3 => 'GM Aborted',
				4 => 'Inflight Unarchored',
				5 => 'Destroyed');
		return ($mapping[$status]);
	}
	static function activityIDToString($activityID)
	{
		/*
		$mapping = array(
				0 => 'None',
				1 => 'Manufacturing',
				2 => 'Research Technology',
				3 => 'Research Time Production',
				4 => 'Research Material Production',
				5 => 'Copying',
				6 => 'Dublicating',
				7 => 'Reverse Engineering',
				8 => 'Inventing');
		 */
		$mapping = array(
				0 => 'None',
				1 => 'Prod',
				2 => 'Tech',
				3 => 'PE',
				4 => 'ME',
				5 => 'Copy',
				6 => 'Dub',
				7 => 'Rev',
				8 => 'Inv');
		return ($mapping[$activityID]);
	}
}

class AssetList
{

    static function getAssetsFromDB($charid, $limit = array(1 => 1), $connect = 'OR')
    {
        $CI =& get_instance();
        $assets = array();
        $where = '';
        foreach ($limit as $k => $v)
        {
            $where .= "{$k}='{$v}' {$connect} ";
        }
        $where = substr($where, 0, -strlen($connect)-2);
		
        $q = $CI->db->query('
            SELECT 
                assets.itemID as assetItemID,
                assets.locationID,
                assets.characterID,
                assets.flag,
                invTypes.groupID,
                invTypes.typeName,
                invTypes.volume,
                invTypes.typeID,
                assets.quantity,
                invGroups.categoryID,
                invGroups.groupName,
                eveGraphics.icon,
                (SELECT COUNT(itemID) FROM contents WHERE contents.locationItemId=assetItemID) AS contentAmount
            FROM 
                invTypes,
                assets,
                eveGraphics,
                invGroups
            WHERE
                (('.$where.') OR (SELECT COUNT(itemID) FROM contents WHERE contents.locationItemID=assetitemID)>0) AND
                assets.characterID=? AND
                assets.typeID=invTypes.typeID AND
                invTypes.graphicID=eveGraphics.graphicID AND
                invTypes.groupID=invGroups.groupID
            ORDER BY
                assets.locationID,invGroups.categoryID,invTypes.typeID;', $charid);

        foreach ($q->result_array() as $row)
        {
            if ($row['contentAmount'] > 0)
            {
                $c = $CI->db->query('
                    SELECT 
                        itemID as assetItemID,
                        characterID,
                        invTypes.groupID,
                        invTypes.typeName,
                        invTypes.volume,
                        invTypes.typeID,
                        invGroups.categoryID,
                        invGroups.groupName,
                        eveGraphics.icon,
                        quantity,
                        flag
                    FROM
                        invTypes,
                        contents,
                        eveGraphics,
                        invGroups
                    WHERE
                        ('.$where.') AND
                        invTypes.groupID=invGroups.groupID AND
                        invTypes.graphicID=eveGraphics.graphicID AND
                        contents.locationItemID=? AND
                        invTypes.typeID=contents.typeID
                    ORDER BY flag DESC,invTypes.typeID', $row['assetItemID']);
                foreach ($c->result_array() as $content)
                {
                    $row['contents'][] = $content;
                }
            }
            $assets[$row['locationID']][] = $row;
        }
        return $assets;
    }

    static function getAssetList($contents, $characterID = False)
    {
        $CI =& get_instance();

        $output = array();
        $xml = new SimpleXMLElement($contents);
       
        /**
         * FIXME: how do we expire outdated assets?
         */
        if ($characterID)
        {
            $CI->db->query('DELETE FROM assets WHERE characterID=?', $characterID);
            $CI->db->query('DELETE FROM contents WHERE characterID=?', $characterID);
        }

        foreach ($xml->result->rowset->row as $row)
        {
            unset($asset);
            foreach ($row->attributes() as $name => $value)
            {
                $asset[(string) $name] = (string)$value;
            }
            if ($characterID)
            {   
                $asset = array_merge(array('characterID' => $characterID), $asset);
                $insert = array_merge($asset, array($asset['quantity'],$asset['flag'],$asset['singleton']));
                $CI->db->query('
                    INSERT INTO assets 
                    (characterID,itemID,locationID,typeID,quantity,flag,singleton)
                    VALUES
                    (?,?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE
                    quantity=?,flag=?,singleton=?', $insert);
            }
            $asset['contents'] = array();
            if (count($row->rowset->row) > 0)
            {
                $index=0;
                foreach ($row->rowset->row as $contents)
                {
                    foreach ($contents->attributes() as $cname => $cvalue)
                    {
                        $asset['contents'][$index][(string) $cname] = (string)$cvalue;
                    }
                    if ($characterID)
                    {
                        $insert = array_merge(
                                array('characterID' => $characterID, 'locationItemID' => $asset['itemID']), 
                                $asset['contents'][$index], 
                                array($asset['contents'][$index]['quantity'], $asset['contents'][$index]['flag'], $asset['contents'][$index]['singleton'])
                            );

                        $CI->db->query('
                            INSERT INTO contents
                            (characterID, locationItemID, itemID, typeID, quantity, flag, singleton)
                            VALUES
                            (?,?,?,?,?,?,?)
                            ON DUPLICATE KEY UPDATE
                            quantity=?,flag=?,singleton=?', $insert);
                    }
                    $index++;
                }
            }
            $output[$asset['locationID']][] = $asset;
        }
        return ((string) $xml->cachedUntil);
    }
}





?>
