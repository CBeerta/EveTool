<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(BASEPATH.'../eveapi/eveapi/class.api.php');
require_once(BASEPATH.'../eveapi/eveapi/class.alliancelist.php');
require_once(BASEPATH.'../eveapi/eveapi/class.balance.php');
require_once(BASEPATH.'../eveapi/eveapi/class.charactersheet.php');
require_once(BASEPATH.'../eveapi/eveapi/class.charselect.php');
require_once(BASEPATH.'../eveapi/eveapi/class.corporationsheet.php');
require_once(BASEPATH.'../eveapi/eveapi/class.generic.php');
require_once(BASEPATH.'../eveapi/eveapi/class.membertrack.php');
require_once(BASEPATH.'../eveapi/eveapi/class.transactions.php');
require_once(BASEPATH.'../eveapi/eveapi/class.walletjournal.php');
require_once(BASEPATH.'../eveapi/eveapi/class.starbases.php');

class EveApi Extends Api {

    var $reftypes;
    var $skilltree;
    var $stationlist;

    function __construct($params)
    {
        $CI =& get_instance();

        if (!empty($params['cachedir']))
        {
            $this->cache(true);
            $this->setCacheDir($params['cachedir']);
        }

        $this->reftypes = RefTypes::getRefTypes($this->getRefTypes());
        $skilltree = Generic::getSkillTree($this->getSkillTree());
        $this->stationlist = Stations::getConquerableStationList($this->getConquerableStationList());

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

    public function getAssetList($timeout = 1440, $cachethis = null)
    {
		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getAssetList: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1440;
		}

		if ($cachethis != null && !is_bool($cachethis))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getAssetList: Non-bool value of cachethis param, reverting to default value");
			}
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
			if ($this->debug)
			{
				$this->addMsg("Error","getConquerableStationList: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 1500;
		}

		if ($cachethis != null && !is_bool($cachethis))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getConquerableStationList: Non-bool value of cachethis param, reverting to default value");
			}
			$cachethis = null;
		}
		$contents = $this->retrieveXml("/eve/ConquerableStationList.xml.aspx", $timeout, null, $cachethis);
		return $contents;
	}

	public function getMarketOrders($timeout = 60, $cachethis = null)
	{
		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getMarketOrders: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 60;
		}

		if ($cachethis != null && !is_bool($cachethis))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getMarketOrders: Non-bool value of cachethis param, reverting to default value");
			}
			$cachethis = null;
		}

        $cachePath = array();
        $cachePath[0] = 'userID';
        $cachePath[1] = 'characterID';
        $cachePath[2] = 'accountKey';

		$contents = $this->retrieveXml("/char/MarketOrders.xml.aspx", $timeout, $cachePath, $cachethis);
		return $contents;
	}

	public function getIndustryJobs($timeout = 60, $cachethis = null)
	{
		if (!is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getIndustryJobs: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = 60;
		}

		if ($cachethis != null && !is_bool($cachethis))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getIndustryJobs: Non-bool value of cachethis param, reverting to default value");
			}
			$cachethis = null;
		}

        $cachePath = array();
        $cachePath[0] = 'userID';
        $cachePath[1] = 'characterID';
        $cachePath[2] = 'accountKey';

		$contents = $this->retrieveXml("/char/IndustryJobs.xml.aspx", $timeout, $cachePath, $cachethis);
		return $contents;
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

class IndustryJobs
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
				3 => 'PEn',
				4 => 'ME',
				5 => 'Copy',
				6 => 'Dub',
				7 => 'Rev',
				8 => 'Inv');
		return ($mapping[$activityID]);
	}
	static function getIndustryJobs($contents)
	{
		$output = array();

		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			$index = 0;
			foreach ($xml->result->rowset->row as $row)
			{
				foreach ($row->attributes() as $name => $value)
                {
					$output[$index][(string) $name] = (string) $value;
				}
				$index++;
			}
		}
		else
		{
			return null;
		}
		return $output;
	}
}

class MarketOrders
{
	static function getMarketOrders($contents)
	{
		$output = array();

		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			$index = 0;
			foreach ($xml->result->rowset->row as $row)
			{
				foreach ($row->attributes() as $name => $value)
                {
					$output[$index][(string) $name] = (string) $value;
				}
				$index++;
			}
		}
		else
		{
			return null;
		}
		return $output;
	}
}

class AssetList
{
    public function getAssetsFromDB($charid, $limit = array(1 => 1), $connect = 'OR')
    {
        $CI =& get_instance();
        $assets = array();
        $where = '';
        foreach ($limit as $k => $v)
        {
            $where .= "{$k}={$v} {$connect} ";
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
                (SELECT COUNT(itemID) FROM contents WHERE contents.locationItemId=assetItemID) AS contentAmount
            FROM 
                invTypes,
                assets,
                invGroups
            WHERE
                (('.$where.') OR (SELECT COUNT(itemID) FROM contents WHERE contents.locationItemID=assetitemID)>0) AND
                assets.characterID=? AND
                assets.typeID=invTypes.typeID AND
                invTypes.groupID=invGroups.groupID
            ORDER BY
                assets.locationID,invGroups.categoryID,invTypes.typeID;', $charid);

        foreach ($q->result_array() as $row)
        {
            if ($row['contentAmount'] > 0)
            {
                $c = $CI->db->query('
                    SELECT 
                        itemID,
                        characterID,
                        invTypes.groupID,
                        invTypes.typeName,
                        invTypes.volume,
                        invTypes.typeID,
                        invGroups.categoryID,
                        quantity,
                        flag
                    FROM
                        invTypes,
                        contents,
                        invGroups
                    WHERE
                        ('.$where.') AND
                        invTypes.groupID=invGroups.groupID AND
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
        return($output);
    }
}
?>
