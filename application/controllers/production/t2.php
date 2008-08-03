<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class T2 extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('production');
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


        $data['content'] = '<b>Not Started on this yet</b>';
        $this->load->view('maintemplate', $data);
        return;
    }
}
?>
