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
        $data['character'] = $character;

        $walletxml = $this->eveapi->getWalletJournal();
        $wallet = WalletJournal::getWalletJournal($walletxml);

        $daily = array();

        foreach ($wallet as $entry)
        {
            $day = apiTimePrettyPrint($entry['date'], 'Y-m-d');
            if (!isset($daily[$day]))
            {
                $total[$day]['prettydate'] = apiTimePrettyPrint($entry['date'], 'l, j F Y');
                $total[$day]['expense'] = 0;
                $total[$day]['income'] = 0;
            }
            if (!isset($daily[$day][$entry['refTypeID']]))
            {
                $daily[$day][$entry['refTypeID']] = array(
                    'refTypeName' => $this->eveapi->reftypes[$entry['refTypeID']],
                    );
            }
            if (!isset($daily[$day][$entry['refTypeID']]['expense']))
            {
                $daily[$day][$entry['refTypeID']]['expense'] = 0;
                $daily[$day][$entry['refTypeID']]['income'] = 0;

            }

            if ($entry['amount'] < 0)
            {
                $daily[$day][$entry['refTypeID']]['expense'] += $entry['amount'];
                $total[$day]['expense'] += $entry['amount'];
            }
            else
            {
                $daily[$day][$entry['refTypeID']]['income'] += $entry['amount'];
                $total[$day]['income'] += $entry['amount'];
            }
        }

        $data['daily'] = $daily;
        $data['total'] = $total;

        $template['title'] = "Wallet Journal for {$character}";
        $template['content'] = $this->load->view('walletdailyjournal', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
