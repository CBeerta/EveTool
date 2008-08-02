<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Production
{
    public function getBlueprintInfo($blueprintID)
    {   
        $CI =& get_instance();
        $q = $CI->db->query('SELECT * FROM invBlueprintTypes WHERE blueprintTypeID=?', $blueprintID);
        return($q->row_array());
    }

    public function getSkillReq($blueprintID)
    {
        $CI =& get_instance();
        $q = $CI->db->query('
                SELECT 
                    typeReq.typeName, graphics.icon, materials.quantity AS level
                FROM 
                    TL2MaterialsForTypeWithActivity AS materials
                INNER JOIN invTypes AS typeReq ON materials.requiredtypeID = typeReq.typeID
                INNER JOIN invGroups AS typeGroup ON typeReq.groupID = typeGroup.groupID
                INNER JOIN eveGraphics AS graphics ON typeReq.graphicID = graphics.graphicID
                WHERE
                    materials.typeID = ? AND 
                    materials.activity = 1 AND 
                    typeGroup.categoryID = 16
                ORDER BY typeReq.typeName;', $blueprintID);

        $skillReq = array();
        foreach ($q->result_array() as $row)
        {
            $skillReq[] = $row;
        }
        return ($skillReq);
    }

    public function getBlueprint($character, $blueprintID, $me = 0, $pe = False)
    {
        $CI =& get_instance();
        $CI->eveapi->setCredentials(
            $CI->chars[$character]['apiuser'], 
            $CI->chars[$character]['apikey'], 
            $CI->chars[$character]['charid']);

        if (!$pe)
        {
            $charsheet = CharacterSheet::getCharacterSheet($CI->eveapi->getcharactersheet());
            $pe = 0;
            foreach($charsheet['skills'] as $skill)
            {
                if($skill['typeID'] == 3380)
                {
                    $pe = $skill['level'];
                }
            }
        }
        $q = $CI->db->query('
            SELECT 
                typeReq.typeID,
                typeReq.typeName, 
                typeReq.groupID, 
                typeReq.volume,
                typeGroup.categoryID,
                bluePrint.wasteFactor,
                bluePrint.materialModifier,
                materials.quantity AS basequantity,
                IF(typeReq.groupID = 332, materials.quantity, CEIL(materials.quantity * (1 + bluePrint.wasteFactor / 100) ) ) AS quantity, 
                materials.damagePerJob,
                (SELECT blueprintTypeID FROM invBlueprintTypes WHERE productTypeID=typeReq.typeID LIMIT 1) AS isPart
            FROM 
                TL2MaterialsForTypeWithActivity AS materials
	            INNER JOIN invTypes AS typeReq ON materials.requiredtypeID = typeReq.typeID
	            INNER JOIN invGroups AS typeGroup ON typeReq.groupID = typeGroup.groupID
	            INNER JOIN invBlueprintTypes AS bluePrint ON materials.typeID = bluePrint.blueprintTypeID
	            INNER JOIN eveGraphics AS graphics ON typeReq.graphicID = graphics.graphicID
            WHERE 
                materials.typeID = ? AND 
                materials.activity = 1 AND 
                typeGroup.categoryID NOT IN (6, 7, 16)
            ORDER BY 
            	typeReq.typeName;', $blueprintID);
        $data = $totalMineralUsage = array();
        foreach ($q->result_array() as $row)
        {
            $waste  = $row['basequantity']*(($row['wasteFactor']/100)/(1+$me) + 0.25 - 0.05 * $pe);
            $row['requiresPerfect'] = $row['basequantity'] + $waste;
            if (is_numeric($row['isPart']))
            {
                /* FIXME: this isnt currently really recursive */

                $q = $CI->db->query('SELECT me FROM blueprintData WHERE characterID=? AND blueprintTypeID=?', array($CI->chars[$character]['charid'], $row['isPart']));
                if ($q->num_rows() > 0)
                {
                    $row['me'] = $q->row()->me;
                }
                else
                {
                    $row['me'] = 0;
                }

                list($row['partRequires'], $childMats) = Production::getBlueprint($character, $row['isPart'], $row['me'], $pe);
                foreach ($row['partRequires'] as $part)
                {
                    if ($part['groupID'] == 18)
                    {
                        if (empty($totalMineralUsage[$part['typeID']]))
                        {
                            $totalMineralUsage[$part['typeID']]['amount'] = ceil($part['requiresPerfect'] * $row['requiresPerfect']);
                            $totalMineralUsage[$part['typeID']]['volume'] = ceil($part['requiresPerfect'] * $row['requiresPerfect'] * $part['volume']);
                        }
                        else
                        {
                            $totalMineralUsage[$part['typeID']]['amount'] += ceil($part['requiresPerfect'] * $row['requiresPerfect']);
                            $totalMineralUsage[$part['typeID']]['volume'] += ceil($part['requiresPerfect'] * $row['requiresPerfect'] * $part['volume']);
                        }
                    }        
                }
            }
            $data[] = $row;
        }
        return (array($data, $totalMineralUsage));
    }
 


}

?>
