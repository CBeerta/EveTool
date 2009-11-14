<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Corporation Info Pages
 *
 *
 * @author Claus Beerta <claus@beerta.de>
 */

class Fancybox extends Controller
{
    function location($id)
    {
        print "<h1>HOLY SHIT, AWESOMEBOX!</h1>";
        print $id;    
        
        /*
    	preg_match("|^([A-Z0-9\-]+)\s?|i", $loc['itemName'], $matches);
    	$loc['systemName'] = $matches[1];
        
        return ($CI->load->view('snippets/location', $loc, True));
        */
    }
}

