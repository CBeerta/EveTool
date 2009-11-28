<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Materials extends MY_Controller
{

    public $groupList = array(18, 754, /* 886,*/ 334, 873, 913, 427, 428, 429, 465, 423);
    
    /**
     * Loads the invTypes for $groupID
     *
     * @param int
     * @returns array
     **/
    private function _get_invgroup($searchtype = 'group', $id)
    {
        $q = $this->db->query("
            SELECT 
                t.*,
                g.*,
                c.*,
                gfx.*
            FROM 
            (
                invTypes AS t,
                invGroups AS g,
                invCategories AS c,
                eveGraphics AS gfx
            )
            WHERE 
                t.graphicID=gfx.graphicID AND
                t.typeName NOT LIKE 'Compressed %' AND
                t.marketGroupID IS NOT NULL AND
                t.groupID=g.groupID AND 
                g.categoryID=c.categoryID AND
                t.published=1 AND /*
                (SELECT IF(COUNT(valueInt)>0, valueInt, 99) FROM dgmTypeAttributes WHERE typeID=t.typeID AND attributeID=633) >= 5 AND
                (SELECT IF(COUNT(valueInt)>0, valueInt, -1) FROM dgmTypeAttributes WHERE typeID=t.typeID AND attributeID=422) <= 1 AND */
                g.{$searchtype}ID = ?", $id);
        return ($q->result_array());
    }
    
    /** 
     * Special case: Valueable loot
     * 
     * Selets Modules with minimum metalevel of 4 and only tech 1
     * 
     * @returns array
     **/
    private function _get_loot()
    {
        $q = $this->db->query("
            SELECT
                t.*,
                t.*,
                g.*,
                c.*,
                eveGraphics.icon,
                metaLevel.valueInt AS metalevel,
                techLevel.valueInt AS techlevel,
                TRIM(effect.effectName) AS slot
            FROM
            (
                invTypes AS t,
                invGroups AS g,
                invCategories AS c,
                eveGraphics
            )
            INNER JOIN dgmTypeAttributes AS metaLevel ON t.typeID = metaLevel.typeID AND metaLevel.attributeID = 633
            INNER JOIN dgmTypeAttributes AS techLevel ON t.typeID = techLevel.typeID AND techLevel.attributeID = 422
            INNER JOIN dgmTypeEffects AS typeEffect ON t.typeID = typeEffect.typeID
            INNER JOIN dgmEffects AS effect ON typeEffect.effectID = effect.effectID
            WHERE 
                t.graphicID=eveGraphics.graphicID AND
                t.marketGroupID IS NOT NULL AND
                t.groupID=g.groupID AND 
                g.categoryID=c.categoryID AND
                c.categoryID=7 AND
                t.published=1 AND
                metaLevel.valueInt >= ? AND
                techLevel.valueInt = ? AND
                effect.effectID IN (".implode(',', $slot).")
            ORDER BY
                effect.effectID, g.categoryID, g.groupID, t.typeName
                ", array($meta_level, $tech_level, ));

        $typeidlist = array();
        foreach ($data['items'] as $item)
        {
            $typeidlist[] = $item->typeID;
        }
        $regionID = !get_user_config($this->Auth['user_id'], 'market_region') ? 10000067 : get_user_config($this->Auth['user_id'], 'market_region');

        $data['prices'] = $this->evecentral->get_prices($typeidlist, $regionID);
    }
    
    /**
     * materials loader
     *
     * Loads the materials from db for our json request
     *
     * @param   int
     *
     * @todo this has gotten quite messy, and should be "reviewed"
     */
    public function load($searchtype = 'group', $id)
    {
        $character = $this->character;

        $regionID = !get_user_config($this->Auth['user_id'], 'market_region') ? 10000067 : get_user_config($this->Auth['user_id'], 'market_region');
        $custom_prices = $this->input->post('custom_prices') ? True : False;
        $data['custom_prices'] = $custom_prices;

        // Step 1: Pull all the player owned assets from the db for $id
        $assets = AssetList::getAssetsFromDB($this->chars[$character]['charid'], array("invGroups.groupID" => $id));

        $materials = array();        
        foreach ($assets as $loc)
        {
            foreach ($loc as $asset)
            {
                if ($asset[$searchtype.'ID'] == $id) 
                {
                    if (!isset($data['data'][$asset['typeID']]))
                    {
                        $materials[$asset['typeID']] = array_merge($asset ,array('quantity' => 0));
                    }
                    $materials[$asset['typeID']]['quantity'] += $asset['quantity'];
                }
                if (isset($asset['contents']))
                {
                    foreach ($asset['contents'] as $content)
                    {
                        if ($content[$searchtype.'ID'] == $id) 
                        {
                            if (!isset($data['data'][$content['typeID']]))
                            {
                                $materials[$content['typeID']] = array_merge($content ,array('quantity' => 0));
                            }
                            $materials[$content['typeID']]['quantity'] += $content['quantity'];
                        }
                    }
                }
            }
        }

        // Step 2: Pull all invTypes from $id and merge with the quantities from Step 1
        $invtypes = $this->_get_invgroup($searchtype, $id);
        $typeids = array();
        foreach ($invtypes as $v)
        {
            if (!empty($materials[$v['typeID']]))
            {
                $data['data'][] = array_merge($v, array('quantity' => $materials[$v['typeID']]['quantity']  )); 
            }
            else
            {
                $data['data'][] = array_merge($v, array('quantity' => 0));
            }
            $typeids[] = $v['typeID'];
        }
                        
        /**
         * Step 3: Did the user alter any quantity and posted? If so, overwrite quantity pulled from Step 2
         * @todo Do we want to save this into the database?
         **/
        if (is_numeric($this->input->post('content')))
        {
            $flashdata = $this->session->flashdata('materials_inplace');
            if ($flashdata)
            {
                foreach ($flashdata as $k => $v)
                {
                    $data['data'][$k]['quantity'] = $v;
                }
            }   
            $data['data'][$this->input->post('n')]['quantity'] = $this->input->post('content');
            $flashdata[$this->input->post('n')] = $this->input->post('content');
            $this->session->set_flashdata('materials_inplace', $flashdata);
        }

        // Step 4: Pull the prices for all of $groupID
        $data['sums']['volume'] = $data['sums']['sellprice'] = $data['sums']['buyprice'] = 0;
        $data['prices'] = $this->evecentral->get_prices($typeids, $regionID, $custom_prices);

        // Step 5: And finally add all the quantites up to totals
        foreach ($data['data'] as $v)
        {
            $data['sums']['volume'] += $v['volume']*$v['quantity'];
            $data['sums']['sellprice'] += $v['quantity']*$data['prices'][$v['typeID']]['sell']['median'];
            $data['sums']['buyprice'] += $v['quantity']*$data['prices'][$v['typeID']]['buy']['median'];
        }        

        // Step 6: Profit!
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
    public function index($searchtype = 'group', $id = 18)
    {
        $regionID = !get_user_config($this->Auth['user_id'], 'market_region') ? 10000067 : get_user_config($this->Auth['user_id'], 'market_region');
        
        if ($this->input->post('groupID'))
        {
            $searchtype = 'group';
            $id = $this->input->post('groupID');
            redirect(site_url("materials/index/{$searchtype}/{$id}"));
            exit;
        }
        $custom_prices = $this->input->post('custom_prices') ? True : False;
        $data['custom_prices'] = $custom_prices;
        
        $data[$searchtype.'ID'] = $id;

        $groupIDList = array();
        $q = $this->db->query('SELECT groupID,groupName FROM invGroups;');
        foreach ($q->result() as $row)
        {
            if ($row->groupID == $id)
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
        $data['searchtype'] = $searchtype;
        
        $data['types'] = $this->_get_invgroup($searchtype, $id);
        
        debug_popup($data);
        
        foreach ($data['types'] as $r)
        {
            $typeIDList[$r['typeID']] = $r['typeName'];
        }
        $data['prices'] = $this->evecentral->get_prices(array_keys($typeIDList), $regionID, $custom_prices);

        $template['content'] = $this->load->view('materials', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
