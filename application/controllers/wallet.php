<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Wallet extends MY_Controller
{
    public function chart()
    {
        $character = $this->character;
        $this->load->library('phpgraphlib', array('width' => 800,'height' => 350));
        $data['character'] = $character;

        $walletxml = $this->eveapi->getWalletJournal();
        $data['wallet'] = WalletJournal::getWalletJournal($walletxml);
        $data['reftypes'] = $this->eveapi->reftypes;

        $chartdata = array();
        $prevday = -1;
        $days = 0;
        foreach ($data['wallet'] as $row)
        {
            $day = date('z', strtotime($row['date']));
            if ($day != $prevday)
            {
                $chartdata[api_time_print($row['date'], 'j M')] = round($row['balance'] / 1000.0 / 1000.0) ;
                $prevday = $day;
                $days++;
            }
            if ($days>30)
            {
                break;
            }
        }
        $this->phpgraphlib->addData($chartdata);
        $this->phpgraphlib->setBars(false);
        $this->phpgraphlib->setDataPoints(true);
        $this->phpgraphlib->setDataValues(true);
        $this->phpgraphlib->setLine(true);
        $this->phpgraphlib->setGradient("red", "maroon");
        $this->phpgraphlib->setTitle("30 Day Wallet History");

        $this->phpgraphlib->createGraph();
        exit;
    }
    
    /**
     * walletjournal
     *
     * Display a Journal with the latest Wallet Transactions
     *
     */
    public function journal()
    {
        $character = $this->character;
        $data['character'] = $character;

        $walletxml = $this->eveapi->getWalletJournal();
        $data['wallet'] = WalletJournal::getWalletJournal($walletxml);
        $data['reftypes'] = $this->eveapi->reftypes;
				
        $template['title'] = "Wallet Journal for {$character}";
        $template['content'] = $this->load->view('walletjournal', $data, True);
        $this->load->view('maintemplate', $template);
    }

    public function dailyjournal()
    {
        $character = $this->character;

        $walletxml = $this->eveapi->getWalletJournal();
        $wallet = WalletJournal::getWalletJournal($walletxml);

		$data = $this->eveapi->get_daily_walletjournal($wallet);
	    $data['character'] = $character;
	
        $template['title'] = "Wallet Journal for {$character}";
        $template['content'] = $this->load->view('walletdailyjournal', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
