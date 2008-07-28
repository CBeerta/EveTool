<?php

class Imgcache Extends Controller
{
    function __construct()
    {
        parent::Controller();
        $this->config->load('evetool');
    }

    public function _mkdirs($dir, $recursive = true) 
    {
        /* Function to create directories recursively, ripped from php.net/mkdir */
        if( is_null($dir) || $dir === "" )
        {
            return FALSE;
        }
        if( is_dir($dir) || $dir === "/" )
        {
            return TRUE;
        }
        if( $this->_mkdirs(dirname($dir), $recursive) )
        {
            return mkdir($dir);
        }
        return FALSE;
    }
    private function _download($uri, $destfile)
    {
        $ch = curl_init($uri);
        $fp = @fopen($destfile, "w");

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_exec($ch);
        curl_close($ch);
        @fclose($fp);
    }

    public function get()
    {
        preg_match('|^/imgcache/get/(.*)|', $this->uri->uri_string(), $matches);

        switch ($this->uri->slash_segment(3)) 
        {
            case 'char/':
                list($tmp,$charid,$size) = explode('/', $matches[1]);
                $destfile = $matches[1];
                $cachefile = $this->config->item('image_cache_path').$destfile;
                $uri = "http://img.eve.is/serv.asp?s={$size}&c={$charid}";
                break;

            case 'itemdb/';
                $destfile = $matches[1];
                $cachefile = $this->config->item('image_cache_path').$destfile;
                $uri = 'http://www.eve-online.com/bitmaps/icons/'.$destfile;
                break;

            default:
                return False;
        }

        
        $this->_mkdirs(dirname($cachefile));
        $this->_download($uri, $cachefile);

        redirect($_SERVER['REQUEST_URI']);
    }
}
?>
