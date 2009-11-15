<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Agent_Info
{
    
    public static function _get_agent($agent_id, $filter = array('1=1'), $on_what = 'eveNames.itemID')
    {
        $CI =& get_instance();
        
        $q = $CI->db->query("
            SELECT 
                eveNames.itemID,
                eveNames.itemName,
                agtAgents.level,
                agtAgents.quality,
                NPCDivision.divisionName AS division,
                station.stationName AS station,
                corpNames.itemName AS corpName,
                agtAgentTypes.agentType,
                region.itemName AS region,
                system.itemName AS systemName,
                ROUND(SolarSystems.security, 1) AS security,
                IF(ROUND(SolarSystems.security, 1)<0.5,'red','black') AS security_color,
                NPCCorp.factionID,
				station.stationID,
                (SELECT factionName FROM chrFactions WHERE factionID=NPCCorp.factionID) AS faction,
                ROUND( (agtAgents.level - 1) * 2 + agtAgents.quality / 20, 2) AS required_standing
            FROM
                eveNames,
                invTypes,
                agtAgentTypes,
                agtAgents
            INNER JOIN eveNames             AS corpNames        ON corpNames.itemID = agtAgents.corporationID
            INNER JOIN staStations          AS station          ON station.stationID = agtAgents.stationID
            INNER JOIN eveNames             AS region           ON region.itemID = station.regionID
            INNER JOIN eveNames             AS system           ON system.itemID = station.solarSystemID
            INNER JOIN mapSolarSystems      AS SolarSystems     ON SolarSystems.solarSystemID = station.solarSystemID
            INNER JOIN crpNPCCorporations   AS NPCCorp          ON NPCCorp.corporationID = agtAgents.corporationID
            INNER JOIN crpNPCDivisions      AS NPCDivision      ON NPCDivision.divisionID = agtAgents.divisionID
            WHERE
                agtAgents.agentID = eveNames.itemID AND
                invTypes.typeID = eveNames.typeID AND
                invTypes.groupID = 1 AND
                agtAgentTypes.agentTypeID = agtAgents.agentTypeID AND
                {$on_what} = ? AND
                ".implode(' AND ', $filter)."
            ORDER BY required_standing,{$on_what};
                ", $agent_id);
        
        if ( $q->num_rows() > 0 )
        {
            return ($q->result());
        }
        return False;
    }

    public function is_agent($agent_id)
    {
        return (Agent_Info::_get_agent($agent_id));
    }
    
    public function is_npccorp($corp_id, $filter = array('1=1'))
    {
        return (Agent_Info::_get_agent($corp_id, $filter, 'agtAgents.corporationID'));
    }
    
    public function is_faction($faction_id)
    {
        $CI =& get_instance();
        
        $q = $CI->db->query('SELECT * FROM chrFactions WHERE factionID = ?', $faction_id);
        
        if ($q->num_rows() > 0)
        {
            return ($q->row());
        }
        return False;
    }
    
    
    public function agent_snippet($agent_id, $chars_standing = False)
    {
        $CI =& get_instance();
        $data = array();
        $data['agent'] = array_shift(Agent_Info::_get_agent($agent_id));
        $data['chars_standing'] = $chars_standing;
        
        return ($CI->load->view('snippets/agent.php', $data, True));
    }
}

?>
