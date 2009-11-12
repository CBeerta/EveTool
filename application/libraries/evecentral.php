<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class EveCentral
{
    public $mineralTypes = array(34,35,36,37,38,39,40,11399);
    public $cacheDir = '/var/tmp';

    /* region 10000002 == Jita */
    /* region 10000030 == Heimatar */
    private function retrieveXml($typeIdList, $region = 10000002, $timeout = 1)
    {
        $uri  = 'http://eve-central.com/api/marketstat?';
        $uri .= 'regionlimit='.$region;
        foreach (array_unique($typeIdList) as $typeId)
        {
            $uri .= '&typeid='.$typeId;
        }
        $destFile = $this->cacheDir.'/evecentral_'.md5($uri).'.cache.xml';

        $update = False;
        if (file_exists($destFile))
        {
            $stat = stat($destFile);
            if (($stat['mtime'] + $timeout * 24 * 60 * 60) < time())
            {
                $update = True;
            }
        }
        else
        {
            $update = True;
        }
        if ($update == True)
        {
            $ch = curl_init($uri);
            $fp = fopen($destFile.'.tmp', "w");

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            $res = curl_getinfo($ch);
            curl_close($ch);
            fclose($fp);

            if ($res['http_code'] >= 400 || $res['http_code'] == 0 || $res === False)
            {
                /* Something went wrong */
                unlink($destFile.'.tmp');
            }
            else
            {
                rename($destFile.'.tmp', $destFile);
            }
        }
        return(simplexml_load_file($destFile));
    }

    public function getPrices($typeIDList, $region = 10000002, $user_prices = False )
    {
        $prices = array();

        $typeIdList = array_unique(array_merge($typeIDList, $this->mineralTypes)); // Always fetch mineral prices
		sort($typeIdList);
		
        foreach ($typeIDList as $typeID)
        {
            foreach (array('median','avg','max') as $kind)
            {
                /* 
                 * Empty everything, incase our pull goes wrong
                 * As we dont want our parent to die horribly
                 */
                $prices[$typeID]['buy'][$kind] = 0;
                $prices[$typeID]['sell'][$kind] = 0;
                $prices[$typeID]['all'][$kind] = 0;
            }
        }
        
        foreach (array_chunk($typeIdList, 20) as $splitted)
        {
            $xml = $this->retrieveXml($splitted, $region);
            if ($xml !== False)
            {
                foreach ($xml->marketstat[0]->type as $type)
                {
                    foreach (array('buy','sell','all') as $kind)
                    {
                        foreach ($type->$kind->children() as $key => $value)
                        {
                            $prices[(int) $type->attributes()->id][$kind][(string) $key] = (string) $value;
                        }
                    }
                }
            }
        }
	
        if ( $user_prices !== False )
        {
            // Apply user mineral Prices over the fetched ones
            $CI =& get_instance();
            $mineral_prices = getUserConfig($CI->Auth['user_id'], 'mineral_prices');
            if ( $mineral_prices !== False )
            {
                $mineral_prices = unserialize($mineral_prices);
                foreach ( $mineral_prices as $k => $v)
                {
                    foreach ( array('median', 'avg', 'max') as $kind )
                    {
                        $prices[$k]['buy'][$kind] = $v;
                        $prices[$k]['sell'][$kind] = $v;
                        $prices[$k]['all'][$kind] = $v;
                    }
                }
            }
        }
        return($prices);
    }

    public function getMineralPrices($region = 10000002)
    {
        return ($this->getPrices($this->mineralTypes, $region));
    }
}


?>
