<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Overview extends MY_Controller
{
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
        
        $this->simplepie->set_feed_url("http://myeve.eve-online.com/feed/rdfnews.asp?tid=1");
        $this->simplepie->init();
        $data['feed'] = $this->simplepie->get_items(0, 5);
        
        $template['content'] = $this->load->view('overview', $data, True);
        $this->load->view('maintemplate', $template);
    }

    function skilltree()
    {
        $data['character'] = $this->character;
        $data['data'] = array();
        $balance = AccountBalance::getAccountBalance($this->eveapi->getAccountBalance());
        $data['balance'] = $balance[0]['balance'];

        $training = CharacterSheet::getSkillInTraining($this->eveapi->getSkillInTraining());
        $charsheet = CharacterSheet::getCharacterSheet($this->eveapi->getcharactersheet());
        
        $skillTree = array();
        $data['skillPointsTotal'] = 0;
        foreach ($charsheet['skills'] as $skill)
        {
            $data['skillPointsTotal'] += $skill['skillpoints'];
            $s = $this->eveapi->skilltree[$skill['typeID']];

            if (!isset($skillTree[$s['groupID']]))
            {
                $skillTree[$s['groupID']] = array('groupSP' => 0, 'skillCount' => 0);
            }

            $skillTree[$s['groupID']]['skills'][$skill['typeID']] = array(
                'typeID' => $skill['typeID'],
                'skillpoints' => $skill['skillpoints'],
                'rank' => $s['rank'],
                'typeName' => $s['typeName'],
                'description' => $s['description'],
                'level' => $skill['level']);
            $skillTree[$s['groupID']]['groupSP'] += $skill['skillpoints'];
            $skillTree[$s['groupID']]['skillCount'] ++;
        }
        $data['skillTree'] = $skillTree;
        
        $learning = isset($skillTree[267]['skills'][3374]['level']) ? 1 + (float)$skillTree[267]['skills'][3374]['level'] * 0.02 : 1;

        $attributes = array(
            'intelligence' => array(3377,12376),
            'charisma' => array(3376,12383),
            'perception' => array(3379,12387),
            'memory' => array(3378,12385),
            'willpower' => array(3375,12386));

        foreach ($attributes as $attribute => $se)
        {
            $sll = isset($skillTree[267]['skills'][$se[0]]['level']) ? $skillTree[267]['skills'][$se[0]]['level'] : 0; // Learnings
            $shl = isset($skillTree[267]['skills'][$se[1]]['level']) ? $skillTree[267]['skills'][$se[1]]['level'] : 0; // Advanced Learnings
            $enhancer = isset($charsheet['enhancers'][$attribute.'Bonus']['augmentatorValue']) ? $charsheet['enhancers'][$attribute.'Bonus']['augmentatorValue'] : 0;
            $data['attributes'][$attribute] = floor(($charsheet['attributes'][$attribute] + $enhancer + $sll + $shl) * $learning);
        }
        $data['charinfo'] = $charsheet;

        if ($training['skillInTraining'] != 0)
        {
            $training['trainingTypeName'] = $this->eveapi->skilltree[$training['trainingTypeID']]['typeName'];
        }
        else
        {
            $training['trainingTypeName'] = 'Not Training';
            $training['trainingToLevel'] = Null;
            $training['trainingTypeID'] = -1;
        }
        $data['training'] = $training;

        $template['content'] = $this->load->view('skilltree', $data, True);
        $this->load->view('maintemplate', $template);
    }


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

    function standings()
    {
        $data['character'] = $this->character;
        $standings = Standings::getStandings($this->eveapi->getStandings());
    
        print '<pre>';
        print_r($standings);

        exit;
    }

}

?>
