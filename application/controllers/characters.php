<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Character Information Pages
 *
 *
 * @author Claus Beerta <claus@beerta.de>
 * @todo Add Certificates
 */
class Characters extends Controller
{
    /**
    *
    * Load the Template and add submenus
    *
    * @access private
    * @param array $data contains the stuff handed over to the template
    **/
	private function _template($data, $current_char = False)
	{
		$characters = array_keys($this->eveapi->characters());
        $menu = array();

		$data['page_title'] = "Characters"; 

        foreach ($characters as $char)
        {
            $menu[$char] = array(
    		        "ships/{$char}" => "Ship Capabilites", 
    		        "sheet/{$char}" => "Character Sheet",
    		        "standings/{$char}" => "Standings",
		        );
        }

        $data['submenu'] = $menu;


		if ($current_char)
		{
    		$data['page_sub_title'] = $current_char; 
/***
    		$data['submenu'] = array("Details on {$current_char}" => 
    		    array(
    		        "ships/{$current_char}" => "Ship Capabilites", 
    		        "sheet/{$current_char}" => "Character Sheet",
    		        "agents/{$current_char}" => "Agents",
		        )
	        );
***/
		}


		$this->load->view('template', $data);
	}

	/**
	* Blog style display of all characters
	*
	*
	**/
	public function index()
	{
		$charinfo = array();
		$skilltree = $this->eveapi->skilltree();
		
		$global['totalisk'] = $global['totalsp'] = 0;

		$alerts = array();
		
		foreach ($this->eveapi->characters() as $char)
		{
			$this->eveapi->setCredentials($char);
			$charsheet = $this->eveapi->CharacterSheet();
			
			if (!isset($account_training[$char->apiUser]))
			{   // if unset, set to 0
    			$account_training[$char->apiUser] = 0;
			}
			if ($charsheet['isTraining'])
			{   // found a char that is training on this account
			    $account_training[$char->apiUser] = 1;
		    }

            $charinfo[$char->name] = array_merge((array) $char, $charsheet);

			if ($charinfo[$char->name]['cloneSkillPoints'] < $charinfo[$char->name]['skillpoints_total'])
			{
			    $alerts[] = "{$char->name} clone Outdated";
			}

			$global['totalisk'] += $charinfo[$char->name]['balance'];
			$global['totalsp'] += $charinfo[$char->name]['skillpoints_total'];
		}		

		foreach ($account_training as $k => $v)
		{
		    if ($v == 0)
		    {
		        $alerts[] = "Account with ".implode(', ', $this->eveapi->accounts[$k])." Has no Character Training.";
		    }
		}

		masort($charinfo, array('name'));
        $this->_template(array('content' => $this->load->view('charoverview', array('data' => $charinfo, 'global' => $global, 'alerts' => $alerts), True)));
	}

    /**
    * Display Charactersheet
    *
    * @access public
    * @param string $character to show ships of
    **/
	public function sheet($character)
	{

		if (!in_array($character, array_keys($this->eveapi->characters())))
		{
		    die("<h1>Char {$character} not found</h1>");
	    }
	    $char = $this->eveapi->characters[$character];
		$this->eveapi->setCredentials($char);

		$data = $this->eveapi->CharacterSheet();
	
        $this->_template(array('content' => $this->load->view('skilltree', $data, True)), $character);
    }

    
    /**
    *
    * Agent Finder
    *
    * @access public
    * @param string $character to show Agents of
    *
    **/
	public function agentfinder($character, $id = 1000146)
	{
	    $this->load->library('agents');

		if (!in_array($character, array_keys($this->eveapi->characters())))
		{
		    die("<h1>Char {$character} not found</h1>");
	    }
	    $char = $this->eveapi->characters[$character];
		$this->eveapi->setCredentials($char);

        if ($this->input->post('corp') > 0)
        {
            $id = $this->input->post('corp');
        }

        $data['corpid'] = $id; 
        $filter = array('1=1');
        if (Agents::is_faction($id))
        {
            redirect("/characters/faction/{$character}/{$id}");
            exit;
        }

        $corpinfo = array_shift(Agents::is_npccorp($id));
        $data['divisions'] = array('0' => '-') + Agents::divisions($id);

        $standings = $this->eveapi->Standings();
        foreach (array('agents', 'factions', 'NPCCorporations') as $type)
        {
            foreach ($standings->result->characterNPCStandings->$type as $row)
            {
                if ( $row->fromID == $id || $row->fromID == $corpinfo->factionID)
                {
                    $data['corpstanding'] = number_format((((0.04*$this->eveapi->skill_level(3359))*(10-$row->standing))+$row->standing), 2);
                }
            }
        }

        $data['regions'] = array('0' => '-') + $this->eveapi->eve_regions();
        $data['corps'] = array('0' => '-') + $this->eveapi->npc_corps();
        
        // Process Filter Rules form $_POST
        $data['show_available'] = is_string($this->input->post('show_available')) ? True : False;
        $data['show_hisec'] = is_string($this->input->post('show_hisec')) ? True : False;

        $data['selected_division'] = 0;
        if ($this->input->post('division') >= 1000)
        {
            $data['selected_division'] = $this->input->post('division');
            switch ($this->input->post('division'))
            {
                case '1001':
                    $filter[] = "agtAgents.divisionID IN (6,9,10,19,21)";
                    break;
                case '1002':
                    $filter[] = "agtAgents.divisionID IN (4,7,12,16,20)";
                    break;
                default: //the retarded clicked the '-'
                    break;
            }
        }
        else if ($this->input->post('division') > 0)
        {
            $data['selected_division'] = $this->input->post('division');
            $filter[] = "agtAgents.divisionID={$data['selected_division']}";
        }
        $data['selected_level'] = 0;
        if ($this->input->post('level') > 0)
        {
            $data['selected_level'] = $this->input->post('level');
            $filter[] = "agtAgents.level = {$data['selected_level']}";
        }
        $data['selected_region'] = 0;
        if ($this->input->post('region') > 0)
        {
            $data['selected_region'] = $this->input->post('region');
            $filter[] = "station.regionID = {$data['selected_region']}";
        }
        if ($data['show_hisec'])
        {
            $filter[] = "ROUND(SolarSystems.security,1) >= 0.5";
        }
        if ($data['show_available'] && isset($data['corpstanding']))
        {
            $filter[] = "((agtAgents.level - 1) * 2 + agtAgents.quality / 20) < {$data['corpstanding']}";
            $filter[] = "agtAgentTypes.agentTypeID NOT IN (6)"; // Only show basic Agent types, not StoryLine Agents
        }
        $data['agents'] = Agents::is_npccorp($id, $filter);
        $data['character'] = $character;

        $this->_template(array('content' => $this->load->view('agents', $data, True)), $character);
    }

    /**
    *
    * NPC Standings
    *
    * @access public
    * @param string $character to show Standings of
    *
    **/
    public function standings($character)
    {
	    $this->load->library('agents');

		if (!in_array($character, array_keys($this->eveapi->characters())))
		{
		    die("<h1>Char {$character} not found</h1>");
	    }
	    $char = $this->eveapi->characters[$character];
		$this->eveapi->setCredentials($char);

        $data = array();

        $rawstandings = $this->eveapi->Standings();
        $standings = array();

        foreach (array('NPCCorporations', 'factions', 'agents') as $type)
        {
            foreach ($rawstandings->result->characterNPCStandings->$type as $row)
            {
                $direction =  isset($row->toName) ? 'towards' : 'from';
                $title = "{$direction} ".ucfirst($type);

                $name = isset($row->toName) ? $row->toName : $row->fromName; 
                $id = isset($row->toID) ? $row->toID : $row->fromID;    

                if ($row->standing >= 0)
                {
					// calculate real standing after skills:        
					// (((0.04*Connections level)*(10-Base Agent Standing))+Base Agent Standing)
                    $realstanding = number_format((((0.04*$this->eveapi->skill_level(3359))*(10-$row->standing))+$row->standing), 2);
                }
                else
                {
					// <effective standing> = <raw standing> + (( 10 - <raw standing> ) x ( <level of diplomacy> x 0.04 ))
					$realstanding = number_format($row->standing + (( 10.0 - $row->standing ) * ( 0.04 * $this->eveapi->skill_level(3357) )), 2);
                }

                $standing = isset($row->toName) ? $row->standing : "{$realstanding} ({$row->standing})";
                
                if ( $row->standing > 5.0)
                {
                    $sta_icon = 'sta_high.png';
                }
                elseif ( $row->standing > 0.0)
                {
                    $sta_icon = 'sta_good.png';
                }
                elseif ( $row->standing < 5.0)
                {
                    $sta_icon = 'sta_horrible.png';
                }
                elseif ( $row->standing < 0.0)
                {
                    $sta_icon = 'sta_bad.png';
                }

                $agent_info = Agents::is_agent($id) ? Agents::agent_snippet($id) : '';
                
                $standings[$title][] = array(
                    'name' => $name,
                    'id' => $id,
                    'standing' => $standing,
					'realstanding' => $realstanding,
                    'agent_info' => $agent_info,
                    'sta_icon' => $sta_icon,
                );
            }
        	masort($standings[$title], array('realstanding', 'name'));
        }

        $data['standings'] = $standings;
        $data['character'] = $character;
        
        $this->_template(array('content' => $this->load->view('standings', $data, True)), $character);
    }


    /**
    *
    * Faction Display
    *
    * @access public
    * @param string $character to show Agents of
    *
    **/
	public function faction($character, $id)
	{
	    $this->load->library('agents');

        $q = $this->db->query ("
            SELECT 
                corporationID,
                itemName 
            FROM 
                crpNPCCorporations,
                eveNames 
            WHERE
                factionID = ? AND
                corporationID = eveNames.itemID
            ORDER BY itemName;
            ", $id);

        $data['corps'] = $q->result();
        $data['faction'] = Agents::is_faction($id);
        $data['character'] = $character;
      
        $this->_template(array('content' => $this->load->view('faction', $data, True)), $character);
	}

    
    /**
    * Display characters ship abilities
    *
    * @access public
    * @param string $character to show ships of
    **/
	public function ships($character)
	{
        $canfly = $has = array();

		if (!in_array($character, array_keys($this->eveapi->characters())))
		{
		    die("<h1>Char {$character} not found</h1>");
	    }
	    $char = $this->eveapi->characters[$character];
		$this->eveapi->setCredentials($char);
		
		$charsheet = $this->eveapi->CharacterSheet();

        $q = $this->db->query("
            SELECT
                t.*,
                g.*,
                r.*,
                (SELECT IFNULL(valueInt, valueFloat) FROM dgmTypeAttributes WHERE typeID=t.typeID AND attributeID = 182) AS skill1req,
                (SELECT IFNULL(valueInt, valueFloat) FROM dgmTypeAttributes WHERE typeID=t.typeID AND attributeID = 183) AS skill2req,
                (SELECT IFNULL(valueInt, valueFloat) FROM dgmTypeAttributes WHERE typeID=t.typeID AND attributeID = 184) AS skill3req,
                (SELECT IFNULL(valueInt, valueFloat) FROM dgmTypeAttributes WHERE typeID=t.typeID AND attributeID = 277) AS skill1level,
                (SELECT IFNULL(valueInt, valueFloat) FROM dgmTypeAttributes WHERE typeID=t.typeID AND attributeID = 278) AS skill2level,
                (SELECT IFNULL(valueInt, valueFloat) FROM dgmTypeAttributes WHERE typeID=t.typeID AND attributeID = 279) AS skill3level,
                (SELECT valueInt FROM dgmTypeAttributes WHERE typeID=t.typeID AND attributeID=422) AS techlevel
            FROM    
                invTypes AS t,
                invGroups AS g,
                chrRaces AS r
            WHERE
                g.groupID=t.groupID AND
                r.raceID=t.raceID AND
                g.categoryID=6 AND
                t.published=1
            ORDER BY
                groupName, raceName, typeName ASC
            ");

        foreach ($q->result_array() as $ship)
        {
            $canflythis = False;
            foreach (array(1,2,3) as $l)
            {
                if (!is_numeric($ship["skill{$l}req"]))
                {
                    continue;
                }
                
                if (isset($charsheet['skills'][$ship["skill{$l}req"]]) && 
                    $charsheet['skills'][$ship["skill{$l}req"]]->level >= $ship["skill{$l}level"]
                    )
                {
                    $canflythis = True;
                }
                else
                {
                    $canflythis = False;
                    break;
                }
            }
            
            if ($canflythis === True)
            {
                $canfly[$ship['groupName']][$ship['raceName']][] = $ship;
            }
        }
        
        $this->_template(array('content' => $this->load->view('ships', array('canfly' => $canfly, 'character' => $character), True)), $character);
    }

}

?>
