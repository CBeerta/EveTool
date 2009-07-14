<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

set_time_limit(0);
ini_set('memory_limit', '512M');

class Cli extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('killmails');
        $this->config->load('evetool');
        $this->load->helper('eve');
    }
    
    public function add_character_ids()
    {
        $q = $this->db->query("SELECT * from kb_characters where characterID < 0;");
        foreach ($q->result() as $row)
        {
            echo "Loading {$row->name}\n";
            get_character_id($row->name);
            sleep(0.2);
        }
    }

    public function import_killmails()    
    {
        /**
         * FIXME:
         * This thing is a MASSIVE memory hog, if you import lots of mails,
         * and i can't quite figure out why it is.
         **/
        $this->db->query('TRUNCATE kb_characters;');
        $this->db->query('TRUNCATE kb_involved;');
        $this->db->query('TRUNCATE kb_killmails;');
        
        $this->killmails->load_all();
        $count = 0;
        foreach ($this->killmails as $k => $v)
        {
            print "Importing {$v->filename} Victim: '{$v->victim}' Corp: '{$v->corp}' Alliance: '{$v->alliance}'\n";
            $v->import();
            $count++;
            $v->__destruct();
        }
        print "Processed {$count} files.\n";
    }
    
    public function import_feed( $week = Null, $year = Null )
    {
        $this->load->library('simplepie');
        $this->simplepie->set_cache_location('/var/tmp');
        
        $myid = urlencode("Infinite Improbability Inc");
        $feeds = array(
            'http://kb.eve-42.com/?a=feed',
            /* 'http://killboard.kia-clan.info/?a=feed', 
            'http://www.eve-corps.net/kb/?a=feed',  */
            'http://killboard.tauceti-federation.com/?a=feed',
            /* 'http://aello.beerta.net/killboard/?a=feed', */
            );
            
        $lossfeeds = array();
        foreach ($feeds as $feed)
        {
            $lossfeeds[] = $feed."&losses=1";
        }
                
        $year = is_null($year) ? gmdate("Y") : $year;
        $week = is_null($week) ? gmdate("W") : $week;
        
        foreach (array_merge($feeds, $lossfeeds) as $feed)
        {
            $url = "{$feed}&year={$year}&week={$week}&corp={$myid}&friend=1";
            print "Fetching: {$url}\n";
            $this->simplepie->set_feed_url($url);
            $this->simplepie->init();
            foreach ($this->simplepie->get_items() as $item)
            {
                try
                {
                    $parser = new Killmail_Parser($item->get_description());
                    $km = $parser->get_parsed();
                    $destfile = $this->config->item('killmail_directory')."/".$km->filename;
                    if (!file_exists($destfile))
                    {
                        if (file_put_contents($destfile, $item->get_description()) !== False)
                        {
                            // It's written to disk, so we can now add it to the database!
                            print "Importing ".$item->get_link()." as {$destfile}\n";
                            $km->import();
                        }
                    }
                }
                catch (Exception $e)
                {
                    print "ERR: Unable to add ".$item->get_link()."\n";
                }
            }
        }        
    }
}

?>
