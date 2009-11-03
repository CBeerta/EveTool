<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cli extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->config->load('evetool');
		$this->load->library('eveapi', array('cachedir' => '/var/tmp'));

        $this->load->helper('eve');
    }
    
	public function cron_update()
	{
		print date('r')." - Updating XML Cache:\n";
	
		$accounts = array();
		$q = $this->db->query('SELECT apiUser, apiFullKey FROM asc_apikeys');
		$index = 0;
		
		$what_to_update = array (
			'Account Balance' => 'getAccountBalance',
			'Skill in Training' => 'getSkillInTraining',
			'Character Sheet' => 'getcharactersheet',
			'Skill Queue' => 'getSkillQueue',
			'Wallet Transactions' => 'getWalletTransactions',
			'Wallet Journal' => 'getWalletJournal',
			'Industry Jobs' => 'getIndustryJobs',
			'Market Orders' => 'getMarketOrders',
			'Standings' => 'getStandings',
		);

		foreach ($q->result()as $row)
		{   
            $this->eveapi->setCredentials($row->apiUser, $row->apiFullKey);
            $chars = Characters::getCharacters($this->eveapi->getCharacters());
			
            if (!is_array($chars))
            {
                continue;
            }
            foreach ($chars as $char)
            {   
				print " - {$char['charname']}: ";
				$this->eveapi->setCredentials($row->apiUser, $row->apiFullKey, $char['charid']);
				
				foreach ($what_to_update as $k => $v)
				{
					print " {$k}";
					$this->eveapi->$v();
					if ($this->eveapi->getCacheStatus() === True)
					{
						print "(C)";
					}
				}
				print "\n";
			}	
		}
	}
	
	
}

?>
