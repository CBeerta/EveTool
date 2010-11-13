<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Assets extends Controller
{
	public $page_title = 'Assets';
	public $submenu = array('Wallet' => array('transactions' => 'Transaction List', 'journal' => 'Daily Journal'));

	private function _get_daily_walletjournal($wallet)
	{
		$data = array();
        $daily = array();
        
        $reftypes = $this->eveapi->get_reftypes();
        
        foreach ($wallet as $entry)
        {
            $day = api_time_print($entry['date'], 'Y-m-d');
            if (!isset($daily[$day]))
            {
                $total[$day]['prettydate'] = api_time_print($entry['date'], 'l, j F Y');
                $total[$day]['expense'] = 0;
                $total[$day]['income'] = 0;
            }
            if (!isset($daily[$day][$entry['refTypeID']]))
            {
            	
                $daily[$day][$entry['refTypeID']] = array(
                    'refTypeName' => $reftypes[$entry['refTypeID']],
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
			
			if (!isset($balance[$day]))
			{
				/* Wallet journal is chronoligcally ordered, so we just want the topmost daily entry as that is the "last for that day" */
				$balance[$day] = $entry['balance'];
			}
        }

        $data['daily'] = $daily;
        $data['total'] = $total;
		$data['balance'] = $balance;
		
		return ($data);
	}

	public function ajax_contents($itemID)
	{
		$characters = $this->eveapi->load_characters();
	    
        $assets = $this->eveapi->load_assets($characters, True);

        $contents = array();
        $index = 0;
        foreach ($assets[$itemID]['contents'] as $v)
        {
            ++$index;
            $contents[$index] = $assets[$v];
        }
        masort($contents, array('flag', 'categoryName'));

        $contents = array_merge(array($assets[$itemID]), $contents);

        $this->load->view('snippets/assets_content', array('contents' => $contents));
	}

	
	public function index($offset = 0, $per_page = 20)
	{
		$data['page_title'] = $this->page_title;
		$data['submenu'] = $this->submenu;
		
		$characters = $this->eveapi->load_characters();

        $assets = $this->eveapi->load_assets($characters, False);

		$total = count($assets);
		$assets = array_slice($assets, $offset, $per_page, True);
		$this->pagination->initialize(array('base_url' => site_url("/assets/index"), 'total_rows' => $total, 'per_page' => $per_page, 'num_links' => 5));
        
        $data['content'] = $this->load->view('assets', array('assets' => $assets), True);
		$this->load->view('template', $data);
	}

	public function journal()
	{
		$data['page_title'] = $this->page_title;
		$data['submenu'] = $this->submenu;
		
		$api = $this->eveapi->api;
		$characters = $this->eveapi->load_characters();
		
		$walletjournal = array();
		
		foreach ($this->eveapi->characters as $char)
		{
			$api->setCredentials($char->apiUser, $char->apiKey, $char->characterID);
			$walletjournal = array_merge($walletjournal, eveapi::from_xml($api->char->WalletJournal(), 'entries'));
		}
		masort($walletjournal, array('unixdate'));
		
		$data['content'] = $this->load->view('walletdailyjournal', $this->_get_daily_walletjournal($walletjournal), True);
		$this->load->view('template', $data);
	}
	
	public function transactions($offset = 0, $per_page = 20)
	{
		$data['page_title'] = $this->page_title;
		$data['submenu'] = $this->submenu;
		
		$api = $this->eveapi->api;
		$characters = $this->eveapi->load_characters();
		
		$transactionlist = array();
		
		foreach ($this->eveapi->characters as $char)
		{
			$api->setCredentials($char->apiUser, $char->apiKey, $char->characterID);
			$transactionlist = array_merge($transactionlist, eveapi::from_xml($api->char->WalletTransactions(), 'transactions', array('char' => $char)));
		}
		
		masort($transactionlist, array('unixtransactionDateTime'));
		$total = count($transactionlist);
		$data['translist'] = array_slice($transactionlist, $offset, $per_page, True);
		$this->pagination->initialize(array('base_url' => site_url("/assets/transactions"), 'total_rows' => $total, 'per_page' => $per_page, 'num_links' => 5));

		$data['content'] = $this->load->view('transactionlist', $data, True);
		$this->load->view('template', $data);
	}

}


?>
