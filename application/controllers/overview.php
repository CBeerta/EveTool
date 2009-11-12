<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Main Entry Pages
 *
 * Shows the Liste of current Characters with info on what they're training, and their wallets
 * Aswell as an RSS dump of the Development Tree History
 *
 * @todo This could use something creative
 * @author Claus Beerta <claus@beerta.de>
 */

class Overview extends MY_Controller
{
    /**
     * Enty Page with Characters and RSS feed
     *
     */
    public function index()
    {
        $this->load->library('simplepie');
        $this->simplepie->set_cache_location('/var/tmp');

        $data['chars'] = array();
        $data['total'] = 0;
        foreach ($this->chars as $k => $v)
        {
            $this->eveapi->setCredentials(
                $v['apiuser'], 
                $v['apikey'], 
                $v['charid']);
            $balance = AccountBalance::getAccountBalance($this->eveapi->getAccountBalance());
            $data['chars'][$k]['balance'] = $balance[0]['balance'];
            $data['total'] += $balance[0]['balance'];

            $training = CharacterSheet::getSkillInTraining($this->eveapi->getSkillInTraining());
            if ($training['skillInTraining'] != 0)
            {
                $training['trainingTypeName'] = $this->eveapi->skilltree[$training['trainingTypeID']]['typeName'];
            }
            $data['chars'][$k]['training'] = $training;
            $data['chars'][$k]['charid'] = $v['charid'];
        }
		
		$this->simplepie->set_feed_url("http://svnfeed.com/convert?url=http%3A%2F%2Fclaus.beerta.de%2Fsvn%2Fevetool%2Ftrunk%2F&x=0&y=0");
		$this->simplepie->set_cache_duration(12*60*60);
        $this->simplepie->init();
        $data['feed'] = $this->simplepie->get_items(0, 5);
        
        $template['content'] = $this->load->view('overview', $data, True);
        $this->load->view('maintemplate', $template);
    }

    /**
     * Global Configuration Settings
     */
    function config()
    {
        $data = array();
        $data['timezone_list'] = timezone_identifiers_list();

        if ($this->input->post('submit'))
        {
            setUserConfig($this->Auth['user_id'], 'market_region', $this->input->post('regions'));
            setUserConfig($this->Auth['user_id'], 'use_perfect', $this->input->post('use_perfect', False));
            setUserConfig($this->Auth['user_id'], 'pull_corp', $this->input->post('pull_corp', False));
            setUserConfig($this->Auth['user_id'], 'user_timezone', $data['timezone_list'][$this->input->post('user_timezone', False)]);
            setUserConfig($this->Auth['user_id'], 'mineral_prices', serialize($this->input->post('mineral_prices')));
        }

        $q = $this->db->query('SELECT regionID,regionName FROM mapRegions ORDER BY regionName');
        $data['config_region'] = getUserConfig($this->Auth['user_id'], 'market_region');

        foreach ($q->result() as $row)
        {
            $data['regions'][$row->regionID] = $row->regionName;
        }
        
        $data['pull_corp'] = !getUserConfig($this->Auth['user_id'], 'pull_corp') ? False : True;
        $data['use_perfect'] = !getUserConfig($this->Auth['user_id'], 'use_perfect') ? False : True;
        $data['user_timezone'] = !getUserConfig($this->Auth['user_id'], 'user_timezone') ? 'GMT' : getUserConfig($this->Auth['user_id'], 'user_timezone');
        $data['selected_tz'] = array_search($data['user_timezone'], $data['timezone_list']);
        
        $default_prices = array(
              34 => 2,
              35 => 8,
              36 => 32,
              37 => 128,
              38 => 512,
              39 => 2000,
              40 => 8000,
              11399 => 32000  
            );
        $data['mineral_prices'] = !getUserConfig($this->Auth['user_id'], 'mineral_prices') ? $default_prices : unserialize(getUserConfig($this->Auth['user_id'], 'mineral_prices'));

        $template['content'] = $this->load->view('config', $data, True);
        $this->load->view('maintemplate', $template);
    }
}

?>
