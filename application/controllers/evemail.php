<?php

class Evemail extends Controller
{
	public function __construct()
	{
		parent::__construct();
		
		$this->load->library("eveapi");
		
		$this->eveapi->load_characters();
	}


	public function index()
	{
		print '<pre>';
		
		$api = $this->eveapi->api;
		
	}
	
	public function rss_feed()
	{
		
		
	}	
	
	
}












?>