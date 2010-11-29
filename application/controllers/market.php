<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Market extends Controller
{
    /**
    *
    * Load the Template and add submenus
    *
    * @access private
    * @param array $data contains the stuff handed over to the template
    **/
	private function _template($data)
	{
		$characters = array_keys($this->eveapi->characters());
        $menu = array();
		foreach ($characters as $v)
		{
		    $menu["index/{$v}"] = $v;
		}
		$data['submenu'] = array('Sections' => array('index' => 'Active Orders', 'past_orders' => 'Past Orders'));
		$data['page_title'] = 'Market'; 

		$this->load->view('template', $data);
	}

    /**
    * Active Orders
    *
    * @access public
    **/	
	public function index()
	{
		$orders = array();
		
        $data['remaining']['buy'] = $data['total']['buy'] = $data['remainingPrice']['buy'] = $data['totalPrice']['buy'] = 0;
        $data['remaining']['sell'] = $data['total']['sell'] = $data['remainingPrice']['sell'] = $data['totalPrice']['sell'] = 0;
        $data['sell'] = $data['buy'] = array();
		
		foreach ($this->eveapi->characters() as $char)
		{
			$this->eveapi->setCredentials($char);
			$market = $this->eveapi->MarketOrders();
			
			foreach ($market->result->orders as $_order)
			{
				$order = (object) $_order->attributes();
				
	            if ($order['orderState'] != 0)
	            {   /* Skip everything that is not currently aktive */
	                continue;
	            }
				
	            $issued = strtotime((string) $order['issued']);
	            $issued += (int) $order['duration'] * 24 * 60 * 60;
	            
	            $row = array(
	                'typeID' => (int) $order['typeID'],
	            	'price' => (float) $order['price'],
	            	'remaining' => $order['volRemaining'],
	            	'total' => $order['volEntered'],
	            	'charID' => $order['charID'],
	            	'owner' => $char,
	            	'ends' => api_time_to_complete($issued),
	            	'stationID' => $order['stationID'],
	            	'issued' => $issued,
	            	);
	            
	            $type = ($order['bid'] == 1) ? 'buy' : 'sell';
	
	            $data[$type][] = $row;
	            if ( $issued > gmmktime() )
	            {
	                $data['remaining'][$type] += $order['volRemaining'];
	                $data['total'][$type] += $order['volEntered'];
	                $data['remainingPrice'][$type] += $order['volRemaining']*$order['price'];
	                $data['totalPrice'][$type] += $order['volEntered']*$order['price'];
	            }
			}
			$data['buy'] = array_add_invtypes($data['buy']);
			$data['sell'] = array_add_invtypes($data['sell']);

			masort($data['buy'], array('issued'));
			masort($data['sell'], array('issued'));
		}
        $this->_template(array('content' => $this->load->view('market', $data, true)));
	}

    /**
    * All Past Orders
    *
    * @access public
    **/	
	public function past_orders($offset = 0, $per_page = 15)
	{
        $past_orders = array();

		foreach ($this->eveapi->characters() as $char)
		{
			$this->eveapi->setCredentials($char);
			$market = $this->eveapi->MarketOrders();

			foreach ($market->result->orders as $_order)
			{
				$order = (object) $_order->attributes();

	            if (in_array($order['orderState'], array(0,1,3)))
	            {   // Skip Active and Cancelled
	                continue;
	            }

	            $issued = strtotime((string) $order['issued']);
	            $issued += (int) $order['duration'] * 24 * 60 * 60;
	            
	            //$row = (array) get_inv_type((int)$order['typeID']);
	            
	            $row = array(
	                'typeID' => (int) $order['typeID'],
	            	'price' => (float) $order['price'],
	            	'remaining' => $order['volRemaining'],
	            	'total' => $order['volEntered'],
	            	'charID' => $order['charID'],
	            	'owner' => $char,
	            	'ends' => api_time_to_complete($issued),
	            	'stationID' => $order['stationID'],
	            	'issued' => $issued,
	            	);
	            $type = ($order['bid'] == 1) ? 'buy' : 'sell';

	            $row['type'] = $type;
	            
	            $past_orders[] = $row;
			}
			masort($past_orders, array('issued'));
		}

		$past_orders = array_add_invtypes($past_orders);

		$total = count($past_orders);
		$data['past_orders'] = array_slice($past_orders, $offset, $per_page, True);
		$this->pagination->initialize(array('base_url' => site_url("/market/past_orders"), 'total_rows' => $total, 'per_page' => $per_page, 'num_links' => 5, 'uri_segment' => 3));

        $this->_template(array('content' => $this->load->view('past_orders', $data, true)));
	}


}


?>
