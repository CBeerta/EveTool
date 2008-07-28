<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Production extends MY_Controller
{
    private function _getBlueprintInfo($blueprintID)
    {
        $q = $this->db->query('SELECT * FROM invBlueprintTypes WHERE blueprintTypeID=?', $blueprintID);
        return($q->row_array());
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
    
    public function t1($character = False)
    {
        if (!in_array($character, array_keys($this->chars)))
        {
            die("Could not find matching char {$character}");
        }
        $data['character'] = $character;

        $blueprintID = !is_numeric($_REQUEST['blueprintID']) ? '967' : $_REQUEST['blueprintID'];
        
        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);
        
        $index = 0;
        $q = $this->db->query('
            SELECT
                typeReq.typeID,
                typeReq.typeName,
                typeReq.groupID, 
                typeGroup.categoryID,
                CEIL(materials.quantity * (1 + bluePrint.wasteFactor / 100) ) AS quantity, 
                materials.damagePerJob 
            FROM 
                TL2MaterialsForTypeWithActivity AS materials 
            INNER JOIN invTypes AS typeReq ON materials.requiredtypeID = typeReq.typeID 
            INNER JOIN invGroups AS typeGroup ON typeReq.groupID = typeGroup.groupID
            INNER JOIN invBlueprintTypes AS bluePrint ON materials.typeID = bluePrint.blueprintTypeID 
            INNER JOIN eveGraphics AS graphics ON typeReq.graphicID = graphics.graphicID 
            WHERE materials.typeID = ? AND materials.activity = 1 ORDER BY typeReq.typeName;
        ', $blueprintID);
        
        list($materials) = getMaterials(18, AssetList::getAssetList($this->eveapi->getAssetList()));
        $data['caption'] = getInvType($blueprintID)->typeName;
        
        $allowsFor = array();
        
        foreach ($q->result() as $row)
        {
            if ($row->groupID == 18)
            {
                $perfect = $row->quantity;
                $data['data'][$index] = array(
                    'typeID' => $row->typeID,
                    'typeName' => $row->typeName,
                    'requires' => $perfect,
                    'requiresPerfect' => $perfect,
                    'available' => $materials[$row->typeID],
                    );
                $allowsFor[$index] = floor($materials[$row->typeID] / $perfect);
            }
            else if ($row->categoryID == 16)
            {
                $data['skillreq'] = $row;
            }
            $index++;
        }
            
        $template['content'] = $this->load->view('production/t1', $data, True);
        $this->load->view('maintemplate', $template);
    }
}

?>
