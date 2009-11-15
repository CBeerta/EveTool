<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Cache Functions
 *
 * This is basically just memcache wrapped so i can use it in CI without worrying if there is actually a memcache
 * 
 *
 * @todo Maybe implement some file or database cache?
 * @author Claus Beerta <claus@beerta.de>
 */

/**
 * Empty Memcache class so we can just go ahead and use memcache functions even if it is not available
 *
 **/
class NoMemcache {
    function __call($name, $arguments)
    {
        return False;
    }
}


class Cache {

    public $memcache;

    public function __construct()
    {
        $CI =& get_instance();
		$CI->config->load('evetool');
        
		if (function_exists("memcache_connect"))
		{
			$this->memcache = new Memcache;
			list($_host, $_port) = explode(':', $CI->config->item('memcache_host'));
			$mc = @$this->memcache->pconnect($_host, $_port);
			if (!$mc)
			{
			    $this->memcache = new NoMemcache;
			}
		}
		else
		{
			$this->memcache = new NoMemcache;
		}
    }
    
    public function get($item)
    {
        return ($this->memcache->get($item));
    }
    
    public function set($item, $value, $flag = MEMCACHE_COMPRESSED, $expire = 86400)
    {
        return ($this->memcache->set($item, $value, $flag, $expire));
    }
    
}


?>
