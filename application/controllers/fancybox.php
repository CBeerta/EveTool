<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Corporation Info Pages
 *
 *
 * @author Claus Beerta <claus@beerta.de>
 */

class Fancybox extends Controller
{
    public function location($id)
    {
		$playerstation = array();
		$query = "
			SELECT
				*
			FROM
			    mapConstellations AS con,
				mapSolarSystems AS sol,
				mapRegions AS reg";

		$stationlist = $this->eveapi->stationlist();
		
		if (!empty($stationlist[$id]))
		{
			$playerstation = $stationlist[$id];
			$query .= "
			WHERE
				sol.solarSystemID={$stationlist[$id]['solarSystemID']} AND
				reg.regionID=sol.regionID";
		}
		else
		{
			$query .= ",
				staStations AS sta
			WHERE
				sta.stationID={$id} AND
				sta.solarSystemID=sol.solarSystemID AND
				con.constellationID=sol.constellationID AND
				reg.regionID=sol.regionID";
		}
		
		$res = $this->db->query ($query);
		
		$loc = array_merge((array) $res->row(), $playerstation);

		if (empty($loc))
		{
		    print '<h1>Unable to find that Station</h1>';
	    }
	    else
	    {
            $this->load->view('snippets/location', array('loc' => $loc));
        }
    }
    
    public function fitting_from_db($typeName, $assetItemID)
    {
        echo Ship_Fitting::get($typeName, Ship_Fitting::items_from_db($assetItemID));
    }
    
    public function character($id)
    {
        $this->load->library("agents");
        if (Agents::is_agent($id))
        {
            die("is an agent");
        }
        else
        {
            $this->load->view('snippets/character', array('char' => get_character_info($id)));
        }
    }
    
    public function item($id)
    {
        $this->load->view('snippets/item', array('item' => (array) get_inv_type($id)));
    }

}

?>
