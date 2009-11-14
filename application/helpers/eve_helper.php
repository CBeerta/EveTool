<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * These Functions are here to completely Trash the MySQL database.
 *
 * @todo Some Caching would be nice.
 * @package eve_helper
 */

/**
 * Convert a Timestamp from the Eve Api to something human readable
 * 
 * @access public
 * @param  string
 * @param  string
 */
function api_time_print($time, $format = 'D d.m.Y H:i')
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


/**
 * Return the Duration from Now till $endTime
 * 
 * @access public
 * @param  string
 */
function api_time_to_complete($endTime)
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
		return("Done: ".date('D, j M Y G:i', $end));
	}
	
    $info = array();
	if ($diff>86400)
	{
	    $info['d'] = ($diff - ($diff % 86400)) / 86400;
	    $diff = $diff % 86400;
	}
	if ($diff > 3600)
	{
	    $info['h'] = ($diff - ($diff % 3600)) / 3600;
	    $diff = $diff % 3600;
	}
	if ($diff > 60)
	{
	    $info['m'] = ($diff - ($diff % 60)) / 60;
	    $diff = $diff % 60;
	}
	$str = '';
	foreach ($info as $k => $v)
	{
	    if ($v > 0)
	    {
	        $str .= "$v$k ";
	    }
	}
	return (trim($str));
}


function surrounding_systems($system, $maxDepth = 1, $currentDepth = 1)
{
    $CI =& get_instance();
    $CI->load->database();
	
	$q = $CI->db->query('
		SELECT 
			a.solarSystemName 
		FROM 
			mapSolarSystems a, 
			mapSolarSystems b, 
			mapSolarSystemJumps j 
		WHERE 
			a.solarsystemID = j.fromSolarSystemID AND 
			b.solarSystemID = j.toSolarSystemID AND 
			b.solarSystemName = ?;', $system);
			
	
	foreach ($q->result_array()  as $row)
	{
		$systems[] = $row['solarSystemName'];
		if ($currentDepth < $maxDepth && $row['solarSystemName'] != $system)
		{
			$systems = array_merge($systems, surroundingSystems($row['solarSystemName'], $maxDepth, $currentDepth + 1));
		}
	}
	
	return (array_unique($systems));
}

/**
 * Return the Region Name for a $regionID
 * 
 * @access public
 * @param  string
 *
 * @todo Convert to return a html snippet like locationid_snippet
**/
function regionid_to_name($regionID)
{
    if (empty($regionID))
    {
        return False;
    }
    $CI =& get_instance();
    $CI->load->database();

    $q = $CI->db->query('SELECT * FROM mapRegions WHERE regionID = ?;', $regionID);
    $row = $q->row();
    if (count($row)>0)
    {
        return ($row->regionName);
    }
    return 'Unknown';
}

/**
 * Return the Location as a HTML snippet
 * 
 * @access public
 * @param  string
**/
function locationid_to_name($locationID)
{

    if (empty($locationID))
    {
        return False;
    }
    
    $CI =& get_instance();
    $CI->load->database();
    
    $q = $CI->db->query('
		SELECT 
			* 
		FROM 
			eveNames 
		WHERE 
			itemID = ? LIMIT 1;', $locationID);
			
    $row = $q->result_array();
    if (count($row)>0)
    {
        $loc = $row[0];
        $displayName = $loc['itemName'];
    }
    else
    {
		//FIXME: This is probably a POS somewhere?
        return ('Unknown');
	}

    if (!empty($CI->eveapi->stationlist[$locationID]))
    {
        $displayName = $CI->eveapi->stationlist[$locationID]['stationName'];
    }
	
    return($displayName);	
}


function get_character_portrait($name, $size = 64)
{
    if (is_numeric($name))
    {
        $id = $name;
    }
    else
    {
        $id = get_character_id($name);
    }
    
    $CI =& get_instance();
    
    $url  = '<img src="';
    if ($size < 64) 
    {
        $url .= site_url("files/cache/char/{$id}/64/char.jpg");
    }
    else
    {
        $url .= site_url("files/cache/char/{$id}/{$size}/char.jpg");
    }
    $url .= "\" width=\"{$size}\" height=\"{$size}\"";
    if (!empty($CI->eveapi->corp_members[$id]))
    {
        $i = $CI->eveapi->corp_members[$id];
        $url .= " name=\"{$i->name}\"";
        //$url .= " title=\"Name: {$i->name}\n\nTitle: {$i->title}\"";
        $url .= " title=\"{$i->name}\"";
    }
    $url .= ">";
    return ($url);
}

function get_character_id($name)
{
    $CI =& get_instance();
    $CI->load->database();
    
    $q = $CI->db->query('SELECT characterID FROM kb_characters WHERE name=?', $name);
    
    if ($q->num_rows() <= 0)
    {
        return False;
    }
    $r = $q->row();
    if ($r->characterID > 0)
    {
        return ($r->characterID);
    }
    $CI->load->library('eveapi', array('cachedir' => '/var/tmp'));
    $data = CharacterID::getCharacterID($CI->eveapi->getCharacterID($name));
    
    $CI ->db->query('UPDATE kb_characters SET characterID = ? WHERE name = ?;', array($data[0]['characterID'], $name));
    //$CI ->db->query("INSERT INTO kb_characters (name, characterID)  VALUES (?, ?) ON DUPLICATE KEY UPDATE characterID = ?;", array($name, $data[0]['characterID'], $data[0]['characterID']));
    return ($data[0]['characterID']);
}

function get_icon_url($type, $size = 64, $background = 'black')
{
    if (empty($type))
    {
        return False;
    }
    else if (is_array($type))
    {
        $row = (object) $type;    
    }
    else if (is_object($type))
    {
        $row = $type;
    }
	
    if (!empty($row->categoryID) && $row->categoryID == 6)
    {
        // Ship
        return ("/files/itemdb/types/shiptypes_png/{$size}_{$size}/{$row->typeID}.png");
    }
    else if (!empty($row->categoryID) && $row->categoryID == 18)
    {
        //drone
        return ("/files/itemdb/types/dronetypes_png/{$size}_{$size}/{$row->typeID}.png");
    }
    else if (!empty($row->categoryID) && $row->categoryID == 23)
    {
        //structure
        return ("/files/itemdb/types/structuretypes_png/{$size}_{$size}/{$row->typeID}.png");
    }
    else if (!empty($row->categoryID) && $row->categoryID == 9)
    {
        //blueprint
        return ("/files/itemdb/blueprints/blueprints/64_64/{$row->typeID}.png");
    }
    else if (isset($row->icon) && !empty($row->icon))
    {
        return ("/files/itemdb/icons/icons_items_png/{$size}_{$size}/icon{$row->icon}.png");
    }
	else if (!empty($row->stationTypeID))
	{
		//Station
		return ("/files/itemdb/types/stationtypes_png/{$size}_{$size}/{$row->stationTypeID}.png");
	}
    else
    {
        return ("/files/itemdb/icons/icons_items_png/{$size}_{$size}/icon07_15.png");
    }
}


function slot_icon($flag)
{
    switch (True)
    {
        case in_array($flag, range(11,18)): //low
            return(array('/files/itemdb/icons/icons_items_png/64_64/icon08_09.png', 'Low Slot'));
        case in_array($flag, range(19,26)): // med
            return(array('/files/itemdb/icons/icons_items_png/64_64/icon08_10.png', 'Medium Slot'));
        case in_array($flag, range(27,34)): // high
            return(array('/files/itemdb/icons/icons_items_png/64_64/icon08_11.png', 'High Slot'));
        case in_array($flag, range(92,99)): // rig
            return(array('/files/itemdb/icons/icons_items_png/32_32/icon22_24.png', 'Rig'));
        case ($flag == 87): // dronebay
            return(array('/files/itemdb/icons/icons_items_png/64_64/icon02_10.png', 'Dronebay'));
        default: //probably Cargo
            return(array('/files/itemdb/icons/icons_items_png/64_64/icon03_13.png', 'Cargo'));
    }
}

function get_user_config($acctID, $keyName)
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

function set_uset_config($acctID, $keyName, $value)
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

function is_public()
{
    $CI =& get_instance();
    $page = implode('_', array_slice($CI->uri->segment_array(), 0, -1));

    $q = $CI->db->query('
        SELECT 
            asc_apikeys.apiUser,
            asc_apikeys.apiFullKey,
            asc_apikeys.acctID
        FROM
            asc_apikeys,
            asc_characters
        WHERE
            asc_apikeys.apiuser = asc_characters.apiuser AND
            asc_characters.characterName = ?
        LIMIT 1', $CI->uri->rsegment($CI->uri->total_segments()));

    $row = $q->row();

    if ($q->num_rows() > 0 && getUserConfig($row->acctID, $page) !== False)
    {
        return ($row);
    }
    else
    {
        return (False);
    }
}

?>
