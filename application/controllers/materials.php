<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Materials extends MY_Controller
{

    public $groupList = array(18, 754, /* 886,*/ 334, 873, 913, 427, 428, 429, 465, 423);
    
    /**
     * materials loader
     *
     * Loads the materials from db for our json request
     *
     * @param   int
     */
    public function load($groupID)
    {
        $character = $this->character;

        $regionID = !get_user_config($this->Auth['user_id'], 'market_region') ? 10000067 : get_user_config($this->Auth['user_id'], 'market_region');
        $custom_prices = $this->input->post('custom_prices') ? True : False;
        $data['custom_prices'] = $custom_prices;

        $data['sums']['volume'] = $data['sums']['sellprice'] = $data['sums']['buyprice'] = 0;
        $assets = AssetList::getAssetsFromDB($this->chars[$character]['charid'], array("invGroups.groupID" => $groupID));
        
        foreach ($assets as $loc)
        {
            foreach ($loc as $asset)
            {
                if ($asset['groupID'] == $groupID) 
                {
                    if (!isset($data['data'][$asset['typeID']]))
                    {
                        $data['data'][$asset['typeID']] = array_merge($asset ,array('quantity' => 0));
                    }
                    $data['data'][$asset['typeID']]['quantity'] += $asset['quantity'];
                }
                if (isset($asset['contents']))
                {
                    foreach ($asset['contents'] as $content)
                    {
                        if ($content['groupID'] == $groupID) 
                        {
                            if (!isset($data['data'][$content['typeID']]))
                                $data['data'][$content['typeID']] = array_merge($content ,array('quantity' => 0));
                            $data['data'][$content['typeID']]['quantity'] += $content['quantity'];
                        }
                    }
                }
            }
        }
        
        /**
         * @todo Do we want to save this into the database?
         **/
        if (is_numeric($this->input->post('content')))
        {
            $ordered = array_merge($data['data'], array());        // yay, reset the index from typeids to numbered for our jquery update
            $to_update = $ordered[$this->input->post('n')];
            $data['data'][$to_update['typeID']]['quantity'] = $this->input->post('content');
        }
        $data['prices'] = $this->evecentral->getPrices(array_keys($data['data']), $regionID, $custom_prices);
        foreach ($data['data'] as $k => $v)
        {
            $data['sums']['volume'] += $v['volume']*$v['quantity'];
            $data['sums']['sellprice'] += $v['quantity']*$data['prices'][$k]['sell']['median'];
            $data['sums']['buyprice'] += $v['quantity']*$data['prices'][$k]['buy']['median'];
        }
                    
        echo json_encode($data);
        exit;
    }
    
    /**
     * materials
     *
     * Display a Table with the Materials, Amounts and Values defined by ?groupID=
     *
     * @param   int
     */
    public function index($groupID = 18)
    {
        $regionID = !get_user_config($this->Auth['user_id'], 'market_region') ? 10000067 : get_user_config($this->Auth['user_id'], 'market_region');
        
        //FIXME: Make it possible again to check for categoryID's
        $sID = 'groupID'; // What ID to search for
        
        if ($this->input->post('groupID'))
        {
            $groupID = $this->input->post('groupID');
            redirect(site_url("materials/index/".$groupID));
            exit;
        }
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
                $data['caption'] .= ' - Prices from the "'.regionid_to_name($regionID).'" region';
            }
            if (in_array($row->groupID, $this->groupList))
            {
                $groupIDList[$row->groupID] = $row->groupName;
            }
  
        }
        $data['groupIDList'] = $groupIDList;
        $data['caption'] = 'Materials - Prices from the "'.regionid_to_name($regionID).'" region';
        
        $q = $this->db->query('
            SELECT 
                * 
            FROM 
                invTypes,
                invGroups,
                eveGraphics
            WHERE 
                invTypes.graphicID=eveGraphics.graphicID AND
                invTypes.marketGroupID IS NOT NULL AND
                invTypes.groupID=invGroups.groupID AND 
                invTypes.groupID = ?', $groupID);
        $data['types'] = $q->result_array();

        foreach ($data['types'] as $r)
        {
            $typeIDList[$r['typeID']] = $r['typeName'];
        }
        $data['prices'] = $this->evecentral->getPrices(array_keys($typeIDList), $regionID, $custom_prices);

        $template['content'] = $this->load->view('materials', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
