<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function name_cmp($a, $b)
{
	return strcmp($a["name"], $b["name"]);	
}


class Corp extends MY_Controller
{
	private $corpname = "Acme Corp";
	
	public function __construct()
	{
		parent::__construct();
        
		if (!$this->has_corpapi_access)
		{
			redirect("/overview/skilltree");
		}
		$this->corpname = $this->chars[$this->character]["corpname"];		
	}
	
	public function memberlist()
	{
		$data = array();
		$data["memberlist"] = MemberTrack::getMembers($this->eveapi->getMemberTracking());
		
		usort($data["memberlist"], "name_cmp");
		$data["corpname"] = $this->chars[$this->character]["corpname"];
        $template['content'] = $this->load->view('corp/memberlist', $data, True);
        $this->load->view('maintemplate', $template);
	}
	
	public function member_detail()
	{
		$data = array();
		
		//$data["member"] = Titles::getTitles($this->eveapi->getTitles());
		
        $template['content'] = $this->load->view('corp/member_detail', $data, True);
        $this->load->view('maintemplate', $template);
	}
	
	public function wallet()
	{
		$data = array();
		
        $walletxml = $this->eveapi->getWalletJournal(null, True);
        $wallet = WalletJournal::getWalletJournal($walletxml);

		if ($wallet == null)
		{
			$template["content"] = '<h1>Unable to Retrieve Corporation Wallet Journal. Do you have the neccessary Roles?</h1>';
		}
		else
		{
			$data = $this->eveapi->get_daily_walletjournal($wallet);
			$data["corpname"] = $this->corpname;
	        $template['content'] = $this->load->view('walletdailyjournal', $data, True);
		}
        $this->load->view('maintemplate', $template);
	}
	
    public function transactions()
    {
        $character = $this->character;
        $data['character'] = $character;

        $transxml = $this->eveapi->getWalletTransactions(null, True);
        $data['translist'] = WalletTransactions::getWalletTransactions($transxml);
		if ($data['translist'] == null)
		{
			$template["content"] = '<h1>Transaction Journal is Empty. Do you have the neccessary Roles?</h1>';
		}
		else
		{
        	$template['title'] = "Transactionlist for {$character}";
        	$template['content'] = $this->load->view('transactionlist', $data, True);
		}
        $this->load->view('maintemplate', $template);
    }
	
}