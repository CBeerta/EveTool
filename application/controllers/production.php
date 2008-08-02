<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Production extends MY_Controller
{

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
    
   
    public function t1Update($character, $blueprintID)
    {
        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);
        $data = array();
        
        list($data['have']) = getMaterials(array(18,873), AssetList::getAssetList($this->eveapi->getAssetList()));
        
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
        $data['totalVolume'] = 0;
        
        list ($components, $totalMineralUsage) = $this->_getBlueprint($character, $blueprintID, $me);
        foreach ($components as $row)
        {
            $req = ceil($row['requiresPerfect'] * $amount);
            $data['req'][$row['typeID']] = $req;
            $data['totalVolume'] += $req * $row['volume'];
        }
        foreach ($totalMineralUsage as $k => $v)
        {
            $data['totalMineralUsage'][$k] = $v * $amount;
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
        list ($components, $data['totalMineralUsage']) = $this->_getBlueprint($character, $blueprintID, 0);

        foreach ($components as $row)
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
