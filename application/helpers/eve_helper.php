<?php
/**
 * 
 * These Functions are here to completely Trash the MySQL database.
 *
 * FIXME: Some Caching would be nice.
 */

class emptyInvType
{
    var $typeName = 'Unknown Item';
    var $description = 'Unknown Item - There is no Description in the Database for this Item Yet';
    var $volume = 0.0;
    var $mass = 0;
}


function apiTimePrettyPrint($time, $format = 'r')
{
    if (is_string($time))
    {
    	$time = strtotime($time.' +0000');
    }
    else
    {
        return $time;
    }
    return (date($format, $time));
}


function timeToComplete($endTime)
{
	$format = '%Y-%m-%d %H:%M:%S';

    if (is_string($endTime))
    {
    	$end = strtotime($endTime.' +0000');
    }
    else
    {
        $end = $endTime;
    }

	$diff = ($end - gmmktime());
	if ($diff <= 0)
	{
		return('Done');
	}
	
    $info = array();
	if ($diff>86400)
	{
	    $info['Day(s)'] = ($diff - ($diff % 86400)) / 86400;
	    $diff = $diff % 86400;
	}
	if ($diff > 3600)
	{
	    $info['Hour(s)'] = ($diff - ($diff % 3600)) / 3600;
	    $diff = $diff % 3600;
	}
	if ($diff > 60)
	{
	    $info['Minute(s)'] = ($diff - ($diff % 60)) / 60;
	    $diff = $diff % 60;
	}
	$str = '';
	foreach ($info as $k => $v)
	{
	    if ($v > 0)
	    {
	        $str .= "$v $k, ";
	    }
	}
	return (substr($str, 0, -2));
}
function getMaterials($groupID, $assets)
{
    $CI =& get_instance();
    $CI->load->database();
   
    if (is_array($groupID))
    {
        $where = 'WHERE (';
        foreach ($groupID as $id)
        {
            $where .= "groupID={$id} OR ";
        }
        $where = substr($where, 0, strlen($where)-4).')';
        $q = $CI->db->query('SELECT typeID FROM eve.invTypes '.$where);
    }
    else
    {
        $q = $CI->db->query('SELECT typeID FROM eve.invTypes where groupID = ?', $groupID);
    }
    $typeIDList = array();
    foreach ($q->result() as $row)
    {
        $typeIDList[] = $row->typeID;
    }
    
    $totalMaterials = array();
    foreach ($typeIDList as $typeID) 
    {
        $totalMaterials[$typeID] = 0;
    }

    foreach ($assets as $locitems)
    {   
        foreach ($locitems as $asset)
        {
            if (in_array($asset['typeID'], $typeIDList))
            {
                $totalMaterials[$asset['typeID']] += $asset['quantity'];
            }
            if (isset($asset['contents']))
            {
                foreach ($asset['contents'] as $content)
                {
                    if (in_array($content['typeID'], $typeIDList))
                    {
                        $totalMaterials[$content['typeID']] += $content['quantity'];
                    }
                }
            }
        }
    }
    return(array($totalMaterials, $typeIDList));
}

function regionIDToName($regionID)
{
    if (empty($regionID))
    {
        return False;
    }
    $CI =& get_instance();
    $CI->load->database();

    $q = $CI->db->query('SELECT * FROM eve.mapRegions WHERE regionID = ?;', $regionID);
    $row = $q->row();
    if (count($row)>0)
    {
        return ($row->regionName);
    }
    return 'Unknown';
}

function locationIDToName($locationID)
{
    if (empty($locationID))
    {
        return False;
    }
    $CI =& get_instance();
    $CI->load->database();

    $q = $CI->db->query('SELECT * FROM eve.eveNames WHERE itemID = ?;', $locationID);
    $row = $q->row();
    if (count($row)>0)
    {
        return ($row->itemName);
    }
    else
    {
        return ($CI->eveapi->stationlist[$locationID]);
    }
}


function getInvType($typeID)
{
    if (empty($typeID))
    {
        return False;
    }
    $CI =& get_instance();
    $CI->load->database();

    $q = $CI->db->query('SELECT * FROM eve.invTypes WHERE typeID = ?;', $typeID);
    $row = $q->row();
    if (count($row) > 0)
    {
        return ($row);
    }
    else
    {
        return (new emptyInvType);
    }
}

function getIconUrl($typeID, $size = 64, $background = 'black')
{
    if (empty($typeID))
    {
        return False;
    }
    $CI =& get_instance();
    $CI->load->database();

    $q = $CI->db->query('
        SELECT icon,invGroups.categoryID,typeID FROM 
            eve.invTypes,
            eve.eveGraphics,
            eve.invGroups
        WHERE 
            invTypes.typeID = ? AND 
            invTypes.graphicID=eveGraphics.graphicID AND
            invTypes.groupID = invGroups.groupID', $typeID);
    $row = $q->row();
    
    if (!empty($row->categoryID) && $row->categoryID == 6)
    {
        // Ship
        return ("/files/cache/itemdb/shiptypes/".($size)."_".($size)."/{$row->typeID}.png");
    }
    else if (!empty($row->categoryID) && $row->categoryID == 9)
    {
        //blueprint
        return ("/files/cache/itemdb/blueprinttypes/64_64/{$row->typeID}.png");
    }
    else if (isset($row->icon) && !empty($row->icon))
    {
        return ("/files/cache/itemdb/{$background}/{$size}_{$size}/icon{$row->icon}.png");
    }
    else
    {
        return ("/files/cache/itemdb/{$background}/{$size}_{$size}/icon07_15.png");
    }
}


function slotIcon($flag)
{
    if ($flag >= 11 && $flag <= 18)
    {
        // low
        return(array('/files/cache/itemdb/black/32_32/icon08_09.png', 'Low Slot'));
    }
    else if ($flag >= 19 && $flag <= 26)
    {
        // med
        return(array('/files/cache/itemdb/black/32_32/icon08_10.png', 'Medium Slot'));
    }
    else if ($flag >= 27 && $flag <= 34)
    {
        // hight
        return(array('/files/cache/itemdb/black/32_32/icon08_11.png', 'High Slot'));
    }
    else if ($flag == 87)
    {
        // dronebay
        return(array('/files/cache/itemdb/black/32_32/icon02_10.png', 'Dronebay'));
    }
    else if ($flag >= 92 && $flag <= 99)
    {
        // rig
        return(array('/files/cache/itemdb/black/32_32/icon22_24.png', 'Rig'));
    }
    else
    {
        // probably cargo
        return(array('/files/cache/itemdb/black/32_32/icon03_13.png', 'Cargo'));
    }
}

function getUserConfig($acctID, $keyName)
{
    $CI =& get_instance();
    $q = $CI->db->query('SELECT value FROM config WHERE acctID=? AND keyname=?', array($acctID, $keyName));
    if ($q->num_rows() > 0)
    {
        return ($q->row()->value);
    }
    else
    {
        return False;
    }
}
function setUserConfig($acctID, $keyName, $value)
{
    $CI =& get_instance();
    $q = $CI->db->query('
            INSERT INTO config
            (acctID,keyname,value) VALUES (?,?,?)
            ON DUPLICATE KEY UPDATE
            value=?;', array($acctID, $keyName, $value, $value));
    if ($CI->db->affected_rows() > 0)
    {
        return True;
    }
    else
    {
        return False;
    }
}


?>
