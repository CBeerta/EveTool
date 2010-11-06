<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/** 
 * masort: http://de3.php.net/manual/de/function.uasort.php#25882 
 *  
 * Sorts array((array(),array()) by $sortby 
 **/

function masort(&$data, $sortby)
{	
    if(is_array($sortby))	
    {		
        $sortby = join(',',$sortby);	
    }	
    uasort($data,create_function('$a,$b','		
        $skeys = explode(\',\',\''.$sortby.'\');		
        foreach($skeys as $key)		
        {			
            if (is_numeric($a[$key]) && is_numeric($b[$key]))			
            {				
                if ($a[$key] == $b[$key]) 				
                {					
                    return 0;				
                }				
                return ($a[$key] < $b[$key]) ? 1 : -1;			
            }			
            else if( ($c = strcasecmp($a[$key],$b[$key])) != 0 )			
            {				
                return($c);
			}		
		}		
		return($c); '));
}

function dotlan_url($url, $module = 'map')
{	
    $search = array(' ');	
    $replace = array('_');
    return ("http://evemaps.dotlan.net/{$module}/".str_replace($search, $replace, $url));
}

// raped from http://www.go4expert.com/forums/showthread.php?t=4948
// A function to return the Roman Numeral, given an integer
function roman($num) 
{
    // Make sure that we only use the integer portion of the value
    $n = intval($num);
    $result = '';

    // Declare a lookup array that we will use to traverse the number:
    $lookup = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
                    'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
                    'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);

    foreach ($lookup as $roman => $value) 
    {
        // Determine the number of matches
        $matches = intval($n / $value);

        // Store that many characters
        $result .= str_repeat($roman, $matches);

        // Substract that from the number
        $n = $n % $value;
    }

    if (empty($result))
    {
        $result = '0';
    }
    return $result;
}


function profile($additional_info = '')
{
   static $start_time = NULL;
   static $start_code_line = 0;

   $call_info = array_shift( debug_backtrace() );
   $code_line = $call_info['line'];
   $file = array_pop( explode('/', $call_info['file']));

   if( $start_time === NULL )
   {
       error_log("debug ".$file."> initialize");
       $start_time = time() + microtime();
       $start_code_line = $code_line;
       return 0;
   }

   error_log(sprintf("debug %s> code-lines: %d-%d time: %.4f mem: %d KB - %s", $file, $start_code_line, $code_line, (time() + microtime() - $start_time), ceil( memory_get_usage()/1024), $additional_info));
   $start_time = time() + microtime();
   $start_code_line = $code_line;
}


?>
