<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Pulls a Ship out of the 'contents' Table, and displays an ingame like Fitting window
 * 
 *
 * @author Claus Beerat <claus@beerta.de>
**/


class Ship_Fitting
{
    
    /**
     * Load a ship with its content
     *
     * @param int
     **/
    public function items_from_db($locationItemID)
    {
        $CI =& get_instance();
        $CI->load->database();
    
        $q = $CI->db->query('
            SELECT 
                invTypes.typeID,
                invTypes.typeName,
                contents.flag,
                invGroups.categoryID,
                contents.quantity,
                eveGraphics.icon
            FROM 
                contents,
                invTypes,
                eveGraphics,
                invGroups
            WHERE
                invTypes.graphicID=eveGraphics.graphicID AND
                contents.typeID = invTypes.typeID AND
                invTypes.groupID = invGroups.groupID AND
                locationItemID = ?;', $locationItemID);
        if ($q->num_rows() <= 0)
        {
            return (array());
        }
        return($q->result());
    }
    
    /**
     * Fill the fitting snippet with content
     *
     * @param int
     * @param array
     * @return string
     **/ 
    public function get($typeName, $fitting_data)
    {
        $CI =& get_instance();
        $CI->load->database();
    
        $data = array();
    
        /* Get Slot configuration for $typeID */
        $q = $CI->db->query("
                SELECT 
                        TRIM(attribtypes.attributename) as type,
                        attrib.valueint AS amount 
                FROM dgmTypeAttributes AS attrib
                        INNER JOIN invTypes AS type
                            ON attrib.typeID = type.typeID
                        INNER JOIN dgmAttributeTypes AS attribtypes
                            ON attrib.attributeID = attribtypes.attributeID
                WHERE attribtypes.attributename IN ('lowSlots', 'medSlots', 'hiSlots', 'rigSlots')
                        AND type.typeName = ?;", $typeName);
        $slots = array();
        foreach ( $q->result() as $slot )
        {
            $slots[$slot->type] = $slot->amount;
        }
        $data['slots'] = $slots;
        
        $fitting = $cargo = array();
        for ( $i = 0 ; $i < 8 ; $i++ )
        {
            $high = slot_icon($i + 27);
            $low = slot_icon($i + 11);
            $med = slot_icon($i + 19);
            
            $fitting["high.{$i}.Icon"] = site_url('/files/images/panel/blank.png');
            $fitting["high.{$i}.Alt"] = 'Empty';
            if ( $i < $slots['hiSlots'] )
            {
                $fitting["high.{$i}.Icon"] = $high[0];
                $fitting["high.{$i}.Alt"] = $high[1];
            }
            
            $fitting["med.{$i}.Icon"] = site_url('/files/images/panel/blank.png');
            $fitting["med.{$i}.Alt"] = 'Empty';
            if ( $i < $slots['medSlots'] )
            {
                $fitting["med.{$i}.Icon"] = $med[0];
                $fitting["med.{$i}.Alt"] = $med[1];
            }
            
            $fitting["low.{$i}.Icon"] = site_url('/files/images/panel/blank.png');
            $fitting["low.{$i}.Alt"] = 'Empty';
            if ( $i < $slots['lowSlots'] )
            {
                $fitting["low.{$i}.Icon"] = $low[0];
                $fitting["low.{$i}.Alt"] = $low[1];
            }
            
            $fitting["rig.{$i}.Icon"] = site_url('/files/images/panel/blank.png');
            $fitting["rig.{$i}.Alt"] = 'Empty';
                    
            $fitting["ammo_high.{$i}.show"] = '';
            $fitting["ammo_high.{$i}.type"] = '';
            
            $fitting["ammo_mid.{$i}.show"] = '';
            $fitting["ammo_mid.{$i}.type"] = '';
        }
        $eft = array('high' => array(), 'low' => array(), 'mid' => array(), 'rig' => array());
        
        foreach ($fitting_data as $row)
        {
            if ($row->flag >= 11 && $row->flag <= 18) //low
            {
                switch ($row->categoryID)
                {
                    case 7: // Module
                        $fitting['low.'.($row->flag - 11).'.Icon'] = get_icon_url($row, 64);
                        $fitting['low.'.($row->flag - 11).'.Alt'] = $row->typeName;
                        $eft['low'][] = $row->typeName;
                        break;
                    case 8: // Charge
                        break;
                }
            }
            else if ($row->flag >= 19 && $row->flag <= 26) //med
            {
                switch ($row->categoryID)
                {
                    case 7:
                        $fitting['med.'.($row->flag - 19).'.Icon'] = get_icon_url($row, 64);
                        $fitting['med.'.($row->flag - 19).'.Alt'] = $row->typeName;
                        $eft['mid'][] = $row->typeName;
                        break;
                    case 8:
                        $fitting['ammo_mid.'.($row->flag - 19).'.type'] = '<img src="'.get_icon_url($row, 32).'" width="24" height="24" title="'.$row->typeName.'">';
                        $fitting['ammo_mid.'.($row->flag - 19).'.show'] = '<img src="'.site_url('/files/images/panel/ammo_CoolGray.png').'" width="32" height="32">';
                        break;
                }
            }
            else if ($row->flag >= 27 && $row->flag <= 34) //high
            {
                switch ($row->categoryID)
                {
                    case 7:
                        $fitting['high.'.($row->flag - 27).'.Icon'] = get_icon_url($row, 64);
                        $fitting['high.'.($row->flag - 27).'.Alt'] = $row->typeName;
                        $eft['high'][] = $row->typeName;
                        break;   
                    case 8:
                        $fitting['ammo_high.'.($row->flag - 27).'.type'] = '<img src="'.get_icon_url($row, 32).'" width="24" height="24" title="'.$row->typeName.'">';
                        $fitting['ammo_high.'.($row->flag - 27).'.show'] = '<img src="'.site_url('/files/images/panel/ammo_CoolGray.png').'" width="32" height="32">';
                        break;
                }
            }
            else if ($row->flag >= 92 && $row->flag <= 99) //rig
            {
                $fitting['rig.'.($row->flag - 92).'.Icon'] = get_icon_url($row, 32);
                $fitting['rig.'.($row->flag - 92).'.Alt'] = $row->typeName;
                $eft['rig'][] = $row->typeName;
            }
            else // Everything else goes to Cargo
            {
                $cargo[] = $row;
            }
        }
        $data['fitting'] = $fitting;
        $data['cargo'] = '';
        
        $data['eft'] = "[{$typeName}, {$typeName} ".uniqid()."]\n";
        foreach (array('low', 'mid', 'high', 'rig') as $slot)
        {
            if (empty($eft[$slot]))
            {
                $data['eft'] .= "[empty {$slot} slot]\n\n";
                continue;
            }
            $data['eft'] .= implode("\n", $eft[$slot])."\n\n";
        }
        $data['eft'] = strtr($data['eft'], array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/')); // js'ify
        
        if (count($cargo) > 0)
        {
            $table = '<tr>';
            $x = 0;    
            foreach ($cargo as $row)    
            {
                $table .= '<td><img src="'.get_icon_url($row, 64).'" title="'.$row->quantity.' - '.$row->typeName.'" width="48" height="48"></td>';
                $x++;
                if ( $x > 7 )
                    {
                        $x = 0;
                        $table .= '</tr><tr>';
                    }
            }
            $table .= '</tr>';
            $data['cargo'] = $table;
        }            
        return ($CI->load->view('snippets/ship_fitting', $data, True));
    }
} //class

?>
