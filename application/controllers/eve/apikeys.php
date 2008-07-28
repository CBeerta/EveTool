<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Apikeys extends MY_Controller
{
    function add()
    {
        $rules['apiuser'] = "required|trim";
        $rules['apikey'] = "required|trim";

        $this->validation->set_rules($rules);

        $data['menu'] = array();

        if ($this->validation->run() === False)
        {
            $data['content'] = $this->load->view('eve/add_apikey', null, True);
            $this->load->view('eve/maintemplate', $data);
        }
        else
        {
            $this->eveapi->setCredentials($this->input->post('apiuser'), $this->input->post('apikey'));
            $chars = Characters::getCharacters($this->eveapi->getCharacters());
            if (count($chars) > 0)
            {
                $this->db->query('
                    INSERT INTO `asc_apikeys` (`acctID`,`apiUser`,`apiFullKey`)
                    VALUES (?,?,?)
                    ON DUPLICATE KEY UPDATE `apiFullKey`=?;', array($this->Auth['user_id'], $this->input->post('apiuser'), $this->input->post('apikey'), $this->input->post('apikey')));

                redirect('eve');
            }
            else
            {
                show_error('Unable to find any Characters on this account.');
            }
        }
    }
}

?>
