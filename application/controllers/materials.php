<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Materials extends MY_Controller
{

    public $group_list = array(
           18 => 'Minerals', 
          334 => 'Construction Components', 
        /* 886,*/ 
          423 => 'Ice Product', 
          427 => 'Moon Materials', 
          428 => 'Intermediate Materials', 
        /* 429 => 'Composite',  */
          465 => 'Ice', 
          754 => 'Salvaged Materials', 
          873 => 'Capital Construction Components', 
          913 => 'Advanced Capital Construction Components',
           -1 => '',
        14107 => 'Low Slot Meta 4 Modules', /* encoded:  1 + meta + slot + 0 + category id */
        14307 => 'Med Slot Meta 4 Modules',
        14207 => 'High Slot Meta 4 Modules',
        );
    
    /**
     * Loads the invTypes for $id
     *
     * @param int
     * @returns array
     **/
    private function _get_invgroup($id)
    {
        if ($id < 10000)
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
                    t.published=1 AND
                    g.groupID = ?
                LIMIT 50", $id);
        }
        else if ($id < 20000)
        {
            $meta = substr($id, 1, 1);
            $slotid = 10 + substr($id, 2, 1);
            $id = substr($id, -1, 1);
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
                    c.categoryID=? AND
                    t.published=1 AND
                    metaLevel.valueInt >= ? AND
                    techLevel.valueInt = 1 AND
                    effect.effectID IN (?)
                ORDER BY
                    effect.effectName, g.categoryID, g.groupID, t.typeName
                /*LIMIT 50*/;", array($id, $meta, $slotid));
        }
        return ($q->result_array());
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
    public function load($id)
    {
        $character = $this->character;

        $region_id = !get_user_config($this->Auth['user_id'], 'market_region') ? 10000067 : get_user_config($this->Auth['user_id'], 'market_region');
        $custom_prices = $this->input->post('custom_prices') ? True : False;
        $data['custom_prices'] = $custom_prices;

        // Step 1: Pull all the player owned assets from the db for $id
        $assets = AssetList::getAssetsFromDB($this->chars[$character]['charid']);

        $materials = array();        
        foreach ($assets as $loc)
        {
            foreach ($loc as $asset)
            {
                if (!isset($data['data'][$asset['typeID']]))
                {
                    $materials[$asset['typeID']] = array_merge($asset ,array('quantity' => 0));
                }
                $materials[$asset['typeID']]['quantity'] += $asset['quantity'];
                if (isset($asset['contents']))
                {
                    foreach ($asset['contents'] as $content)
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

        // Step 2: Pull all invTypes from $id and merge with the quantities from Step 1
        $invtypes = $this->_get_invgroup($id);
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
        $data['prices'] = $this->evecentral->get_prices($typeids, $region_id, $custom_prices);

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
    public function index($id = 18)
    {
        $region_id = !get_user_config($this->Auth['user_id'], 'market_region') ? 10000067 : get_user_config($this->Auth['user_id'], 'market_region');
        
        if ($this->input->post('id'))
        {
            $id = $this->input->post('id');
            if ($id <= 0)
            {
                $id = 18; // Idiot klicked on the seperator
            }
            redirect(site_url("materials/index/{$id}"));
            exit;
        }

        $custom_prices = $this->input->post('custom_prices') ? True : False;
        $data['custom_prices'] = $custom_prices;
        $data['id'] = $id;
        
        $data['group_list'] = $this->group_list;
        
        $data['caption'] = $this->group_list[$id].' - Prices from the "'.regionid_to_name($region_id).'" region';
        $data['types'] = $this->_get_invgroup($id);
        
        foreach ($data['types'] as $r)
        {
            $typeid_list[$r['typeID']] = $r['typeName'];
        }
        $data['prices'] = $this->evecentral->get_prices(array_keys($typeid_list), $region_id, $custom_prices);

        $template['content'] = $this->load->view('materials', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
