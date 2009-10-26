<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends Controller
{
    var $chars = array();
    var $character = Null;
    var $corp = Null;
    var $char;
    var $Auth;
    var $has_corpapi_access = False;

    function __construct ()
    {
        parent::Controller();
        
        $this->load->library('pagination');
        $this->load->library('eveapi', array('cachedir' => '/var/tmp'));
        
        $this->load->helper('cookie');
        $this->load->helper('eve');
        $this->load->helper('message');
        $this->load->helper('fitting');
        $this->load->helper('inventory');
        $this->load->helper('agent');

        $this->load->library('evecentral');
        $this->load->library('production');

        $accounts = array();

        $data['character'] = '';
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
                if (in_array($char['charname'], $this->uri->segment_array()))
                {
                    $data['character'] = $char['charname'];
                }
            }
        }
        
        $data['tool'] = $this->uri->segment(1, 'Overview');
        $data['chars'] = empty($this->chars) ? array() : $this->chars;

        $data['base_url'] = site_url("/overview/skilltree");
        $character = urldecode($this->uri->segment($this->uri->total_segments()));
        if (in_array($character, array_keys($this->chars)))
        {
			// This Happens when the user clicks on a portrait in the top navigation
			$this->session->set_userdata('character', $character);
            $this->character = $character;
            
            // Pop the Charactername off the url, so we can build a new url for a different character
            $full_uri = explode('/', $this->uri->uri_string());
            array_pop($full_uri);
            $data['base_url'] = !$this->uri->segment(1) ? site_url("/overview/skilltree") : site_url(implode('/', $full_uri));
        }
        else if ($character = $this->session->userdata('character'))
        {
			// This happens when there is a character selected in the session, and the user is just browsing sections
            $this->character = $character;
            $data['base_url'] = !$this->uri->segment(1) ? site_url("/overview/skilltree") : current_url();
        }

        if (!is_null($this->character))
        {
            $data['character'] = $this->character;
            $this->corp = $this->chars[$this->character]['corpname'];
            $this->eveapi->setCredentials(
                $this->chars[$this->character]['apiuser'], 
                $this->chars[$this->character]['apikey'], 
                $this->chars[$this->character]['charid']);
            $this->has_corpapi_access = EveApi::has_corpapi_access();
        }
		/*
        else if ($data['tool'] != 'Overview')
        {
             //* This is a Stupid Fix, more of a workaround. For some reason the Sessions sometimes resets on one of the contant pages,
             //* Which obviously doesnt work very well, so redirect home in that case.
             //* FIXME!
            redirect("/");
        }
		*/
		
        $user_timezone = !getUserConfig($this->Auth['user_id'], 'user_timezone') ? 'GMT' : getUserConfig($this->Auth['user_id'], 'user_timezone');
        date_default_timezone_set($user_timezone);

        $this->load->view('header.php', $data);
    }
}

?>
