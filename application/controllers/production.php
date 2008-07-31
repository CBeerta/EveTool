<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Production extends MY_Controller
{
    private function _getBlueprintInfo($blueprintID)
    {
        $q = $this->db->query('SELECT * FROM invBlueprintTypes WHERE blueprintTypeID=?', $blueprintID);
        return($q->row_array());
    }

    private function _getSkillReq($blueprintID)
    {
        $q = $this->db->query('
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

    public function index($character = False)
    {
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
                AND category.categoryID=6
            ORDER BY
                techLevel, groupID, productType.typeName');
        
        foreach ($q->result() as $row)
        {
            $data['t'.$row->techLevel][$row->groupName][$row->blueprintTypeID] = $row->Item;
        }
        $assets = AssetList::getAssetsFromDB($this->chars[$character]['charid'], array('invGroups.categoryID'  => 9));
        $blueprints = array();
        foreach ($assets as $loc)
        {  
            foreach ($loc as $asset)
            {
                if ($asset['categoryID'] == 9)
                {
                    $blueprints[] = array_merge($asset, $this->_getBlueprintInfo($asset['typeID']));
                }
                if (isset($asset['contents']))
                {
                    foreach ($asset['contents'] as $content)
                    {
                        if ($content['categoryID'] == 9)
                        {
                            $blueprints[] = array_merge($content, $this->_getBlueprintInfo($content['typeID']), array('locationID' => $asset['locationID']));
                        }
                    }
                }
            }
        }
        $data['blueprints'] = $blueprints;
        
        $template['content'] = $this->load->view('blueprintfinder', $data, True);
        $this->load->view('maintemplate', $template);
        return;
    }
    
    private function _getBlueprint($blueprintID, $me = 0, $pe = 5)
    {
        $q = $this->db->query('
            SELECT 
                typeReq.typeID,
                typeReq.typeName, 
                typeReq.groupID, 
                typeGroup.categoryID,
                bluePrint.wasteFactor,
                bluePrint.materialModifier,
                materials.quantity AS basequantity,
                IF(typeReq.groupID = 332, materials.quantity, CEIL(materials.quantity * (1 + bluePrint.wasteFactor / 100) ) ) AS quantity, 
                materials.damagePerJob
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
        $data = array();
        foreach ($q->result_array() as $row)
        {
            $waste  = $row['basequantity']*(($row['wasteFactor']/100)/(1+$me) + 0.25 - 0.05 * $pe);
            $row['requiresPerfect'] = $row['basequantity'] + $waste;
            $data[] = $row;
        }
        return $data;
    }
    
    public function t1Update($character, $blueprintID)
    {
        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);

        list($materials) = getMaterials(array(18,873), AssetList::getAssetList($this->eveapi->getAssetList()));
        
        $me = is_numeric($this->input->post('me')) ? $this->input->post('me') : 0;
        $amount = is_numeric($this->input->post('amount')) ? $this->input->post('amount') : 1;
        
        foreach ($this->_getBlueprint($blueprintID, $me) as $row)
        {
            $req = ceil($row['requiresPerfect'] * $amount);
            $have = ceil($materials[$row['typeID']]);
            
            $data['req'][$row['typeID']] = $req;
            $data['have'][$row['typeID']] = $have;
        }
        echo json_encode($data);
        exit;
    }

    public function t1($character, $blueprintID = False)
    {
        if (!in_array($character, array_keys($this->chars)))
        {
            die("Could not find matching char {$character}");
        }

        $q = $this->db->query('SELECT groupName FROM invGroups WHERE invGroups.categoryID=6;');
        foreach ($q->result() as $row)
        {
            $blueprintID = is_numeric($this->input->post($row->groupName)) ? $this->input->post($row->groupName) : $blueprintID;
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
        foreach ($this->_getBlueprint($blueprintID, 0, 5) as $row)
        {
            $data['data'][$index] = $row;
            $index++;
        }
        $data['skillreq'] = $this->_getSkillReq($blueprintID);
        $data['content'] = $this->load->view('production/t1', $data, True);
        $this->load->view('maintemplate', $data);
    }
}
?>
