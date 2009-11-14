<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Market extends MY_Controller
{
    /**
     * orders
     *
     * Display a Table with active Market Orders for the selected Character
     *
     * @param   string
     */
    public function orders($days = 7)
    {
        $character = $this->character;
        $data['character'] = $character;
        
        $char_orders = MarketOrders::getMarketOrders($this->eveapi->getMarketOrders());
        if ($this->has_corpapi_access && getUserConfig($this->Auth['user_id'], 'pull_corp'))
        {
            $corp_orders = MarketOrders::getMarketOrders($this->eveapi->getMarketOrders(True));
            $data['corpmates'] = $this->eveapi->corp_members;
        }

        $orders = empty($char_orders) ? array() : $char_orders;
        if (!empty($corp_orders))
        {
            $orders = array_merge($char_orders, $corp_orders);
        }
        
        $data['remaining']['buy'] = $data['total']['buy'] = $data['remainingPrice']['buy'] = $data['totalPrice']['buy'] = 0;
        $data['remaining']['sell'] = $data['total']['sell'] = $data['remainingPrice']['sell'] = $data['totalPrice']['sell'] = 0;
        $data['sell'] = $data['buy'] = array();
        
        foreach ($orders as $order)
        {
            if ($order['orderState'] != 0)
            {
                /* Skip everything that is not currently aktive */
                continue;
            }
       
            $issued = strtotime($order['issued']);
            $issued += $order['duration'] * 24 * 60 * 60;

            $row = get_inv_type($order['typeID']);
            $row->price = $order['price'];
            $row->remaining = $order['volRemaining'];
            $row->total = $order['volEntered'];
            $row->charID = $order['charID'];
            $row->ends = api_time_to_complete($issued);
            $row->location = locationid_to_name($order['stationID']);
            $row->locationid = $order['stationID'];
            
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

        $template['content'] = $this->load->view('market', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
