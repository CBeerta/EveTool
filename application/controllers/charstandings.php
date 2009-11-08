<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class CharStandings extends MY_Controller
{
    public $standings;
	        
    public function __construct()
    {
        parent::__construct();
        $this->standings = Standings::getStandings($this->eveapi->getStandings());
    }   
    
    public function agents ()
    {
        $data['character'] = $this->character;
        $rawstandings = $this->standings['characterStandings'];
            
        $template['content'] = $this->_get_standings(array_reverse($rawstandings['standingsFrom']));
        $this->load->view('maintemplate', $template);
    }
    
    private function _get_standings ( $rawstandings )
    {
        
        $standings = array();     
        foreach ( $rawstandings as $k => $v )
        {
            $direction =  isset($v[0]['toName']) ? 'towards' : 'from';
            $name = ucfirst($k);

            $title = "{$direction} {$name}";
            foreach ( $v as $to )
            {
                $name = isset($to['toName']) ? $to['toName'] : $to['fromName']; 
                $id = isset($to['toID']) ? $to['toID'] : $to['fromID'];    
                
                if ($to['standing'] >= 0)
                {
					// calculate real standing after skills:        
					// (((0.04*Connections level)*(10-Base Agent Standing))+Base Agent Standing)
                    $realstanding = number_format((((0.04*$this->eveapi->get_skill_level(3359))*(10-$to['standing']))+$to['standing']), 2);
                }
                else
                {
					// <effective standing> = <raw standing> + (( 10 - <raw standing> ) x ( <level of diplomacy> x 0.04 ))
					$realstanding = number_format($to['standing'] + (( 10.0 - $to['standing'] ) * ( 0.04 * $this->eveapi->get_skill_level(3357) )), 2);
                }
                
                $standing = isset($to['toName']) ? $to['standing'] : "{$realstanding} ({$to['standing']})";
                $agent_info = Agent_Info::is_agent($id) ? Agent_Info::agent_snippet($id) : '';
                
                if ( $to['standing'] > 5.0)
                {
                    $sta_icon = 'sta_high.png';
                }
                elseif ( $to['standing'] > 0.0)
                {
                    $sta_icon = 'sta_good.png';
                }
                elseif ( $to['standing'] < 5.0)
                {
                    $sta_icon = 'sta_horrible.png';
                }
                elseif ( $to['standing'] < 0.0)
                {
                    $sta_icon = 'sta_bad.png';
                }
                
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
        return ($this->load->view('charstandings', $data, True));
    }
}
?>