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
                    typeActivityMaterials AS materials
                INNER JOIN invTypes AS typeReq ON materials.requiredtypeID = typeReq.typeID
                INNER JOIN invGroups AS typeGroup ON typeReq.groupID = typeGroup.groupID
                INNER JOIN eveGraphics AS graphics ON typeReq.graphicID = graphics.graphicID
                WHERE
                    materials.typeID = ? AND 
                    materials.activityID = 1 AND 
                    typeGroup.categoryID = 16
                ORDER BY typeReq.typeName;', $blueprintID);

        $skillReq = array();
        foreach ($q->result_array() as $row)
        {
            $skillReq[] = $row;
        }
        return ($skillReq);
    }
	
	/**
	 * Extract Materials from a "AssetList::getAssetsFromDB" Array
	 *
	 **/
	static function getMaterials($assets)
	{
		$CI =& get_instance();
		$CI->load->database();
		
		$q = $CI->db->query('
			SELECT 
				typeID 
			FROM 
				invTypes, 
				invGroups 
			WHERE 
				invTypes.groupID=invGroups.groupID AND 
				(
					invGroups.categoryID=4 OR 
					invGroups.categoryID=6 OR
					invGroups.categoryID=17
				);
			');
		
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

    public function getBlueprint($character, $blueprintID, $me = 0, $have = False, $pe = False)
    {
        $CI =& get_instance();
        if (!$pe)
        {
            $pe = $CI->eveapi->get_skill_level(3388);
        }

        if (!$have)
        {
            $have = array();
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
                graphics.icon,
				bluePrint.techLevel,
                IF(typeReq.groupID = 332, materials.quantity, CEIL(materials.quantity * (1 + bluePrint.wasteFactor / 100) ) ) AS quantity, 
                materials.damagePerJob,
                (SELECT blueprintTypeID FROM invBlueprintTypes WHERE productTypeID=typeReq.typeID LIMIT 1) AS isPart
            FROM 
                typeActivityMaterials AS materials
	            INNER JOIN invTypes AS typeReq ON materials.requiredtypeID = typeReq.typeID
	            INNER JOIN invGroups AS typeGroup ON typeReq.groupID = typeGroup.groupID
	            INNER JOIN invBlueprintTypes AS bluePrint ON materials.typeID = bluePrint.blueprintTypeID
	            INNER JOIN eveGraphics AS graphics ON typeReq.graphicID = graphics.graphicID
            WHERE 
                materials.typeID = ? AND 
                materials.activityID = 1 AND 
                typeGroup.categoryID NOT IN (/*6, */ 7, 16)
            ORDER BY 
            	typeReq.typeName;', $blueprintID);

        $data = $totalMineralUsage = array();
				
        foreach ($q->result_array() as $row)
        {
            $waste  = round($row['basequantity']*(($row['wasteFactor']/100)/(1+$me) + 0.25 - 0.05 * $pe));
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

                list($row['partRequires'], $childMats) = Production::getBlueprint($character, $row['isPart'], $row['me'], $have, $pe);

                if (!isset($have[$row['typeID']]))
                {
                    $have[$row['typeID']] = 0;
                }

                foreach ($row['partRequires'] as $part)
                {
                    if ($part['groupID'] == 18 || $part['groupID'] == 429)
                    {
                        $need = $row['requiresPerfect'] - $have[$row['typeID']];

                        if (empty($totalMineralUsage[$part['typeID']]))
                        {
                            $totalMineralUsage[$part['typeID']] = $part;
                            $totalMineralUsage[$part['typeID']]['amount'] = round($part['requiresPerfect'] * $need);
                            $totalMineralUsage[$part['typeID']]['volume'] = round($part['requiresPerfect'] * $need * $part['volume']);
                        }
                        else
                        {
                            $totalMineralUsage[$part['typeID']]['amount'] += round($part['requiresPerfect'] * $need);
                            $totalMineralUsage[$part['typeID']]['volume'] += round($part['requiresPerfect'] * $need * $part['volume']);
                        }
                    }        
                }
            }
			
			if ($row['requiresPerfect'] > 0) 
			{
				$data[] = $row;
			}
        }
        return (array($data, $totalMineralUsage));
    }
}
?>
