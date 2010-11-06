<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Characters extends Controller
{
	public $page_title = 'Characters';
	public $submenu;

	public function __construct()
	{
		parent::__construct();
		
		$this->submenu = $this->eveapi->load_characters();
	}
	
	public function _remap($method)
	{
		$data['page_title'] = $this->page_title;
		$data['submenu'] = $this->submenu;
		
		$data['content'] = $method;
		$this->load->view('template', $data);
	}

}












?>