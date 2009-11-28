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
        $typeidlist = array();
        
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
            $typeidlist[] = $row->typeID;

            $data[$type][] = $row;
            if ( $issued > gmmktime() )
            {
                $data['remaining'][$type] += $order['volRemaining'];
                $data['total'][$type] += $order['volEntered'];
                $data['remainingPrice'][$type] += $order['volRemaining']*$order['price'];
                $data['totalPrice'][$type] += $order['volEntered']*$order['price'];
            }
        }
        $regionID = !get_user_config($this->Auth['user_id'], 'market_region') ? 10000067 : get_user_config($this->Auth['user_id'], 'market_region');

        $data['prices'] = $this->evecentral->get_prices($typeidlist, $regionID);
        $template['content'] = $this->load->view('market', $data, True);
        $this->load->view('maintemplate', $template);
    }

    /**
     * fitting_helper
     *
     * Lets you PC EFT Fittings
     *
     * @param   string
     */
    public function fitting_helper($location = False)
    {
        $character = $this->character;
        $data['character'] = $character;
        $data['posted'] = '';
        
        if ($this->input->post("items"))
        {
            $items = $this->input->post("items");
            $data['posted'] = $items;
            $eft = explode("\n", $items);
                                    
            $types = array();
            for ($i = 0; $i < count($eft); $i++) 
            {
                $name = explode(',', $eft[$i]);
                $name = trim(stripslashes($name[0]));
                $name = str_replace(array('Drones_Active=', '['), '', $name);
                
                if (!empty($name))
                {
                    $type = get_inv_type($name);
                    
                    if ($type->typeID == -1)
                    {
                        $data['errors'][] = $name;
                        continue;
                    }
                    else if (!empty($types[$type->typeID]))
                    {
                        $types[$type->typeID]->amount ++;
                    }
                    else
                    {
                        $types[$type->typeID] = $type;
                        $types[$type->typeID]->amount = 1;
                    }
                }
            }
            $prices = $this->evecentral->get_prices(array_keys($types));
            
            $data['types'] = $types;
            $data['prices'] = $prices;
            $data['total_volume'] = $data['total_buy'] = $data['total_sell'] = 0;
            
            foreach ($types as $k => $v)
            {
                $data['total_sell'] += $prices[$k]['sell']['median'] * $v->amount;
                $data['total_buy'] += $prices[$k]['buy']['median'] * $v->amount;
                $data['total_volume'] += $v->volume * $v->amount;
            }
        }
        $template['content'] = $this->load->view('fitting_helper', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
