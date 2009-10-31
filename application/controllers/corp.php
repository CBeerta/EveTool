<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Corp extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
        
		if (!$this->has_corpapi_access)
		{
			redirect("/overview/skilltree");
		}		
	}
	
	public function memberlist()
	{
		$data = array();
		
		$data["memberlist"] = MemberTrack::getMembers($this->eveapi->getMemberTracking());
		$data["corpname"] = $this->chars[$this->character]["corpname"];
        $template['content'] = $this->load->view('corp/memberlist', $data, True);
        $this->load->view('maintemplate', $template);
	}
}