<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class EveCentral
{

    var $mineralTypes = array(34,35,36,37,38,39,40,11399);
    var $cacheDir = '/var/tmp';

    /* region 10000002 == Jita */
    /* region 10000030 == Heimatar */
    private function retrieveXml($typeIdList, $region = 10000002, $timeout = 2)
    {
        $uri  = 'http://eve-central.com/api/marketstat?';
        $uri .= 'regionlimit='.$region;

        foreach ($typeIdList as $typeId)
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
            $fp = fopen($destFile, "w");

            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
        return(simplexml_load_file($destFile));
    }

    public function getPrices($typeIDList, $region = 10000002)
    {
        $xml = $this->retrieveXml($typeIDList, $region);

        $prices = array();
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
        return($prices);
    }

    public function getMineralPrices($region = 10000002)
    {
        return ($this->getPrices($this->mineralTypes, $region));
    }
}


?>
