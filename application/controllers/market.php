<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Market extends Controller
{
	public $page_title = 'Market';
	public $submenu = array();
	
	public function _remap($method)
	{
		$data['page_title'] = $this->page_title;
		$data['submenu'] = $this->submenu;
		
		$data['content'] = $this->$method();
		$this->load->view('template', $data);
	}
	
	public function index()
	{
		return ('whut');
	}
	
	
}


?>