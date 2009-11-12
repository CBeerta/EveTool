<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Materials extends MY_Controller
{

    public $groupList = array(18, 754, /* 886,*/ 334, 873, 913, 427, 428, 429, 465, 423);

    /**
     * minerals
     *
     * Display a Table with the Materials, Amounts and Values defined by ?groupID=
     *
     * @param   string
     */
    public function index($groupID = 18, $character = False)
    {
        $character = $this->character; 
        $data['character'] = $character;

        $regionID = !get_user_config($this->Auth['user_id'], 'market_region') ? 10000067 : get_user_config($this->Auth['user_id'], 'market_region');
        
        //FIXME: Make it possible again to check for categoryID's
        $sID = 'groupID'; // What ID to search for
        
        $groupID = $this->input->post('groupID') ? $this->input->post('groupID') : $groupID;
        $custom_prices = $this->input->post('custom_prices') ? True : False;
        $data['custom_prices'] = $custom_prices;
        
        $data['groupID'] = $groupID;

        $groupIDList = array();
        $q = $this->db->query('SELECT groupID,groupName FROM invGroups;');
        foreach ($q->result() as $row)
        {
            if ($row->groupID == $groupID)
            {
                $data['caption'] = $row->groupName;
                $data['caption'] .= ' - Prices from the "'.regionid_snippet($regionID).'" region';
            }
            if (in_array($row->groupID, $this->groupList))
            {
                $groupIDList[$row->groupID] = $row->groupName;
            }
        }
        $data['groupIDList'] = $groupIDList;
        $data['data'] = $typeIDList = array();
        $data['sums']['volume'] = $data['sums']['sellprice'] = $data['sums']['buyprice'] = 0;

        $assets = AssetList::getAssetsFromDB($this->chars[$character]['charid'], array("invGroups.{$sID}" => $$sID));

        foreach ($assets as $loc)
        {
            foreach ($loc as $asset)
            {
                if ($asset[$sID] == $$sID) 
                {
                    if (!isset($data['data'][$asset['typeID']]))
                        $data['data'][$asset['typeID']] = array_merge($asset ,array('quantity' => 0));
                    $data['data'][$asset['typeID']]['quantity'] += $asset['quantity'];
                }
                if (isset($asset['contents']))
                {
                    foreach ($asset['contents'] as $content)
                    {
                        if ($content[$sID] == $$sID) 
                        {
                            if (!isset($data['data'][$content['typeID']]))
                                $data['data'][$content['typeID']] = array_merge($content ,array('quantity' => 0));
                            $data['data'][$content['typeID']]['quantity'] += $content['quantity'];
                        }
                    }
                }
            }
        }
        $data['prices'] = $this->evecentral->getPrices(array_keys($data['data']), $regionID, $custom_prices);
        foreach ($data['data'] as $k => $v)
        {
            $data['sums']['volume'] += $v['volume']*$v['quantity'];
            $data['sums']['sellprice'] += $v['quantity']*$data['prices'][$k]['sell']['median'];
            $data['sums']['buyprice'] += $v['quantity']*$data['prices'][$k]['buy']['median'];
        }
        $data['caption'] = 'Materials - Prices from the "'.regionid_snippet($regionID).'" region';

        $template['content'] = $this->load->view('materials', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
