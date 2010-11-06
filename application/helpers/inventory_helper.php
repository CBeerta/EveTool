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


function get_inv_type($type)
{
    if (empty($type))
    {
        return False;
    }
    $CI =& get_instance();
    $CI->load->database();

    $trace = debug_backtrace();
    error_log(date('c')." get_inv_type({$type}) called by: {$trace[0]['file']} -> {$trace[1]['function']}");
    if (($_mc = $CI->cache->get('invtype_'.$type)))
    {
        return ($_mc);
    }
    if (is_numeric($type))
    {
        $type_select = 'typeID';
    }
    else
    {
        $type_select = 'typeName';
    }
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
            invGroups.categoryID,
            eveGraphics.icon,
            (SELECT IF(COUNT(valueInt)>0, valueInt, 1) FROM dgmTypeAttributes WHERE typeID=invTypes.typeID AND attributeID=422) AS techlevel
        FROM 
            invTypes,
            invGroups,
            eveGraphics,
            invCategories 
        WHERE 
            invTypes.{$type_select} = ? AND 
            invTypes.groupID=invGroups.groupID AND 
            invTypes.graphicID=eveGraphics.graphicID AND
            invCategories.categoryID=invGroups.categoryID;"
        , $type);
    $row = $q->row();
    if (count($row) == 1)
    {
        $CI->cache->set('invtype_'.$row->typeID, $row, 0, 604800);
        return ($row);
    }
    else
    {
        return (new emptyInvType);
    }
}
?>
