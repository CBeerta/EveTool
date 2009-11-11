<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Manufacturing extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('production');
    }

    public function index($techLevel)
    {
        $character = $this->character;
        $data['character'] = $character;
        $data['tl'] = $techLevel;

        $q = $this->db->query('
            SELECT 
                productGroup.groupID, 
                productGroup.groupName, 
                category.categoryID,
                blueprintType.typeID, 
                blueprints.blueprintTypeID,
                blueprintType.typename Blueprint,
                productType.typeID, 
                productType.typename Item,        
                productGraphic.icon ItemGraphic,        
                productGroup.groupName ItemGroup,        
                productCategory.categoryName ItemCategory,        
                blueprints.productionTime,        
                blueprints.techLevel,        
                blueprints.researchProductivityTime,        
                blueprints.researchMaterialTime,        
                blueprints.researchCopyTime,        
                blueprints.researchTechTime,        
                blueprints.productivityModifier,        
                blueprints.materialModifier,        
                blueprints.wasteFactor,        
                blueprints.maxProductionLimit  
            FROM 
                invBlueprintTypes AS blueprints  
                INNER JOIN invTypes AS blueprintType        ON blueprints.blueprintTypeID = blueprintType.typeID  
                INNER JOIN invTypes AS productType          ON blueprints.productTypeID   = productType.typeID  
                INNER JOIN invGroups AS productGroup        ON productType.groupID        = productGroup.groupID  
                INNER JOIN invCategories AS productCategory ON productGroup.categoryID    = productCategory.categoryID  
                INNER JOIN eveGraphics AS blueprintGraphic  ON blueprintType.graphicID    = blueprintGraphic.graphicID  
                INNER JOIN eveGraphics AS productGraphic    ON productType.graphicID      = productGraphic.graphicID
                INNER JOIN invCategories AS category        ON productGroup.categoryID   = category.categoryID
            WHERE 
                blueprintType.published = 1 
                AND ( category.categoryID=6 OR category.categoryID=7 OR category.categoryID=18 OR category.categoryID=8 )
            ORDER BY
                category.categoryID, techLevel, groupID, productType.typeName');
        
        foreach ($q->result() as $row)
        {
            $data['blueprints'][$row->techLevel][$row->categoryID][$row->groupName][$row->blueprintTypeID] = $row->Item;
        }
		
        $template['content'] = $this->load->view('blueprints', $data, True);
        $this->load->view('maintemplate', $template);
        return;
    }
    
    public function redirect($blueprintID = 24699)
    {
        $q = $this->db->query('SELECT groupName FROM invGroups WHERE invGroups.categoryID=6 OR invGroups.categoryID=7 OR invGroups.categoryID=18 OR invGroups.categoryID=8;');
        
        foreach ($q->result() as $row)
        {
            $groupName = str_replace(' ', '_', $row->groupName);
            $blueprintID = is_numeric($this->input->post($groupName)) ? $this->input->post($groupName) : $blueprintID;
        }
        
        redirect("manufacturing/detail/{$blueprintID}");
    }
   
    public function update($blueprintID)
    {
        $character = $this->character;
        $data = array();
        
		list($data['have']) = Production::getMaterials(AssetList::getAssetsFromDB($this->chars[$character]['charid']));
        if (is_numeric($this->input->post('me')))
        {
            $me = $this->input->post('me');
            $q = $this->db->query('
                INSERT INTO blueprintData
                (characterID, blueprintTypeID, me) VALUES (?,?,?)
                ON DUPLICATE KEY UPDATE me=?', array($this->chars[$character]['charid'], $blueprintID, $me, $me));
        }
        else
        {
            $q = $this->db->query('SELECT me FROM blueprintData WHERE characterID=? AND blueprintTypeID=?', array($this->chars[$character]['charid'], $blueprintID));
            $res = $q->row();
            if ($q->num_rows() > 0)
            {
                $me = $res->me;
            }
            else
            {
                $me = 0;
            }
        }
        $amount = is_numeric($this->input->post('amount')) ? $this->input->post('amount') : 1;
                
        $data['me'] = $me;
        $data['totalVolume'] = $data['totalMineralVolume'] = $data['totalMineralVolumeValue'] = $data['totalValue'] = 0;

        $pe = !get_user_config($this->Auth['user_id'], 'use_perfect') ? False : 5;
        
        list ($components, $totalMineralUsage, $totalMoonGoo) = Production::getBlueprint($character, $blueprintID, $me, $data['have'], $pe);

		$typeIds = array();
		foreach($components as $row) 
        {
			array_push($typeIds, $row['typeID']);
		}
		foreach($totalMineralUsage as $k => $v) 
        {
			array_push($typeIds, $k);
		}
		$regionID = !get_user_config($this->Auth['user_id'], 'market_region') ? 10000067 : get_user_config($this->Auth['user_id'], 'market_region');
		
    	$prices = $this->evecentral->getPrices($typeIds, $regionID);

		foreach ($components as $row)
        {
            $req = ceil($row['requiresPerfect'] * $amount);
 	   
	 	    $data['price'][$row['typeID']] = $prices[$row['typeID']]['buy']['median'];
		    $data['value'][$row['typeID']] = $prices[$row['typeID']]['buy']['median'] * $req;
	 	    $data['req'][$row['typeID']] = $req;
	        $data['totalVolume'] += $req * $row['volume'];
            $data['totalValue'] += $req  * $prices[$row['typeID']]['buy']['median'];
		}
        foreach ($totalMineralUsage as $k => $v)
        {
        	$data['price'][$k] = $prices[$k]['buy']['median'];
        	$data['totalMineralValue'][$k] = $v['amount'] * $amount * $prices[$k]['buy']['median'];
            $data['totalMineralUsage'][$k] = $v['amount'] * $amount;
            $data['totalMineralVolume'] += $v['volume'] * $amount;
            $data['totalMineralVolumeValue'] += $v['amount'] * $amount * $prices[$k]['buy']['median'];
        }
        echo json_encode($data);
        exit;
    }

    public function detail($blueprintID)
    {
        $character = $this->character;
        
        $data['character'] = $character;
        $data['blueprintID'] = $blueprintID;
        $data['content'] = '';

        $q = $this->db->query("
                SELECT 
                    invTypes.typeID,
                    invTypes.typeName,
                    invTypes.groupID,
                    invGroups.categoryID,
                    invTypes.description,
                    eveGraphics.icon
                FROM 
                    invTypes, 
                    eveGraphics,
                    invGroups,
                    invBlueprintTypes 
                WHERE 
                    invTypes.groupID = invGroups.groupID AND
                    eveGraphics.graphicID = invTypes.graphicID AND
                    invTypes.typeID = invBlueprintTypes.productTypeID AND 
                    invBlueprintTypes.blueprintTypeID = ?", $blueprintID);
        $data['product'] = $q->row();
        $data['caption'] = get_inv_type($blueprintID)->typeName;
        
        $allowsFor = $data['data'] = array();

        $index = 0;
        list ($components, $data['totalMineralUsage'], $totalMoonGoo) = Production::getBlueprint($character, $blueprintID, 0);
		
        foreach ($components as $row)
        {
            $data['data'][$index] = $row;
            $index++;
        }
        
        $data['skillreq'] = Production::getSkillReq($blueprintID);
        $data['content'] = $this->load->view('manufacturing', $data, True);
        $this->load->view('maintemplate', $data);
    }
}
?>
