<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Agent Pages
 *
 * Information about Agents with characters standings towards them 
 *
 * @author Claus Beerta <claus@beerta.de>
 */

class Agents extends MY_Controller
{
    
/*
	function surrounding_agents($system)
	{
		print '<pre>';
		
		print_r(surroundingSystems($system, 10));
	
		die();
	}
*/

    /**
     * Return a list of Agent Divisions
     */
    private function _agent_divisions($corp_id)
    {
        $divisions = array();
        $q = $this->db->query('
            SELECT 
                divisionID,
                divisionName 
            FROM 
                crpNPCDivisions
            WHERE 
                divisionID IN (SELECT DISTINCT(divisionID) FROM agtAgents WHERE corporationID = ?)
            ORDER BY 
                divisionName;', $corp_id);

        foreach ($q->result() as $row)
        {
            $divisions[$row->divisionID] = $row->divisionName;
        }
        $divisions[1000] = '-';
        $divisions[1001] = 'Combat';
        $divisions[1002] = 'Courier';
        return ($divisions);
    }  
    
    
    /**
     * Figure out what type an agent of $id is, and redirect accordingly
     */
    public function by_id($id)
    {
        if ( Agent_Info::is_agent($id) )
        {
            redirect ("/agents/npc/{$id}/{$this->character}");
        }
        else if ( Agent_Info::is_npccorp($id) )
        {
            redirect ("/agents/npccorp/{$id}/{$this->character}");
        }
        else if ( Agent_Info::is_faction($id) )
        {
            redirect ("/agents/faction/{$id}/{$this->character}");
        }
        else
        {
            // Neither, so useless. 
            redirect ("/charstandings/agents/{$this->character}");
        }
    }

    /**
     * Details about the selected Corp, with its agents
     */
    public function npccorp($id)
    {
        $standings = Standings::getStandings($this->eveapi->getStandings());
        $corpinfo = array_shift(Agent_Info::is_npccorp($id));

        if ($this->input->post('corp') > 0)
        {
            $id = $this->input->post('corp');
        }
        $data['corpid'] = $id;        
        
        if (!empty($standings['characterStandings']['standingsFrom']['factions']))
        {
            $rawstandings = $standings['characterStandings']['standingsFrom']['factions'];
        }
        else
        {
            $rawstandings = array();
        }
        if (!empty($standings['characterStandings']['standingsFrom']['NPCCorporations']))
        {
            $rawstandings = array_merge($rawstandings, $standings['characterStandings']['standingsFrom']['NPCCorporations']);
        }

        foreach ( $rawstandings as $row )
        {
            if ( $row['fromID'] == $id || $row['fromID'] == $corpinfo->factionID)
            {
                $data['corpstanding'] = number_format((((0.04*$this->eveapi->get_skill_level(3359))*(10-$row['standing']))+$row['standing']), 2);
            }
        }
        
        $data['regions'] = array('0' => '-') + $this->eveapi->get_eve_regions();
        $data['divisions'] = array('0' => '-') + $this->_agent_divisions($id);
        $data['corps'] = $this->eveapi->get_npc_corps();

        // Process Filter Rules form $_POST
        $data['show_available'] = is_string($this->input->post('show_available')) ? True : False;
        $data['show_hisec'] = is_string($this->input->post('show_hisec')) ? True : False;

        $filter = array('1=1');
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
            // Why the fuck doesn't MySQL allow the use of SELECT AS statements in WHERE clauses? Grrrr...
            $filter[] = "((agtAgents.level - 1) * 2 + agtAgents.quality / 20) < {$data['corpstanding']}";
            $filter[] = "agtAgentTypes.agentTypeID NOT IN (6)"; // Only show basic Agent types, not StoryLine Agents
        }

        $data['agents'] = Agent_Info::is_npccorp($id, $filter);
        
        $template['content'] = $this->load->view('agents', $data, True);
        $this->load->view('maintemplate', $template);
    }


    /**
     * A List of Agents belonging to the Selected Faction
     */
    public function faction($id)
    {
        $q = $this->db->query ("
            SELECT 
                corporationID,
                itemName 
            FROM 
                crpNPCCorporations,
                eveNames 
            WHERE
                factionID = ? AND
                corporationID = eveNames.itemID;
            ", $id);
        $data['corps'] = $q->result();
        $data['faction'] = Agent_Info::is_faction($id);
        
        $template['content'] = $this->load->view('faction', $data, True);
        $this->load->view('maintemplate', $template);
    }

    /**
     * Details about a single Agent
     * @todo needs to be finished
     */
    public function npc($id)
    {
        $template['content'] = "This is a NPC Agent, and this isn't done yet!";
        $this->load->view('maintemplate', $template);
    }

}

?>
