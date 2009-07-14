<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pos extends MY_Controller
{
    
        
    function index()
    {
        $data = StarbaseList::getStarbaseList($this->eveapi->getStarBaseList());

        print '<pre>';
        print_r($data);
        print '</pre>';

        
        $data['content'] = '<b>Not Started on this yet</b>';
        $this->load->view('maintemplate', $data);
        return;
    }      
    
    function detail($id)
    {
        $data = StarbaseDetail::getStarbaseDetail($this->eveapi->getStarbaseDetail($id));

        print '<pre>';
        print_r($data);
        print '</pre>';

        $data['content'] = '<b>Not Started on this yet</b>';
        $this->load->view('maintemplate', $data);
        return;
    }

}

?>