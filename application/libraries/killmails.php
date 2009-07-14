<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Language independent base class to the Killmail that holds the Functions
**/
class Killmail
{
    public $final_blow = 0;
    public $involved_parties = array();
    public $items = array();
    
    public $hash = False;
    public $filename = False;

    /**
    * Convert Parsed mail to a string
    *
    * BEWARE: the killmail hash is based on this, if you change the output, 
    * the hashes in the database will be all wrong!
    **/
    public function __toString()
    {
        return (print_r($this, True));
    }

    /**
    * Add a character from killmail to db
    *
    * @access private
    * @param string
    * @param string
    * @param string
    **/
    private function __add_character($name, $corp, $alliance)
    {
        $CI =& get_instance();
        $char_id = get_character_id($name);
        $char_id = is_null($char_id) ? -1 : $char_id;
        $insert = array($name, $corp, $alliance, $char_id, $corp, $alliance);
        $q = $CI->db->query('INSERT INTO kb_characters (name,corp,alliance,characterID) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE corp=?,alliance=?', $insert);
        return ($CI->db->insert_id());
    }

    /**
    * Add involvement relation to db
    *
    * @access private
    * @param string
    * @param string
    * @param boolean
    **/
    private function __add_involved($charid, $kmid, $victim = False)
    {
        $CI =& get_instance();
        $insert = array($charid, $kmid, $victim);
        $q = $CI->db->query('INSERT IGNORE INTO kb_involved (charID,kmID,victim) VALUES (?, ?, ?);', $insert);
        return ($CI->db->insert_id());
    }
    
    /**
    * Add a parsed killmail to the Database
    *
    * @access public
    **/
    public function import()
    {
        $CI =& get_instance();
        $char_id = $this->__add_character($this->victim, $this->corp, $this->alliance);
       
        $insert = array($this->hash, $this->filename, gmdate('c', $this->when), $this->filename, gmdate('c', $this->when));
        $q = $CI->db->query("INSERT INTO kb_killmails (`hash`,`filename`,`when`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE filename=?,`when`=?;", $insert);
        $km_id = $CI->db->insert_id()."\n";
        $this->__add_involved($char_id, $km_id, True);

        foreach ($this->involved_parties as $v)
        {
            $party_id = $this->__add_character($v->name, $v->corp, $v->alliance);
            $this->__add_involved($party_id, $km_id);
        }
    }
    
    public function add_sql_data()
    {
        $CI =& get_instance();
        $items = array(mysql_escape_string($this->destroyed));
        $names = array(mysql_escape_string($this->victim));
        
        // First insert the character ID's
        foreach ($this->involved_parties as $char)
        {
            $items[] = mysql_escape_string($char->ship);
            $items[] = mysql_escape_string($char->weapon);
            $names[] = mysql_escape_string($char->name);
        }
        $q = $CI->db->query ("SELECT * from kb_characters WHERE name IN ('".implode("','", $names)."');");
        foreach ($q->result() as $row)
        {
            $names_to_id[$row->name] = $row->characterID;
        }
        foreach ($this->involved_parties as $k => $v)
        {
            if ($names_to_id[$v->name] < 0)
            {
                $names_to_id[$v->name] = get_character_id($v->name);
            }
            $this->involved_parties[$k]->characterID = $names_to_id[$v->name];
        }
        $this->characterID = $names_to_id[$this->victim];
        
        //now add all the item ID's
        foreach (array_merge($this->items['destroyed_items'], $this->items['dropped_items']) as $row)
        {
            $items[] = mysql_escape_string($row->name);
        }
        $items = array_unique($items);
        $q = $CI->db->query("
            SELECT 
                invTypes.typeName,
                invTypes.typeID,
                invGroups.groupName,
                invTypes.groupID,
                invTypes.description,
                invTypes.volume,
                invTypes.mass,
                invCategories.categoryName,
                invGroups.categoryID,
                eveGraphics.icon
            FROM 
                invTypes,
                invGroups,
                eveGraphics,
                invCategories 
            WHERE 
                invTypes.typeName IN ('".implode("','", $items)."') AND 
                invTypes.groupID=invGroups.groupID AND 
                invTypes.graphicID=eveGraphics.graphicID AND
                invCategories.categoryID=invGroups.categoryID;");
        foreach ($q->result() as $v)
        {
            $this->items_to_id[$v->typeName] = $v;
        }
        $this->items_to_id['Unknown'] = new emptyInvType;
    }
}


/**
* English Version of a Killmail
**/
class Killmail_EN extends Killmail
{
    public $when = False;
    public $victim = False;
    public $corp = 'Victim has no Corp';
    public $alliance = 'Victom has no Alliance';
    public $faction = 'NONE';
    public $destroyed = False;
    public $system = False;
    public $security = False;
    public $damage_taken = 0;

    /**
    * Return translation for how the strings in the Killmail show up
    *
    * @access public
    * @param string
    **/    
    public function _text($k)
    {
        $texts = array(
            'involved_parties' => 'Involved parties:',
            'dropped_items' => 'Dropped items:',
            'destroyed_items' => 'Destroyed items:',
            );
        return ($texts[$k]);
    }
}

/**
* This Parses the actual Killmail Text, either from a file, or from a string
*
**/
class Killmail_Parser
{
    private $raw_mail = Null;
    private $km = Null;
   
    /**
    * Determine if input is an existing file, or a string, and load accordingly
    *
    * @access public
    * @param string
    **/
    public function __construct($km)
    {
        $CI =& get_instance();
        $CI->config->load('evetool');

        $this->km = new Killmail_EN;

        $km_file = $CI->config->item('killmail_directory')."/{$km}";
        if (is_readable($km_file))
        {
            $this->__load_from_file($km_file);
        }      
        else if (is_string($km))
        {
            $this->__load_from_string($km);
        }
        else
        {
            throw new Exception ("Neither File nor String with Killmail was readable");
        }
        $this->parse_involved();
        $this->parse_items('destroyed_items');
        $this->parse_items('dropped_items');
    }
    
    /**
    * Load killmail from the String, and convert it for the parsers
    *
    * @access private
    * @param string
    **/
    private function __load_from_string($text)
    {
            $mail = split("[\n]", $text);
            foreach ($mail as $line)
            {
                $this->raw_mail[] = trim($line);
            }
            $this->parse_header();
            $this->km->hash = md5($this->km);
            $this->km->filename = "{$this->km->hash}.txt";            
    }

    /**
    * Load killmail from the file given, and convert it for the parsers
    *
    * @access private
    * @param string
    **/
    private function __load_from_file($km_file)
    {
        $raw_mail = file($km_file);
        if (!$raw_mail)
        {
            throw new Exception ("Unable to open km file {$km_file}");
        }
        else
        {
            foreach ($raw_mail as $line)
            {
                // Yes, i could use FILE_IGNORE_NEW_LINES in file() but then 
                // 'auto_detect_line_endings' would need to be enabled to work with windoze files
                // so lets do it the "safe" way
                $this->raw_mail[] = trim($line);
            }
            $this->parse_header();
            $this->km->hash = md5($this->km);
            $this->km->filename = basename($km_file);
        }
    }

    /**
    * Return a completele parsed Killmail Object
    *
    * @access public
    **/
    public function get_parsed()
    {
        return ($this->km);
    }


    /**
    * Parse the Dropped and Destroyed Items part of a killmail
    *
    * @access public
    * @param string
    **/
    private function parse_items($type)
    {
        $this->km->items[$type] = array();
        if (($num = array_search($this->km->_text($type), $this->raw_mail)) === False)
        {
            return False;
        }
        $num++;
        while ( $num < count($this->raw_mail) && 
                    $this->raw_mail[$num] != $this->km->_text('dropped_items') && 
                    $this->raw_mail[$num] != $this->km->_text('destroyed_items') && 
                    $this->raw_mail[$num] != $this->km->_text('involved_parties') 
        )
        {
            $line =  $this->raw_mail[$num];
            if (empty($line))
            {
                $num++;
                continue;
            }
            
            if (preg_match("|(.*), Qty: (\d+) \((.*)\)|", $line, $matches))
            {
                $this->km->items[$type][] = (object) array('name' => $matches[1], 'qty' => $matches[2], 'loc' => strtolower(str_replace(' ', '_', $matches[3])));
            }
            else if (preg_match("|(.*), Qty: (\d+)|", $line, $matches))
            {
                $this->km->items[$type][] = (object) array('name' => $matches[1], 'qty' => $matches[2], 'loc' => 'fitted');
            }
            else if (preg_match("|(.*) \((.*)\)|", $line, $matches))
            {
                $this->km->items[$type][] = (object) array('name' => $matches[1], 'qty' => 1, 'loc' => strtolower(str_replace(' ', '_', $matches[2])));
            }
            else
            {
                $this->km->items[$type][] = (object) array('name' => $line, 'qty' => 1, 'loc' => 'fitted');
            }            
            $num++;
        }
    }
    
    /**
    * Parse the Involved Parties section of the Killmail
    *
    * @access public
    **/
    private function parse_involved()
    {
        if (($num = array_search($this->km->_text('involved_parties'), $this->raw_mail)) === False)
        {
            return False;
        }
        $num++;
        $party = False;

        while ( $num < count($this->raw_mail) && $this->raw_mail[$num] != $this->km->_text('destroyed_items') && $this->raw_mail[$num] != $this->km->_text('dropped_items') )
        {
            $line =  $this->raw_mail[$num];
            if (!empty($line))
            {
                list($option, $text) = explode(':', $line);
                $option = strtolower(str_replace(' ', '_', $option));
                $text = trim($text);
            }
            else
            {
                $option = 'NEWLINE';
            }
            
            switch ($option)
            {
                case 'name':
                    $name = empty($text) ? 'A Ghost!' : $text;
                    $party = (object) array (
                        'name' => $name,
                        'security' => '',
                        'corp' => 'has no Corp',
                        'alliance' => 'has no Alliance',
                        'faction' => 'NONE',
                        'ship' => 'NONE',
                        'weapon' => 'NONE',
                        'damage_done' => 0,
                        );
                    if ( preg_match('|(.*) \\(laid the final blow\\)|', $text, $matches) )
                    {
                        $party->name = $matches[1];
                        $this->km->final_blow = count($this->km->involved_parties);
                    }
                    break;
                case 'NEWLINE':
                    if (isset($party) && $party)
                    {
                        $this->km->involved_parties[] = $party;
                        unset($party);
                    }
                    break;
                default:
                    if (isset($party->$option))
                    {
                        $party->$option = trim($text);
                    }
                    break;
            }
            $num++;
        }
        if (isset($party))
        {
            $this->km->involved_parties[] = $party;
        }
    }
    
    /**
    * Parse the Header of a killmail
    *
    * @access public
    **/
    private function parse_header()
    {
        foreach ($this->raw_mail as $line)
        {
            if (preg_match("|(\d+).(\d+).(\d+) (\d+):(\d+):?(\d+)?|", $line, $matches))
            {
                if (!isset($matches[6]))
                {
                    $matches[6] = 0;
                }
                $this->km->when = gmmktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
                continue;
            }
            else if (preg_match("|^".$this->km->_text('involved_parties')."|i", $line))
            {
                return;
            }
            else if (preg_match("|^(.*):|", $line))
            {
                list($option, $text) = explode(':', $line);
                $option = strtolower(str_replace(' ', '_', $option));
                $text = trim($text);
                if (isset($this->km->$option))
                {
                    $this->km->$option = $text;
                }
            }
            else if (empty($line))
            {
                continue;
            }
            else
            {
                throw new Exception("Unable to parse Killmail");
            }
        }
        // if we reached this, it's a malformed killmail
        throw new Exception ("Malformed Killmail!");
    }
}


/**
* Iteration Class to go over selected killmails with foreach()
*
**/
class Killmails implements Iterator
{
    private $key = 0;
    private $available_files;
    
    private $km = Null;
    
    public function current()
    {
        $this->km = new Killmail_Parser($this->available_files[$this->key]);
        return ($this->km->get_parsed());
    }
    
    public function valid()
    {
        return (isset($this->available_files[$this->key]));
    }
    
    public function next()
    {
        unset($this->km);
        ++$this->key;
    }
    
    public function key()
    {
        return ($this->key);
    }
    
    /**
    * Load all available killmails in our killmail_directory
    *
    * @access public
    **/
    public function load_all()
    {
        $CI =& get_instance();
        $CI->config->load('evetool');
        
        $files = array();
        
        if ($dh = opendir($CI->config->item('killmail_directory')))
        {
            while ($file = readdir($dh))
            {
                $fname = $CI->config->item('killmail_directory')."/{$file}";
                if (!preg_match("|^.*\.txt$|", $file) && !is_file($fname))
                {
                    continue;
                }
                $stat = stat($fname);
                $files[$file] = $stat['mtime'];
            }
        }
        asort($files);
        $this->available_files = array_keys($files);
        $this->key = 0;
        closedir($dh);        
    }
    
    /**
    * Select Corporation Killmails from a database query
    *
    * @access public
    * @param string
    **/
    public function select_by_corp($name, $offset = 0)
    {
        $CI =& get_instance();
        
        $q = $CI->db->query("
            SELECT
            SQL_CALC_FOUND_ROWS
                *
            FROM
                kb_killmails,
                kb_involved
            INNER JOIN kb_characters AS charNames ON kb_involved.charID = charNames.id
            WHERE
                kb_involved.kmID=kb_killmails.id AND
                kb_involved.charID IN(SELECT id FROM kb_characters WHERE corp = ?)
            GROUP BY kb_killmails.id
            ORDER BY kb_killmails.when DESC
            LIMIT ?,30;
        ", array($name, (int) $offset));
        foreach ($q->result() as $row)
        {
            $this->available_files[] = $row->filename;
        }
        $q = $CI->db->query("SELECT FOUND_ROWS() AS rows;");
        return ($q->row()->rows);
    }
    
    /**
    * Select Character Killmails from a database query
    *
    * @access public
    * @param string
    **/
    public function select_by_char($name, $offset = 0)
    {
        $CI =& get_instance();
        
        $q = $CI->db->query("
            SELECT
            SQL_CALC_FOUND_ROWS
                kb_killmails.filename,
                kb_characters.characterID
            FROM
                kb_killmails,
                kb_involved,
                kb_characters
            WHERE
                kb_involved.kmID=kb_killmails.id AND
                kb_involved.charID=kb_characters.id AND
                kb_characters.name = ?
            ORDER BY kb_killmails.when DESC
            LIMIT ?,30;", array($name, (int) $offset));

        foreach ($q->result() as $row)
        {
            $this->available_files[] = $row->filename;
        }
        $q = $CI->db->query("SELECT FOUND_ROWS() AS rows;");
        return ($q->row()->rows);
    }

    public function rewind()
    {
        $this->key = 0;
    }
}

?>