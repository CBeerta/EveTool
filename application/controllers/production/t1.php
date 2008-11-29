<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class T1 extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('production');
    }

    public function index($character = False)
    {
        $character = urldecode($character);
        if (!in_array($character, array_keys($this->chars)))
        {
            die("Could not find matching char {$character}");
        }
        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);
        $data['character'] = $character;

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
                blueprints.chanceOfReverseEngineering,        
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
                AND ( category.categoryID=6 OR category.categoryID=7  OR category.categoryID=18 )
            ORDER BY
                category.categoryID, techLevel, groupID, productType.typeName');
        
        foreach ($q->result() as $row)
        {
            $data['t'.$row->techLevel][$row->categoryID][$row->groupName][$row->blueprintTypeID] = $row->Item;
        }
        
        $template['content'] = $this->load->view('production/t1_blueprints', $data, True);
        $this->load->view('maintemplate', $template);
        return;
    }
   
    public function update($character, $blueprintID)
    {
        $character = urldecode($character);
        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);
        $data = array();
        
        list($data['have']) = getMaterials(array(18, 754, 873), AssetList::getAssetsFromDB($this->chars[$character]['charid']));
        
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

        $pe = !getUserConfig($this->Auth['user_id'], 'use_perfect') ? False : 5;
        
        list ($components, $totalMineralUsage) = Production::getBlueprint($character, $blueprintID, $me, $data['have'], $pe);
        
		$typeIds = array();
		foreach($components as $row) 
        {
			array_push($typeIds, $row['typeID']);
		}
		foreach($totalMineralUsage as $k => $v) 
        {
			array_push($typeIds, $k);
		}
		$regionID = !getUserConfig($this->Auth['user_id'], 'market_region') ? 10000067 : getUserConfig($this->Auth['user_id'], 'market_region');
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

    public function detail($character, $blueprintID = False)
    {
        $character = urldecode($character);
        if (!in_array($character, array_keys($this->chars)))
        {
            die("Could not find matching char {$character}");
        }

        $q = $this->db->query('SELECT groupName FROM invGroups WHERE invGroups.categoryID=6 OR invGroups.categoryID=7 OR invGroups.categoryID=18;');
        foreach ($q->result() as $row)
        {
            $groupName = str_replace(' ', '_', $row->groupName);
            $blueprintID = is_numeric($this->input->post($groupName)) ? $this->input->post($groupName) : $blueprintID;
        }
        $data['character'] = $character;
        $data['blueprintID'] = $blueprintID;
        $data['content'] = '';

        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);

        $q = $this->db->query('SELECT * FROM invTypes, invBlueprintTypes WHERE invTypes.typeID=invBlueprintTypes.productTypeID AND invBlueprintTypes.blueprintTypeID=?', $blueprintID);
        $data['product'] = $q->row();
        $data['caption'] = getInvType($blueprintID)->typeName;
        
        $allowsFor = $data['data'] = array();

        $index = 0;
        list ($components, $data['totalMineralUsage']) = Production::getBlueprint($character, $blueprintID, 0);
        foreach ($components as $row)
        {
            $data['data'][$index] = $row;
            $index++;
        }
        
        $data['skillreq'] = Production::getSkillReq($blueprintID);
        $data['content'] = $this->load->view('production/t1', $data, True);
        $this->load->view('maintemplate', $data);
    }
}
?>
