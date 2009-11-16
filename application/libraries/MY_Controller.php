<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Controller Class that initializes the environment for all PAges
 *
 *
 * @author Claus Beerta <claus@beerta.de>
 */


class MY_Controller extends Controller
{
    /**
     * Array with all characters on this account
     */
    public $chars = array();
    
    /**
     * The Current and Active Character
     **/
    public $character = Null;
    
    /**
     * Corp the current character is in
     */
    public $corp = Null;
    
    /**
     * Authentication details of the current session
     */
    public $Auth;
    
    /**
     * Bool that describes if the current selected character has corpapi roles
     */
    public $has_corpapi_access = False;
	
    public function __construct ()
    {
        parent::Controller();
        
        $this->load->library('pagination');
        $this->load->library('eveapi', array('cachedir' => '/var/tmp'));
        
        $this->load->helper('cookie');
        $this->load->helper('eve');
        $this->load->helper('igb');
        $this->load->helper('message');
        $this->load->helper('fitting');
        $this->load->helper('inventory');
        $this->load->helper('agent');
        $this->load->helper('random_functions');

        $this->load->library('evecentral');
        $this->load->library('production');
        $this->load->library('cache');
        
        $accounts = array();

        $data['character'] = '';
        if (!$this->users->isLoggedIn())
        {
            redirect('user/login');
            exit;
        }
        
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
        
        /**
         * @todo Maybe move this to the database, instead of using the Api? It's now cached with memcache, though if that really does anything remains to be seen
         */
        if ( ($this->chars = $this->cache->get('evetool_accounts_'.$this->Auth['user_id'])) === False )
        {
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
        }
        $this->cache->set('evetool_accounts_'.$this->Auth['user_id'], $this->chars);
        
        $data['tool'] = $this->uri->segment(1, 'Overview');
        $data['chars'] = empty($this->chars) ? array() : $this->chars;

        $data['base_url'] = site_url("/character/skilltree");
        $character = urldecode($this->uri->segment($this->uri->total_segments()));
        if (in_array($character, array_keys($this->chars)))
        {
			// This Happens when the user clicks on a portrait in the top navigation
			$this->session->set_userdata('character', $character);
            $this->character = $character;
            
            // Pop the Charactername off the url, so we can build a new url for a different character
            $full_uri = explode('/', $this->uri->uri_string());
            array_pop($full_uri);
            $data['base_url'] = !$this->uri->segment(1) ? site_url("/character/skilltree") : site_url(implode('/', $full_uri));
        }
        else if ($character = $this->session->userdata('character'))
        {
			// This happens when there is a character selected in the session, and the user is just browsing sections
            $this->character = $character;
            $data['base_url'] = !$this->uri->segment(1) ? site_url("/character/skilltree") : current_url();
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
			$data['has_corpapi_access'] = $this->has_corpapi_access;
        }
		
        $user_timezone = !get_user_config($this->Auth['user_id'], 'user_timezone') ? 'GMT' : get_user_config($this->Auth['user_id'], 'user_timezone');
        date_default_timezone_set($user_timezone);

        $this->load->view('header.php', $data);
    }
}

?>
