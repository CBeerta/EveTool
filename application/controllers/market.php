<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Market extends Controller
{
	public $page_title = 'Market';
	public $submenu = array();
	
	public function _remap($method)
	{
		$data['page_title'] = $this->page_title;
		$data['submenu'] = $this->submenu;
		
		$data['content'] = $this->$method();
		$this->load->view('template', $data);
	}
	
	public function index()
	{
		$orders = array();
		$api = $this->eveapi->api;
		$characters = $this->eveapi->load_characters();
		
        $data['remaining']['buy'] = $data['total']['buy'] = $data['remainingPrice']['buy'] = $data['totalPrice']['buy'] = 0;
        $data['remaining']['sell'] = $data['total']['sell'] = $data['remainingPrice']['sell'] = $data['totalPrice']['sell'] = 0;
        $data['sell'] = $data['buy'] = array();
        $typeidlist = array();
		
		foreach ($this->eveapi->characters as $char)
		{
			$api->setCredentials($char->apiUser, $char->apiKey, $char->characterID);
			$market = $api->char->MarketOrders();
			
			foreach ($market->result->orders as $_order)
			{
				$order = (object) $_order->attributes();
				
	            if ($order['orderState'] != 0)
	            {
	                /* Skip everything that is not currently aktive */
	                continue;
	            }
				
	            $issued = strtotime((string) $order['issued']);
	            $issued += (int) $order['duration'] * 24 * 60 * 60;
	            
	            $row = (array) get_inv_type((int)$order['typeID']);
	            
	            $row += array(
	            	'price' => (float) $order['price'],
	            	'remaining' => $order['volRemaining'],
	            	'total' => $order['volEntered'],
	            	'charID' => $order['charID'],
	            	'owner' => $char,
	            	'ends' => api_time_to_complete($issued),
	            	'location' => locationid_to_name($order['stationID']),
	            	'locationid' => $order['stationID'],
	            	'issued' => $issued,
	            	);
	            
	            $type = ($order['bid'] == 1) ? 'buy' : 'sell';
	            $typeidlist[] = $row['typeID'];
	
	            $data[$type][] = $row;
	            if ( $issued > gmmktime() )
	            {
	                $data['remaining'][$type] += $order['volRemaining'];
	                $data['total'][$type] += $order['volEntered'];
	                $data['remainingPrice'][$type] += $order['volRemaining']*$order['price'];
	                $data['totalPrice'][$type] += $order['volEntered']*$order['price'];
	            }
			}
			masort($data['buy'], array('issued'));
			masort($data['sell'], array('issued'));
		}
		return ($this->load->view('market', $data, true));
	}

}


?>
