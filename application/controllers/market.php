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
    public function orders($character = False)
    {
        $character = urldecode($character);
        if (!in_array($character, array_keys($this->chars)))
        {
            die("Could not find matchign char {$character}");
        }
        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);
        $orders = MarketOrders::getMarketOrders($this->eveapi->getMarketOrders());
        $data['character'] = $character;

        $index = 0;
        $data['remaining'] = $data['total'] = $data['remainingPrice'] = $data['totalPrice'] = 0;
        $data['data'] = array();
        foreach ($orders as $order)
        {
            $issued = strtotime($order['issued']);
            $issued += $order['duration'] * 24 * 60 * 60;

            $data['data'][$index] = array(
                    'typeName' => getInvType($order['typeID'])->typeName,
                    'typeID' => $order['typeID'],
                    'price' => $order['price'],
                    'remaining' => $order['volRemaining'],
                    'total' => $order['volEntered'],
                    'ends' => timeToComplete($issued),
                    'location' => locationIDToName($order['stationID'])
                );
            $data['remaining'] += $order['volRemaining'];
            $data['total'] += $order['volEntered'];
            $data['remainingPrice'] += $order['volRemaining']*$order['price'];
            $data['totalPrice'] += $order['volEntered']*$order['price'];
            $index++;
        }
        $template['content'] = $this->load->view('market', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
