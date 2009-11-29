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
        //$this->phpgraphlib->setBackgroundColor("76,76,76");
        //$this->phpgraphlib->setGridColor("white");
        $this->phpgraphlib->setGrid(false);
        $this->phpgraphlib->addData($chartdata);
        $this->phpgraphlib->setBars(false);
        $this->phpgraphlib->setDataPoints(true);
        $this->phpgraphlib->setDataValues(true);
        $this->phpgraphlib->setLine(true);
        //$this->phpgraphlib->setLineColor("black");
        //$this->phpgraphlib->setDataValueColor("200,200,200");
        //$this->phpgraphlib->setLegendColor("200,200,200");
        //$this->phpgraphlib->setTextColor("200,200,200");
        $this->phpgraphlib->setGradient("red", "maroon");
        $this->phpgraphlib->setTitle("30 Day Wallet History");
        //$this->phpgraphlib->setTitleColor("200,200,200");

        $this->phpgraphlib->createGraph();
        exit;
    }
    
    /**
     * journal
     *
     * Display a Journal with the latest Wallet Transactions
     *
     */
    public function journal($offset = 0, $per_page = 20)
    {
        $data['character'] = $this->character;
        if ($offset === $this->character || $per_page === $this->character)
        {
            // @todo we should really get a proper solution for this
            redirect(site_url("/wallet/journal"));
        }

        $walletxml = $this->eveapi->getWalletJournal();
        $data['wallet'] = WalletJournal::getWalletJournal($walletxml);
        
        $total = count($data['wallet']);
        $data['wallet'] = array_slice($data['wallet'], $offset, $per_page, True);
        $this->pagination->initialize(array('base_url' => site_url("/wallet/journal"), 'total_rows' => $total, 'per_page' => $per_page, 'num_links' => 5));
        
        $data['reftypes'] = $this->eveapi->reftypes;
				
        $template['title'] = "Wallet Journal for {$this->character}";
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
	    $data['character'] = $this->character;

        $walletxml = $this->eveapi->getWalletJournal();
        $wallet = WalletJournal::getWalletJournal($walletxml);

		$data = $this->eveapi->get_daily_walletjournal($wallet);
	
        $template['title'] = "Wallet Journal for {$this->character}";
        $template['content'] = $this->load->view('walletdailyjournal', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
