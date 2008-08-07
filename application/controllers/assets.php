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

    public function index($character = False)
    {
        if (!in_array($character, array_keys($this->chars)))
        {
            die("Could not find matchign char {$character}");
        }
        $data = array();
        $data['character'] = $character;

        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);

        AssetList::getAssetList($this->eveapi->getAssetList(), $this->chars[$character]['charid']);
        $data['assets'] = AssetList::getAssetsFromDB($this->chars[$character]['charid']);
        $template['content'] = $this->load->view('assets', $data, True);
        $this->load->view('maintemplate', $template);
    }
/*
    public function search($character = False, $query)
    {
        if (!in_array($character, array_keys($this->chars)))
        {
            die("Could not find matchign char {$character}");
        }
        $data = array();
        $data['character'] = $character;

        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);

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
