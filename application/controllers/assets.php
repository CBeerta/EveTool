<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Assets extends Controller
{
    /**
    * Page Title
    **/
	public $page_title = 'Assets';

	/**
	* Contents for the sidebar submenu
	**/
	public $submenu = array('Wallet' => array('transactions' => 'Transaction List', 'journal' => 'Daily Journal'));

    /**
    *
    * Load the Template and add submenus
    *
    * @access private
    * @param array $data contains the stuff handed over to the template
    **/
	private function _template($data)
	{
		$characters = $this->eveapi->load_characters();
        $menu = array();
		foreach ($characters as $v)
		{
		    $menu["index/{$v}"] = $v;
		}
		$data['submenu'] = array_merge(array('Inventory' => $menu), $this->submenu);
		$data['page_title'] = 'Assets'; 

		$data['search'] = (object) array('url' => 'assets/search', 'header' => 'Search Assets');
		$this->load->view('template', $data);
	}

    /**
    * 
    * Rebuild the wallet xml to a daily array
    *
    * @access private
    * @param object AleXML of the wallet
    **/    
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

    /**
    *
    * Loads the ajax snippet to display asset contents
    *
    * @access public
    * @param int $itemID ID of the container to look into
    **/
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

    /**
    *
    * Display Assets fo All Characters or a specific Character
    *
    * @access public
    * @param string $character All for all, otherwise Charname
    **/
	public function index($character = 'All', $offset = 0, $per_page = 20)
	{
		$characters = $this->eveapi->load_characters();

        if ($character == 'All' || !in_array($character, $characters))
        {
    		$data['caption'] = "Assets for All Characters";
            $assets = $this->eveapi->load_assets($characters, False);
        }
        else
        {
    		$data['caption'] = "Assets for {$character}";
            $assets = $this->eveapi->load_assets(array($character), False);
        }
        
		$total = count($assets);
		$data['assets'] = array_slice($assets, $offset, $per_page, True);
		$this->pagination->initialize(array('base_url' => site_url("/assets/index/{$character}"), 'total_rows' => $total, 'per_page' => $per_page, 'num_links' => 5, 'uri_segment' => 4));
        $this->_template(array('content' => $this->load->view('assets', $data, True)));
	}

    /**
    *
    * Search for assets
    *
    * @access public
    **/
	public function search()
	{
		$data['page_title'] = $this->page_title;
		$data['submenu'] = $this->submenu;

		$characters = $this->eveapi->load_characters();

        $assets = $this->eveapi->load_assets($characters, True);

        $search = $this->input->post('s');
        $found = array();

        foreach ($assets as $k => $v)
        {
            foreach (array('typeName', 'groupName', 'categoryName' /*, 'description' */) as $field)
            {
                similar_text(strtolower($v[$field]), strtolower($search), &$percent);
                if ($percent > 80)
                {
                    $found[$k] = $v;
                }
                else if (strpos(strtolower($v[$field]), strtolower($search)) !== False)
                {
                    $found[$k] = $v;
                }
            }
        }
        $data['assets'] = $found;
        $data['show_contents'] = True;
        $data['caption'] = "Results for '{$search}'";

        $this->_template(array('content' => $this->load->view('assets', $data, True)));
	}

    /**
    *
    * Wallet Journal as daily view
    *
    * @access public
    **/
	public function journal()
	{
		$api = $this->eveapi->api;
		$this->eveapi->load_characters();
		
		$walletjournal = array();
		
		foreach ($this->eveapi->characters as $char)
		{
			$api->setCredentials($char->apiUser, $char->apiKey, $char->characterID);
			$walletjournal = array_merge($walletjournal, eveapi::from_xml($api->char->WalletJournal(), 'entries'));
		}
		masort($walletjournal, array('unixdate'));
		
		$data['content'] = $this->load->view('walletdailyjournal', $this->_get_daily_walletjournal($walletjournal), True);
        $this->_template($data);
	}

    /**
    *
    * Wallet Transactions
    *
    * @access public
    **/
	public function transactions($offset = 0, $per_page = 20)
	{
		$api = $this->eveapi->api;
		$this->eveapi->load_characters();
		
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
        $this->_template($data);
	}

}


?>
