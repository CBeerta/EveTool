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
		$data['submenu'] = $this->submenu;
		
		$data['content'] = $this->$method();
		$this->load->view('template', $data);
	}
	
	public function index()
	{
		$api = $this->eveapi->api;
		$characters = $this->eveapi->load_characters();
		
		$charinfo = array();
		//$this->eveapi->get_skilltree();
		
		foreach ($this->eveapi->characters as $char)
		{
			$api->setCredentials($char->apiUser, $char->apiKey, $char->characterID);
			
			if (!isset($charinfo[$char->name]))
			{
				$charinfo[$char->name] = (array) $char;
			}
			
			$_training = $api->char->SkillInTraining();
			
			//print_r($_training->result);
			if ((string) $_training->result->skillInTraining > 0)
			{
				foreach (array('trainingTypeID', 'trainingToLevel', 'trainingStartTime', 'trainingEndTime' ) as $n)
				{
					$charinfo[$char->name][$n] = (string) $_training->result->$n;
				}
			}
			
			$_charsheet = $api->char->CharacterSheet();
			
			//print_r($_charsheet->result);
			foreach (array('balance', 'corp', 'DoB', 'corporationName', 'allianceName', 'gender', '' ) as $n)
			{
				$charinfo[$char->name][$n] = (string) $_charsheet->result->$n;
			}
			
			if ($charinfo[$char->name]['gender'] == 'Male')
			{
				$charinfo[$char->name]['sex'] = 'He';
			}
			else
			{
				$charinfo[$char->name]['sex'] = 'She';
			}
		}		
		ksort($charinfo);
		
		return ($this->load->view('charoverview', array('data' => $charinfo), True));
	}

}












?>