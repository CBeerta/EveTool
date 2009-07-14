<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
SELECT TRIM(effect.effectName) AS slot FROM invTypes AS type      INNER JOIN dgmTypeEffects AS typeEffect        ON type.typeID = typeEffect.typeID      INNER JOIN dgmEffects AS effect        ON typeEffect.effectID = effect.effectID WHERE effect.effectName IN ('loPower', 'medPower', 'hiPower', 'rigSlot')   AND type.typeName = 'Heat Dissipation Field II';

*/

class Killboard extends MY_Controller
{
    private function __get_totals($char)
    {
        $totals = (object) array(
                'kills' => 0,
                'losses' => 0,
            );

        $q = $this->db->query("
            SELECT 
                COUNT(filename) AS num
            FROM 
                kb_killmails,   
                kb_involved, 
                kb_characters 
            WHERE 
                kb_involved.kmID=kb_killmails.id AND 
                kb_involved.charID=kb_characters.id AND
                kb_characters.name = ?
            GROUP BY victim;", $char);

        if ($q->num_rows() == 2)
        {
            $totals->kills = $q->row(0)->num;
            $totals->losses = $q->row(1)->num;
        }
        return ($totals);
    }
    
    public function char($character, $offset = 0)
    {
        $character = urldecode($character);
        $data['type'] = "char";
        $data['character'] = $character;
        $data['corp'] = '';
        $data['totals'] = $this->__get_totals($character);

        $config['base_url'] = site_url("killboard/char/{$character}");
        $config['total_rows'] = $this->killmails->select_by_char($character, $offset );
        $config['per_page'] = 30;
        $config['uri_segment'] = 4;
        $config['num_links'] = 4;
        
        foreach ( $this->killmails as $k => $v )
        {
            $data['killmails'][date('Y-m-d', $v->when)][] = $v;
        }
        
        $this->pagination->initialize($config); 
        $data['pagination'] = $this->pagination->create_links();
        
        $template['content'] = $this->load->view('killboard/overview', $data, True);
        $this->load->view('maintemplate', $template);
    }

    public function corp($corp, $offset = 0)
    {
        $data['character'] = $this->character;
        $data['corp'] = urldecode($corp);
        $data['type'] = "corp/{$data['corp']}";
        /* $data['totals'] = $this->__get_totals($this->character); */

        $config['base_url'] = site_url("killboard/corp/{$data['corp']}");
        $config['total_rows'] = $this->killmails->select_by_corp(urldecode($corp), $offset );
        $config['per_page'] = 30;
        $config['uri_segment'] = 4;
        $config['num_links'] = 4;
        
        foreach ( $this->killmails as $k => $v )
        {
            $data['killmails'][date('Y-m-d', $v->when)][] = $v;
        }

        $this->pagination->initialize($config); 
        $data['pagination'] = $this->pagination->create_links();

        $template['content'] = $this->load->view('killboard/overview', $data, True);
        $this->load->view('maintemplate', $template);
    }

    
    public function detail($km_file)
    {
        $data['character'] = $this->character;
        $data['corp'] = '';
        
    
        $km = new Killmail_Parser(basename($km_file));
        $data['k'] = $km->get_parsed();
        $data['k']->add_sql_data();
        /* $data['i'] = getInvType($data['k']->destroyed); */
        
        $template['content'] = $this->load->view('killboard/detail', $data, True);
        $this->load->view('maintemplate', $template);
    }
    
    public function post()
    {
        $data['character'] = $this->character;
        $data = array();
        
        $km_text = $this->input->post('killmail');        
        if ($km_text)
        {
            try
            {            
                $parser = new Killmail_Parser($km_text);
            }
            catch (Exception $e)
            {
                msg_forward('Invalid Killmail', 'The Killmail you posted is invalid', site_url("killboard/post/{$this->character}"));
                exit;
            }
            $km = $parser->get_parsed();
            
            $destfile = $this->config->item('killmail_directory')."/".$km->filename;
            if (!file_exists($destfile))
            {
                if (@file_put_contents($destfile, $km_text) !== False)
                {
                    $km->import();
                    msg_forward('Killmail Imported', 'The Killmail you posted was imported.', site_url("killboard/detail/{$km->filename}/{$this->character}"));
                    exit;
                }
                else
                {
                    show_error('Unable to save Killmail to the "killmail_directory", check your directory permissions!');
                    exit;
                }
            }
            else
            {
                msg_forward('Killmail already imported', 'The Killmail you posted has already been posted.', site_url("killboard/detail/{$km->filename}/{$this->character}"));
                exit;
            }
        }
        $template['content'] = $this->load->view('killboard/post', $data, True);
        $this->load->view('maintemplate', $template);
    }
}

?>
