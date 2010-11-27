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
	private function _template($data)
	{
		$characters = array_keys($this->eveapi->characters());
        $menu = array();
		foreach ($characters as $v)
		{
		    $menu["index/{$v}"] = $v;
		}
		//$data['submenu'] = array();
		$data['page_title'] = 'Characters'; 

		//$data['search'] = (object) array('url' => 'assets/search', 'header' => 'Search Assets');
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
	
        $this->_template(array('content' => $this->load->view('skilltree', $data, True)));
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
        
        $this->_template(array('content' => $this->load->view('ships', array('canfly' => $canfly, 'character' => $character), True)));
    }

}

?>
