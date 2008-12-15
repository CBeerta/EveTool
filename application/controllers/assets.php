<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Assets extends MY_Controller
{
    /**
     * assets
     *
     * Display a Table with all the characters assets, ordered by Location
     *
     * @param   string
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
    
    public function search($query, $character)
    {
        $character = $this->character;
        
        print '<pre>';
            
    }

/*
    public function search($query, $character)
    {
        $data = array();
        $character = $this->character;
        $data['character'] = $this->character;
        print '<pre>';

        $res = $this->db->query('SELECT typeName,typeID FROM invTypes WHERE typeName like ?;', "%{$query}%");
        $typeIdList = array();
        foreach ($res->result() as $row)
        {
                $typeIdList[$row->typeID] = "invTypes.typeName";
        }
        print_r($typeIdList);             
        $data['assets'] = AssetList::getAssetsFromDB($this->chars[$character]['charid'], $typeIdList );
        
        print_r($data['assets']);


        exit;
        $template['content'] = $this->load->view('assets', $data, True);
        $this->load->view('maintemplate', $template);
    }
*/

}
?>
