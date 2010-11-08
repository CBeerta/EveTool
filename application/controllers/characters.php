<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Characters extends Controller
{
	public $page_title = 'Characters';
	public $submenu;

	public function __construct()
	{
		parent::Controller();
		
		$this->submenu = $this->eveapi->load_characters();
	}
	
	public function _remap($method)
	{
		$data['page_title'] = $this->page_title;
		//$data['submenu'] = $this->submenu;
		
		$data['content'] = $this->$method();
		$this->load->view('template', $data);
	}
	
	public function index()
	{
		$api = $this->eveapi->api;
		$characters = $this->eveapi->load_characters();
		
		$charinfo = array();
		$skilltree = $this->eveapi->get_skilltree();
		
		$global['totalisk'] = $global['totalsp'] = 0;
		
		foreach ($this->eveapi->characters as $char)
		{
			$api->setCredentials($char->apiUser, $char->apiKey, $char->characterID);
			
			if (!isset($charinfo[$char->name]))
			{
				$charinfo[$char->name] = (array) $char;
			}

			$_training = $api->char->SkillInTraining();
			if ((string) $_training->result->skillInTraining > 0)
			{
				foreach (array('trainingTypeID', 'trainingToLevel', 'trainingStartTime', 'trainingEndTime' ) as $n)
				{
					$charinfo[$char->name][$n] = (string) $_training->result->$n;
				}
				$charinfo[$char->name]['trainingTypeName'] = $skilltree[(string) $_training->result->trainingTypeID];
			}

			$_charsheet = $api->char->CharacterSheet();
			$charinfo[$char->name]['extra_info'] = eveapi::charsheet_extra_info($_charsheet);
			foreach (array('balance', 'corp', 'DoB', 'corporationName', 'allianceName', 'gender' ) as $n)
			{
				$charinfo[$char->name][$n] = (string) $_charsheet->result->$n;
			}
			
			if ($charinfo[$char->name]['gender'] == 'Male')
			{
				$charinfo[$char->name]['sex'] = 'He';
				$charinfo[$char->name]['sex2'] = 'His';
			}
			else
			{
				$charinfo[$char->name]['sex'] = 'She';
				$charinfo[$char->name]['sex2'] = 'Her';
			}
			
			$global['totalisk'] += $charinfo[$char->name]['balance'];
			$global['totalsp'] += $charinfo[$char->name]['extra_info']['skillPointsTotal'];
		}		
		ksort($charinfo);
		return ($this->load->view('charoverview', array('data' => $charinfo, 'global' => $global), True));
	}

}

?>
