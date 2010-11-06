<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends Controller
{
	public $page_title = 'Home';
	public $submenu = array('EveMail');
	
	public function _remap($method)
	{
		$data['page_title'] = $this->page_title;
		$data['submenu'] = $this->submenu;
		
		$data['content'] = $this->$method();
		$this->load->view('template', $data);
	}
	
	public function index()
	{
		$content = $this->load->view('home', '', true);
		
		return ($content);
	}
	
	public function evemail()
	{
		$content = "You need Viagra, ASAP!";
		
		return ($content);
	}

}


?>