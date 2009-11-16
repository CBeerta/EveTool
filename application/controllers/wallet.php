<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Wallet Pages
 *
 * Various Functions to access Characters Wallet 
 *
 * @author Claus Beerta <claus@beerta.de>
 */

class Wallet extends MY_Controller
{
    /**
     * Chart the Characters last 30 days of Wallet History
     */
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
        $this->phpgraphlib->setBackgroundColor("160,166,136");
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
     * journal
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
    
    /**
     * daily journal
     *
     * Wallet Journal broken down on a Days basis
     *
     */
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
