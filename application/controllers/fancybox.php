<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Corporation Info Pages
 *
 *
 * @author Claus Beerta <claus@beerta.de>
 */

class Fancybox extends Controller
{

    /**
     * Constructor to load the libraries we need 
     */
    public function __construct()
    {
        parent::__construct();

        $this->config->load('evetool');
		$this->load->library('eveapi', array('cachedir' => '/var/tmp'));

        $this->load->helper('eve');
        $this->load->helper('inventory');
    }


    public function location($id)
    {
        print "<h1>HOLY SHIT, LOCATION BOX!</h1>";
        print $id;    
        
        /*
    	preg_match("|^([A-Z0-9\-]+)\s?|i", $loc['itemName'], $matches);
    	$loc['systemName'] = $matches[1];
        
        return ($CI->load->view('snippets/location', $loc, True));
        */
    }
    
    public function character($id)
    {
        print "<h1>HOLY SHIT, CHARACTER BOX!</h1>";
        print $id;    
    }
    
    public function item($id)
    {
        print "<h1>HOLY SHIT, ITEM BOX!</h1>";
        print '<pre>';
        
        print_r (get_inv_type($id));
    }

}

