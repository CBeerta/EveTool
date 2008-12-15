<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Materials extends MY_Controller
{

    var $groupList = array(18, 754, /* 886,*/ 334, 873, 913, 427, 428, 429, 465, 423);

    /**
     * minerals
     *
     * Display a Table with the Materials, Amounts and Values defined by ?groupID=
     *
     * @param   string
     */
    public function index($groupID, $character)
    {
        $character = $this->character; 
        $data['character'] = $character;

/*      FIXME: doesnt work anymoar */
//        $groupID = !isset($_REQUEST['groupID']) ? '18' : $_REQUEST['groupID'];
//        $categoryID = !isset($_REQUEST['categoryID']) ? '25' : $_REQUEST['categoryID'];

        $regionID = !getUserConfig($this->Auth['user_id'], 'market_region') ? 10000067 : getUserConfig($this->Auth['user_id'], 'market_region');
        $sID = !isset($_REQUEST['categoryID']) ? 'groupID' : 'categoryID';

        $groupIDList = array();
        $q = $this->db->query('SELECT groupID,groupName FROM invGroups;');
        foreach ($q->result() as $row)
        {
            if ($row->groupID == $groupID)
            {
                $data['caption'] = $row->groupName;
                $data['caption'] .= ' - Prices from the "'.regionIDToName($regionID).'" region';
            }
            if (in_array($row->groupID, $this->groupList))
            {
                $groupIDList[] = array('groupID' => $row->groupID, 'groupName' => $row->groupName);
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
                        $data['data'][$asset['typeID']] = array('volume' => $asset['volume'], 'quantity' => 0, 'typeName' => $asset['typeName']);
                    $data['data'][$asset['typeID']]['quantity'] += $asset['quantity'];
                }
                if (isset($asset['contents']))
                {
                    foreach ($asset['contents'] as $content)
                    {
                        if ($content[$sID] == $$sID) 
                        {
                            if (!isset($data['data'][$content['typeID']]))
                                $data['data'][$content['typeID']] = array('volume' => $content['volume'], 'quantity' => 0, 'typeName' => $content['typeName']);
                            $data['data'][$content['typeID']]['quantity'] += $content['quantity'];
                        }
                    }
                }
            }
        }
        $data['prices'] = $this->evecentral->getPrices(array_keys($data['data']), $regionID);
        foreach ($data['data'] as $k => $v)
        {
            $data['sums']['volume'] += $v['volume']*$v['quantity'];
            $data['sums']['sellprice'] += $v['quantity']*$data['prices'][$k]['sell']['median'];
            $data['sums']['buyprice'] += $v['quantity']*$data['prices'][$k]['buy']['median'];
        }
        $data['caption'] = 'Materials - Prices from the "'.regionIDToName($regionID).'" region';

        $template['content'] = $this->load->view('materials', $data, True);
        $this->load->view('maintemplate', $template);
    }

    private function _byCategory($charid, $categoryID = 9)
    {
        $assets = AssetList::getAssetsFromDB($charid, array('invGroups.categoryID'  => $categoryID));

        $data = array();
        $data = array();
        foreach ($assets as $loc)
        {  
            foreach ($loc as $asset)
            {
                if ($asset['categoryID'] == $categoryID)
                {
                    $data[] = array_merge($asset, Production::getBlueprintInfo($asset['typeID']));
                }
                if (isset($asset['contents']))
                {
                    foreach ($asset['contents'] as $content)
                    {
                        if ($content['categoryID'] == $categoryID)
                        {
                            $data[] = array_merge($content, Production::getBlueprintInfo($content['typeID']), array('locationID' => $asset['locationID']));
                        }
                    }
                }
            }
        }
        return ($data);
    }

    public function blueprints()
    {
        $character = $this->character;
        $data['character'] = $character;
        $data['assets'] = $this->_byCategory($this->chars[$character]['charid'], 9);
        $data['title']= 'Played Owned Blueprints';
        $template['content'] = $this->load->view('bycategory', $data, True);
        $this->load->view('maintemplate', $template);
    }

    public function ships()
    {
        $character = $this->character;
        $data['character'] = $character;
        $data['assets'] = $this->_byCategory($this->chars[$character]['charid'], 6);
        $data['title']= 'Played Owned Ships';
        $template['content'] = $this->load->view('bycategory', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
