<?php

/**
 *  classname:    Whitelist
 *  scope:        PUBLIC
 *  author:       Claus Beerta <claus@beerta.de>
**/

class Whitelist extends Controller
{
    function __construct ()
    {
        parent::Controller();
        $this->load->helper('form');
        $this->load->plugin('captcha');
    }
    
    public function index()
    {
        
    }
    
    public function request($msgid=NULL)
    {
        $this->db->simple_query('USE `claus`;');
        
        if ($msgid === NULL)
        {
            die('No MSGID given');
        }
        
        $query = $this->db->query("SELECT * FROM `whitelist` WHERE `msgid`=? AND `requested`=0", $msgid);
        if ($query->num_rows() > 0)
        {
            $captcha_vals = array(
                                        'img_path'       => '/home/www/public/files/tmp/',
                                        'img_url'        => site_url().'files/tmp/',
                                );

            $cap = create_captcha($captcha_vals);
            $data = array(
                                'captcha_id'    => '',
                                'captcha_time'  => $cap['time'],
                                'ip_address'    => $this->input->ip_address(),
                                'word'          => $cap['word']
                        );

            $cquery = $this->db->insert_string('captcha', $data);
            $this->db->query($cquery);
            
            $data = $query->row_array();
            $data['image'] = $cap['image'];
            $this->load->view('whitelist.php', $data);
        } 
        else 
        {
            die('Unable to find your MSGID in the Database, or you have already requested to be added.');
        }
    }
    
    public function add()
    {
        // First, delete old captchas
        $this->db->simple_query('USE `claus`;');
        $expiration = time()-7200; // Two hour limit
        $this->db->simple_query("DELETE FROM captcha WHERE captcha_time < ".$expiration);

        // Then see if a captcha exists:
        $sql = "SELECT COUNT(*) AS count FROM captcha WHERE LOWER(word) = ? AND ip_address = ? AND captcha_time > ?";
        $binds = array(strtolower($_POST['captcha']), $this->input->ip_address(), $expiration);
        $query = $this->db->query($sql, $binds);
        $row = $query->row();

        if ($row->count == 0)
        {
            echo "You must submit the word that appears in the image";
        } 
        else
        {
            $this->db->query("UPDATE `whitelist` SET `requested`=1 WHERE `id` = ?", $this->input->post('id'));
            echo "Your request was recorded.";
        }
    }   
}

?>
