<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Wallet extends MY_Controller
{
    public function chart($character = false)
    {
        $character = urldecode($character);
        if (!in_array($character, array_keys($this->chars)))
        {
            die("Could not find matchign char {$character}");
        }

        $this->load->library('phpgraphlib', array('width' => 800,'height' => 350));

        $data['character'] = $character;

        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);


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
                $chartdata[apiTimePrettyPrint($row['date'], 'j M')] = $row['balance'];
                $prevday = $day;
                $days++;
            }
            if ($days>30)
            {
                break;
            }
        }
        $this->phpgraphlib->addData($chartdata);
        $this->phpgraphlib->setBars(true);
        $this->phpgraphlib->setDataPoints(false);
        $this->phpgraphlib->setLine(false);
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
     * @param   string
     */
    public function journal($character = False)
    {
        $character = urldecode($character);
        if (!in_array($character, array_keys($this->chars)))
        {
            die("Could not find matchign char {$character}");
        }

        $data['character'] = $character;

        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);


        $walletxml = $this->eveapi->getWalletJournal();
        $data['wallet'] = WalletJournal::getWalletJournal($walletxml);
        $data['reftypes'] = $this->eveapi->reftypes;

        $template['title'] = "Wallet Journal for {$character}";
        $template['content'] = $this->load->view('walletjournal', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
