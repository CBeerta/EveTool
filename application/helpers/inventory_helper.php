<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class emptyInvType
{
    var $typeName = 'Unknown Item';
    var $description = 'Unknown Item - There is no Description in the Database for this Item Yet';
    var $volume = 0.0;
    var $mass = 0;
    var $typeID = -1;
    var $groupID = -1;
    var $categoryID = -1;
    var $groupName = 'Unknown';
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


function getInvType($type)
{
    if (empty($type))
    {
        return False;
    }
    $CI =& get_instance();
    $CI->load->database();
    
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
            /* (SELECT valueInt FROM dgmTypeAttributes WHERE typeID=invTypes.typeID AND attributeID=422) AS techlevel */
            invTypes.typeName,
            invTypes.typeID,
            invGroups.groupName,
            invTypes.groupID,
            invTypes.description,
            invTypes.volume,
            invTypes.mass,
            invCategories.categoryName,
            invGroups.categoryID,
            eveGraphics.icon
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
    if (count($row) > 0)
    {
        return ($row);
    }
    else
    {
        return (new emptyInvType);
    }
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
        $q = $CI->db->query('SELECT typeID FROM invTypes '.$where);
    }
    else
    {
        $q = $CI->db->query('SELECT typeID FROM invTypes where groupID = ?', $groupID);
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

?>
