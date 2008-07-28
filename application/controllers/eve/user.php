<?php
class User extends MY_Controller {

    function __construct()
    {
        parent::Controller();

        $this->load->library('users');

        $data['chars'] = array();
        $data['skillinfo'] = '<p>Please Login!</p>';
        $this->load->view('eve/header', $data);
    }

    function login()
    {
        $rules['username'] = "required|trim";
        $rules['password'] = "required";

        $this->validation->set_rules($rules);

        if ($this->validation->run() === False)
        {
            $data['content'] = $this->load->view('auth/login', null, True);
            $this->load->view('eve/maintemplate', $data);
        }
        else
        {
            if ( !$this->users->login($this->input->post('username'),$this->input->post('password')) )
            {
                show_error($this->users->last_error);
                return False;
                redirect('user/login');
            }
            else
            {
                redirect('eve');
            }
        }
    }

    function register()
    {
        $rules['username'] = "required|trim";
        $rules['email'] = "required|valid_email";
        $rules['password'] = "required|min_length[5]";

        $this->validation->set_rules($rules);

        $data['menu'] = array();

        if ($this->validation->run() === False)
        {
            $data['content'] = $this->load->view('auth/register', null, True);
            $this->load->view('eve/maintemplate', $data);
        }
        else
        {
            if(!$this->users->register($this->input->post('username'),$this->input->post('password'),$this->input->post('email')))
            {
                show_error($this->users->last_error);
                return False;
            }
            else
            {
                redirect('eve');
            }
        }
    }


    function logout()
    {
        $this->users->logout();
        redirect('eve');
    }
}
?>
