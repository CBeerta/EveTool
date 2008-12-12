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
    	$character = $this->character;
        $data['character'] = $character;


        $data['content'] = '<b>Not Started on this yet</b>';
        $this->load->view('maintemplate', $data);
        return;
    }
}
?>
