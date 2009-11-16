<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ingamee Browser Functions
 *
 */
 
 
 /**
 * Returns True if user trusts us
 **/
function igb_trusted()
{
    if (isset($_SERVER['HTTP_EVE_TRUSTED']) && strcasecmp($_SERVER['HTTP_EVE_TRUSTED'], 'yes') == 0)
    {   
        return (True);
    }
    return (False);
}

function igb_show_info($typeID, $itemID = False)
{
    /**
     * typeIDs:
     * 3 = Region
     * 5 = SolarSystem
     **/
    $itemID = !$itemID ? '' : ", {$itemID}";
    return ("<a href=\"#\" id=\"igb_link\" onclick=\"CCPEVE.showInfo({$typeID}{$itemID})\"><img src=\"".site_url("/files/itemdb/icons/icons_items_png/16_16/icon09_10.png")."\"></a>");
}

function igb_set_destination($solarSystemID)
{
    return ("<a href=\"#\" id=\"igb_link\" onclick=\"CCPEVE.setDestination({$solarSystemID})\">Set Destination</a>");
}


?>
