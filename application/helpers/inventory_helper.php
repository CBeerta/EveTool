<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class emptyInvType
{
    public $typeName = 'Unknown Item';
    public $description = 'Unknown Item - There is no Description in the Database for this Item Yet';
    public $volume = 0.0;
    public $mass = 0;
    public $typeID = -1;
    public $groupID = -1;
    public $categoryID = -1;
    public $groupName = 'Unknown';
}

function get_slots($itemNames)
{
    $CI =& get_instance();
    $CI->load->database();
    
    $escaped_names = array();
    foreach ($itemNames as $name)
    {
        $escaped_names[] = mysql_escape_string($name);
    }
    
    $q = $CI->db->query("
        SELECT 
            type.typeName, 
            type.typeID, 
            groups.categoryID,
            eveGraphics.icon,
            TRIM(effect.effectName) AS slot 
        FROM 
            invGroups as groups,
            eveGraphics,
            invTypes AS type
        INNER JOIN dgmTypeEffects   AS typeEffect   ON type.typeID = typeEffect.typeID      
        INNER JOIN dgmEffects       AS effect       ON typeEffect.effectID = effect.effectID 
        WHERE 
            effect.effectName IN ('loPower', 'medPower', 'hiPower', 'rigSlot') AND 
            groups.groupID = type.groupID AND
            type.graphicID = eveGraphics.graphicID AND
            type.typeName IN ('".implode("','", $escaped_names)."');");
    $data = array();
    
    foreach ($q->result() as $row)
    {
        $data[$row->typeName] = (object) array(
            'typeID' => $row->typeID, 
            'slot' => $row->slot, 
            'categoryID' => $row->categoryID,
            'icon' => $row->icon,
            );
    }
    return($data);
}

/**
*
* Load a single invType. This function should be avoided! better to use array_add_invtypes
*
* @access public
* @param string $type typeID or typeName 
*
**/
function get_inv_type($type)
{
    if (empty($type))
    {
        return False;
    }

    $CI =& get_instance();

    if ( !is_array($type) && ($_mc = $CI->cache->get('invtype_'.$type)))
    {
        return ($_mc);
    }

    if (is_numeric($type))
    {
        $where = "invTypes.typeID = {$type}";
    }
    else
    {
        $where = "invTypes.typeName = {$type}";
    }

    if (($invtype =db_load_invtype($where)))
    {
        $CI->cache->set('invtype_'.$invtype->typeID, $invtype, 0, 604800);
    }
    else
    {
        return (new emptyInvType);
    }
}


/**
*
* Insert invTypes into an array $data. Source array has to have a field $typeID
*
* @access public
* @param array $data Array to will
*
**/
function array_add_invtypes(array $data)
{

    if (empty($data))
    {
        return ($data);
    }
    
    $typeid_list = array();
    $ret = array();
    foreach ($data as $row)
    {
        if (isset($row['typeID']))
        {
            $typeid_list[] = $row['typeID'];
        }
    }
    $typeid_list = array_unique($typeid_list);
    sort($typeid_list);

    $invtypes = db_load_invtype("invTypes.typeID IN (".implode(',', $typeid_list).")");

    foreach ($data as $k => $v)
    {
        $ret[$k] = array_merge($v, (array) $invtypes[$v['typeID']]);
    }

    return ($ret);
}

/**
*
* Load invtypes from the database
*
* @access public
* @param string $where where clause to use 
*
**/
function db_load_invtype($where)
{
    $CI =& get_instance();
    $CI->load->database();

    $trace = debug_backtrace();
    error_log(date('c')." db_load_invtype({$where}) called by: {$trace[0]['file']} -> {$trace[2]['function']}");

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
            {$where} AND
            invTypes.groupID=invGroups.groupID AND 
            invCategories.categoryID=invGroups.categoryID;");
    if ($q->num_rows() == 1)
    {
        $row = $q->row();
        return ($row);
    }
    else if ($q->num_rows() >= 1)
    {
        $invtypes = array();
        foreach ($q->result() as $row)
        {
            $invtypes[$row->typeID] = $row;
        }
        return ($invtypes);
    }

    return False;
}
?>
