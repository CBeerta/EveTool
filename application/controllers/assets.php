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


/*
    public function search($character = False, $query)
    {
        $data = array();
        $data['character'] = $this->character;


        $data['assets'] = AssetList::getAssetsFromDB($this->chars[$character]['charid'], array('invTypes.typeName' => $query) );
        
        print '<pre>';
        print_r($data['assets']);

        exit;

        $template['content'] = $this->load->view('assets', $data, True);
        $this->load->view('maintemplate', $template);
    }
*/
}
?>
