<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class T2 extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('production');
    }

    public function index()
    {
		//FIXME: This is code dublication, it's identical to the t1 part apart from the template loaded (which is almost identical aswell)
        $character = $this->character;
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
            $data['t'.$row->techLevel][$row->categoryID][$row->groupName][$row->blueprintTypeID] = $row->Item;
        }
        
        $template['content'] = $this->load->view('production/t2_blueprints', $data, True);
        $this->load->view('maintemplate', $template);
        return;
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
        $data['caption'] = getInvType($blueprintID)->typeName;
        
        $allowsFor = $data['data'] = array();

        $index = 0;
        list ($components, $data['totalMineralUsage']) = Production::getBlueprint($character, $blueprintID, 0);
        foreach ($components as $row)
        {
            $data['data'][$index] = $row;
            $index++;
        }
		
		print '<pre>';
		print_r($components);
		print '</pre>';
		die();
        $data['skillreq'] = Production::getSkillReq($blueprintID);
        $data['content'] = $this->load->view('production/t1', $data, True);
        $this->load->view('maintemplate', $data);
    }	

}
?>
