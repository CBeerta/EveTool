<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * User Library for Code Igniter
 * Written by Troy Whiteley
 * Copyright 2008
 * http://dawnerd.com
 *
 * Released as free code, however,
 * You must keep the copyright information.
 * 
 * Requires session library.
 * http://codeigniter.com/wiki/DB_Session/
 *
 * Version 1.0
 */

class Users {

	var $ci;
	var $last_error;
	var $user;

    function Users()
    {
		$this->ci =& get_instance();
		$this->ci->load->library('encrypt');
    }
	
	/**
	 * function _validatePass( $username, $password )
	 * Checks if a password matches the user.
	 */
	function _validatePass( $username, $password )
	{
		$query = $this->ci->db->getwhere('users', array('username' => $username));
		if( $query->num_rows() == 1 )
		{
			foreach ($query->result() as $row)
			{
				$decrypted_password = $this->ci->encrypt->decode($row->password);
			}
			
			if( $password == $decrypted_password )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
		
	}
	
	/**
	 * function isLoggedIn()
	 * Checks if the current user is
	 * logged in
	 */
	function isLoggedIn()
	{
		if( $this->ci->session->userdata('user') != NULL )
		{
			$userdata = unserialize($this->ci->encrypt->decode($this->ci->session->userdata('user')));
			
			if( $this->_validatePass($userdata['username'],$userdata['password']) )
			{
				$this->user = $userdata['username'];
				return true;
			}
			else
			{
				//Cear the session for good measure
				$this->ci->session->sess_destroy();
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * function login( $username, $password )
	 * Logs a user in if their information
	 * is correct.
	 */
	function login( $username, $password )
	{
		if( $this->_validatePass($username,$password) )
		{
			$userdata = $this->ci->encrypt->encode(serialize(array('username'=>$username,'password'=>$password)));
			//Set the session data
			$this->ci->session->set_userdata('user',$userdata);
			return true;
		}
		else
		{
			$this->last_error = "Invalid username or password.";
			return false;
		}
	}
	
	/**
	 * function register( $username, $password, $email, $emailcheck = true )
	 * Registers a user if the given username is not in use.
	 * Optionally, you may chose to allow more than one account
	 * per email address by setting the fourth value to false.
	 * If there is more than one account per email, the recover
	 * password function will not work.
	 */
	function register( $username, $password, $email, $emailcheck = TRUE )
	{
		$query = $this->ci->db->getwhere('users', array('username' => $username));
		if( $query->num_rows() == 1 )
		{
			$this->last_error = "Username already in use.";
			return false;
		}
		else
		{	
			if( $emailcheck )
			{
				$query = $this->ci->db->getwhere('users', array('email' => $email));
				if( $query->num_rows() == 1 )
				{
					$this->last_error = "Email already in use.";
					return false;
				}
			}
			
			$encoded_password = $this->ci->encrypt->encode($password);
			
			$data = array(
					'username'  => $username,
					'password'  => $encoded_password,
					'email'		=> $email
					);
					
			$this->ci->db->insert('users',$data);
			
			return true;
		}
	}
	
	/**
	 * function recoverPassword( $email )
	 * Decrypts and returns the password in the database
	 * for a given user. This function should only be used
	 * if you want to send the user their password
	 * rather than generating a new one.
	 * Idealy you would create your own function
	 * to generate a random password. This only works if
	 * there is one account per email. Otherwise errors
	 * WILL occur.
	 */
	function recoverPassword( $email )
	{
		$query = $this->ci->db->getwhere('users', array('email' => $email));
		if( $query->num_rows() == 1 )
		{
			foreach ($query->result() as $row)
			{
				$decrypted_password = $this->ci->encrypt->decode($row->password);
				return $decrypted_password;
			}
		}
		else
		{
			$this->last_error = "Account not found.";
			return false;
		}
	}
	
	/**
	 * function updateInfo( $username, $field, $data )
	 * Updates user information.
	 * The following fields are reserved and may
	 * not be updated:
	 *	-username
	 *	-id
	 */
	function updateInfo( $username, $field, $data )
	{
		if( $field == 'email' )
		{
			$query = $this->ci->db->getwhere('users', array('email' => $data));
			if( $query->num_rows() == 1 )
			{
				$this->last_error = "Email already in use.";
				return false;
			}
			else
			{
				$this->ci->db->where('username',$username);
				$userdata = array('email'=>$data);
				$this->ci->db->update('users',$userdata);
				return true;
			}
		}
		elseif( $field == 'password' )
		{
			$encrypted_password = $this->ci->encrypt->encode($data);
			
			$this->ci->db->where('username',$username);
			$userdata = array('password'=>$encrypted_password);
			$this->ci->db->update('users',$userdata);
			return true;
		}
		elseif( $field == 'id' || $field == 'username' )
		{
			$this->last_error = "Cannot update restricted field.";
			return false;
		}
		else
		{
			$this->ci->db->where('username',$username);
			$userdata = array($field=>$data);
			$this->ci->db->update('users',$userdata);
			return true;
		}
	}
	
	/**
	 * function getInfo( $username, $field )
	 * Grabs informaltion about the given user.
	 * Note: You may not retrive the users password.
	 * If you are looking to verify their password
	 * use the verifyPass function.
	 */
	function getInfo( $username, $field )
	{
		if( $field == 'password' )
		{
			$this->last_error = "Cannot retrive restricted field.";
			return false;
		}
		else
		{
			$this->ci->db->select($field);
			$query = $this->ci->db->getwhere('users',array('username'=>$username));
			foreach ($query->result() as $row)
			{
				return $row->$field;
			}
		}
	}
	
	/**
	 * function isAdmin( $username )
	 * Returns true if the given user is an admin.
	 */
	function isAdmin( $username )
	{
		$this->ci->db->select('isadmin');
		$query = $this->ci->db->getwhere('users',array('username'=>$username));
		foreach ($query->result() as $row)
		{
			return $row->isadmin;
		}
	}	
	
	/**
	 * function logout()
	 * Logs out the current user.
	 */
	function logout()
	{
		$this->ci->session->unset_userdata('user');
		$this->ci->session->sess_destroy();
		return true;
	}
}
?>
