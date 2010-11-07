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
		$playerStation = array();
		$query = "
			SELECT
				*
			FROM
			    mapConstellations AS con,
				mapSolarSystems AS sol,
				mapRegions AS reg";
		
		if (!empty($this->eveapi->stationlist[$id]))
		{
			$playerStation = $this->eveapi->stationlist[$id];
			$query .= "
			WHERE
				sol.solarSystemID={$this->eveapi->stationlist[$id]['solarSystemID']} AND
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
		
		$loc = array_merge((array) $res->row(), $playerStation);
        $this->load->view('snippets/location', array('loc' => $loc));
    }
    
    public function fitting_from_db($typeName, $assetItemID)
    {
        echo Ship_Fitting::get($typeName, Ship_Fitting::items_from_db($assetItemID));
    }
    
    public function character($id)
    {
        $this->load->view('snippets/character', array());
    }
    
    public function item($id)
    {
        $this->load->view('snippets/item', array('item' => (array) get_inv_type($id)));
    }

}

?>