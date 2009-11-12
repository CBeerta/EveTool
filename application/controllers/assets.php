<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Asset Pages
 *
 * Various Functions to access Characters Assets 
 *
 * @author Claus Beerta <claus@beerta.de>
 */

class Assets extends MY_Controller {

    /**
     * Asset Display
     *
     * Display a Table with all the characters assets, ordered by Location
     */
    public function index()
    {
        $data = array();
        $data['character'] = $this->character;

        $data['cachedUntil'] = AssetList::getAssetList($this->eveapi->getAssetList(), $this->chars[$this->character]['charid']);
        $data['assets'] = AssetList::getAssetsFromDB($this->chars[$this->character]['charid']);
        $template['content'] = $this->load->view('assets', $data, True);
        $this->load->view('maintemplate', $template);
    }
    
    /**
     * Filter Assets by categoryID
     *
     */
    private function _byCategory($charid, $categoryID = 9, $filter = array(29,31, 237) )
    {
        $assets = AssetList::getAssetsFromDB($charid, array('invGroups.categoryID'  => $categoryID));

        $data = array();
        foreach ($assets as $loc)
        {  
            foreach ($loc as $asset)
            {
                if ( $asset['categoryID'] == $categoryID && !in_array($asset['groupID'], $filter) )
                {
                    $data[] = array_merge($asset, Production::getBlueprintInfo($asset['typeID']));
                }
                if (isset($asset['contents']))
                {
                    foreach ($asset['contents'] as $content)
                    {
                        if ( $content['categoryID'] == $categoryID && !in_array($content['groupID'], $filter) )
                        {
                            $data[] = array_merge($content, Production::getBlueprintInfo($content['typeID']), array('locationID' => $asset['locationID']));
                        }
                    }
                }
            }
        }
        return ($data);
    }
    
    /**
     * Displays a Table of Blueprints owned by the selected character
     */
    public function blueprints()
    {
        $character = $this->character;
        $data['character'] = $character;
        $data['assets'] = $this->_byCategory($this->chars[$character]['charid'], 9);
        $data['title']= 'Played Owned Blueprints';
        
        $template['content'] = $this->load->view('bycategory', $data, True);
        $this->load->view('maintemplate', $template);
    }

    /**
     * Displays a Table of Ships owned by the selected character
     */
    public function ships()
    {
        $character = $this->character;
        $data['character'] = $character;
        $data['assets'] = $this->_byCategory($this->chars[$character]['charid'], 6);
        $data['title']= 'Played Owned Ships';
        
        $template['content'] = $this->load->view('bycategory', $data, True);
        $this->load->view('maintemplate', $template);
    }

    /**
     * Search for assets
     *
     * This Functions searches all the assets on all Characters this login has
     */
    public function search()
    {
        $data = array();
        $character = $this->character;
        $data['character'] = $this->character;
		
		if (!is_string($this->input->post('search')))
		{
			redirect('overview/skilltree');
			exit;
		}
		else
		{
			$query = $this->input->post('search');
		}
		
		$char_ids = array(-1);
		foreach ($this->chars as $char)
		{
			$char_ids[] = $char['charid'];
		}
		
		$assets = $this->db->query('	
			SELECT 
				assets.characterID,
				itemID,
				invTypes.typeID,
				locationID,
				typeName,
				quantity,
				volume,
				categoryID,
				icon
			FROM 
				assets, 
				invTypes,
				invGroups,
				eveGraphics
			WHERE 
				invTypes.graphicID=eveGraphics.graphicID AND
				invTypes.groupID=invGroups.groupID AND
				assets.typeID=invTypes.typeID AND
				assets.characterID IN ('.implode(',', $char_ids).') AND
				invTypes.typeName LIKE ?;',  "%{$query}%");
		$contents = $this->db->query('
			SELECT 
				contents.characterID,
				contents.itemID,
				invTypes.typeID,
				assets.typeID AS containedIn, 
				typeName, 
				assets.locationID,
				contents.quantity,
				invTypes.volume,
				categoryID,
				icon
			FROM 
				assets, 
				contents, 
				invTypes,
				invGroups,
				eveGraphics
			WHERE 
				invTypes.graphicID=eveGraphics.graphicID AND
				invTypes.groupID=invGroups.groupID AND
				assets.itemID=contents.locationItemID AND 
				contents.typeID=invTypes.typeID AND 
				contents.characterID IN ('.implode(',', $char_ids).') AND
				invTypes.typeName LIKE ?;', "%{$query}%");

		$data['found'] = array_merge($assets->result_array(), $contents->result_array());
		
        $template['content'] = $this->load->view('search', $data, True);
        $this->load->view('maintemplate', $template);
    }

}
?>
