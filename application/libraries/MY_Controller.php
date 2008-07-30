<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends Controller
{
    var $chars = array();
    var $Auth;

    function __construct ()
    {
        parent::Controller();

        $this->load->library('eveapi', array('cachedir' => '/var/tmp'));
        $this->load->helper('eve');
        $this->load->helper('message');

        $this->load->library('evecentral');

        $accounts = array();

        if (!$this->users->isLoggedIn())
        {
            redirect('user/login');
        }
        else
        {
            $this->Auth['user_id'] = $this->users->getInfo($this->users->user, 'id');
            $this->Auth['username'] = $this->users->user;

            $q = $this->db->query(
                'SELECT apiUser, apiFullKey 
                FROM asc_apikeys 
                WHERE acctID = ? AND apiFullKey != "";', $this->Auth['user_id']);
            $index = 0;

            foreach ($q->result()as $row)
            {   
                $accounts[$index]['apiuser'] = $row->apiUser;
                $accounts[$index]['apikey'] = $row->apiFullKey;
                $index++;
            }
        }
        /**
         * FIXME: Maybe move this to the database, instead of using the Api?
         **/
        foreach ($accounts as $account)
        {
            $this->eveapi->setCredentials($account['apiuser'], $account['apikey']);
            $chars = Characters::getCharacters($this->eveapi->getCharacters());
            if (!is_array($chars))
            {
                continue;
            }
            foreach ($chars as $char)
            {   
                $this->chars[$char['charname']]['charid'] = $char['charid'];
                $this->chars[$char['charname']]['apiuser'] = $account['apiuser'];
                $this->chars[$char['charname']]['apikey'] = $account['apikey'];
                $this->chars[$char['charname']]['corpname'] = $char['corpname'];
                $this->chars[$char['charname']]['corpid'] = $char['corpid'];
            }
        }
        $tool = $this->uri->segment($this->uri->total_segments()-2);
        $subtool = $this->uri->segment($this->uri->total_segments()-1);
        $data['tool'] = empty($tool) ? 'overview' : $tool;
        $data['subtool'] = empty($subtool) ? 'index' : $subtool;
        
        $data['chars'] = empty($this->chars) ? array() : $this->chars;
        $this->load->view('header.php', $data);
    }
}

?>
