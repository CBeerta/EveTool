<?php
class User extends MY_Controller {

    function __construct()
    {
        parent::Controller();

        $this->load->library('users');

        $data['chars'] = array();
        $data['skillinfo'] = '<p>Please Login!</p>';
        $this->load->view('header', $data);
    }

    function login()
    {
        $rules['username'] = "required|trim";
        $rules['password'] = "required";

        $this->validation->set_rules($rules);

        if ($this->validation->run() === False)
        {
            $data['content'] = $this->load->view('auth/login', null, True);
            $this->load->view('maintemplate', $data);
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
                redirect();
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
            $this->load->view('maintemplate', $data);
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
                redirect();
            }
        }
    }

    function recover()
    {
        $this->load->library('email');
        $this->load->helper('message_helper');

        $rules['email'] = "required|valid_email";
        $this->validation->set_rules($rules);
        $data['menu'] = array();
        if ($this->validation->run() === False)
        {
            $data['content'] = $this->load->view('auth/recover', null, True);
            $this->load->view('maintemplate', $data);
        }
        else
        {
            $email = $this->input->post('email');

            $pw = $this->users->recoverPassword($email);
            if ($pw)
            {
                $this->email->from('admin@beerta.net', 'Password Recovery Agent');
                $this->email->to($email);
                $this->email->subject('Password Recovery request');
                $this->email->message("'You have requested a Password Recovery.\nYour password is: ".$this->users->recoverPassword($email));
                $this->email->send();
                msg_forward('Password Recovery', 'Your password has been sent to your mail Account.', site_url('user/login'));
                exit;
            }
            else
            {
                msg_forward('Password Recovery', 'Unable to find your Password in the Database.', site_url('user/login'));
            }
        }
    }

    function logout()
    {
        $this->users->logout();
        redirect();
    }
}
?>
