<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*

This file is part of Open City Streets.

Open City Streets is free software: you can redistribute it and/or modify
it under the terms of the Affero GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Open City Streets is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
Affero GNU General Public License for more details.

You should have received a copy of the Affero GNU General Public License
along with Open City Streets.  If not, see <http://www.gnu.org/licenses/>.

*/


/** ocs_auth.php - User authentication and registration functions
*
*  Based on Redux Authentication 2 - http://code.google.com/p/reduxauth/
*  10/22/09 - Added to OCS project for use as initial auth module - AAW
*  10/23/09 - Many small bug fixes and improvements, commented within - AAW 
*  10/25/09 - Addition improvements, more of a rewrite.. at what point is it no longer Redux Auth? - AAW
*/


class ocs_auth
{
	/**
	* CodeIgniter global
	*
	* @var string
	**/
	protected $ci;
	
	/**
	* account status ('not_activated', etc ...)
	*
	* @var string
	**/
	protected $status;
	
	/**
	* __construct
	*
	* @return void
	* @author Mathew of Redux Auth
	**/
	
	public function __construct()
	{
		$this->ci =& get_instance();
		$email = $this->ci->config->item('email');
		$this->ci->load->library('email', $email);
	}
	
	
	/**
	* Activate user.
	*
	* @return void
	* @author Mathew of Redux Auth
	**/
	public function activate($code)
	{
		return $this->ci->ocs_auth_model->activate($code);
	}
	
	
	/**
	* Deactivate user.
	*
	* @return void
	* @author Mathew of Redux Auth, fixed by AAW
	**/
	public function deactivate($identity)
	{
	    return $this->ci->ocs_auth_model->deactivate($identity);
	}
	
	
	/**
	* Change password.
	*
	* @return void
	* @author Mathew of Redux Auth
	**/
	public function change_password($identity, $old, $new)
	{
        return $this->ci->ocs_auth_model->change_password($identity, $old, $new);
	}
	
	
	/**
	* forgotten password feature
	*
	* @return void
	* @author Mathew of Redux Auth, many changes AAW
	**/
	
	public function forgotten_password($email)
	{
		// check to see if the email is actually valid, helps to avoid complete failure in next part (as in original) - AAW
		
		$useridentity = $this->ci->ocs_auth_model->find_identity_by_column('email',$email);
		
		if ($useridentity === false)
		{
			$this->ci->ocs_logging->log_message('info',"no matching account '$email'");
			return array(false,'No matching account was found.');
		}
		
		// function now returns key directly on success
		$forgotten_password_code = $this->ci->ocs_auth_model->forgotten_password($email);
		
		if ($forgotten_password_code === false)
		{
			$this->ci->ocs_logging->log_message('error',"model failed to set reset code for '$email'");
			return array(false,'Error in model while trying to get/set password reset code');
		}
		else
		{
			// Get user information for email message.
			// fixed not to assume we are using email as identity.. why have this feature if you do not implement everywhere? - AAW
			
			$profile = $this->ci->ocs_auth_model->profile($useridentity);
			
			$data = array('identity'                => $profile->{$this->ci->config->item('auth_identity_column','ocs')},
				'forgotten_password_code' => $forgotten_password_code);
			
			$message = $this->ci->load->view($this->ci->config->item('email_template_dir','ocs').'forgotten_password', $data, true);
			
			$this->ci->email->clear();
			$this->ci->email->set_newline("\r\n");
			$this->ci->email->from($this->ci->config->item('email_addr','ocs'), $this->ci->config->item('email_name','ocs'));
			$this->ci->email->to($email); 
			$this->ci->email->subject($this->ci->config->item('email_subj_lostpass','ocs'));
			$this->ci->email->message($message);
			$emailresult = $this->ci->email->send();
			
			if ($emailresult === false)
			{
				// once again, we've no easy way to back out at this point.. later i suppose
				$this->ci->ocs_logging->log_message('error','error sending password reset email');
				return array(false,'Error sending password reset email');
			}
			
			$this->ci->ocs_logging->log_message('info',"sent password reset email to '$email'");
			return array(true,null);
		}
	}
	
	
	
	public function forgotten_password_complete($code,$password)
	{
		// apply new password - complete rewrite - AAW
		
		$useridentity = $this->ci->ocs_auth_model->find_identity_by_column('forgotten_password_code',$code);
		
		if ($useridentity === false)
		{
			$this->ci->ocs_logging->log_message('info','verification code not found');
			return array(false,'The verification code is not valid.');
		}
		
		$result = $this->ci->ocs_auth_model->set_password($useridentity,$password);
		
		if ($result === false)
		{
			$this->ci->ocs_logging->log_message('error',"model failed to update password for '$useridentity'");
			return array(false, 'Model failed to update password');
		}
		
		$this->ci->ocs_logging->log_message('info',"password reset for '$useridentity'");
		return array(true,null);
	}
	
	
	
	/**
	* register
	*
	* @author Mathew of Redux Auth, changes by AAW
	**/
	
	public function register($username, $password, $email)
	{
		$email_activation = $this->ci->config->item('auth_email_activation','ocs');
		$email_folder     = $this->ci->config->item('email_template_dir','ocs');
		
		$result = $this->ci->ocs_auth_model->register($username, $password, $email);
		if ($result === false)
		{
			$this->ci->ocs_logging->log_message('error','model reports failure registering user');
			return array(false,'Error adding new user to database');
		}
		else
		{
			if ($email)
			{
				// send registration email with activation code
				
				// function now returns code directly AAW
				$activation_code = $this->ci->ocs_auth_model->deactivate($username);
				
				if ($activation_code === false) 
				{	 
					return array(false,'Error generating activation code');
				}
				
				$data = array('username' => $username,
					'password'   => $password,
					'email'      => $email,
					'activation' => $activation_code);
				
				$message = $this->ci->load->view($email_folder.'registration', $data, true);
				
				$this->ci->email->clear();
				$this->ci->email->set_newline("\r\n");
				$this->ci->email->from($this->ci->config->item('email_addr','ocs'), $this->ci->config->item('email_name','ocs'));
				$this->ci->email->to($email);
				$this->ci->email->subject($this->ci->config->item('email_subj_registration','ocs'));
				$this->ci->email->message($message);
				
				if ($this->ci->email->send())
				{
					$this->ci->ocs_logging->log_message('info',"sent registration email for new user '$username' to '$email'");
					return array(true,null);
				}
				else
				{
					// So we have inserted the user details including the activation code, but can't 
					// send it to user.. crap?
					// Really should roll back the DB or something
					$this->ci->ocs_logging->log_message('error',"error sending registration email to '$email'");
					return array(false,'An error occured while sending the registration email.');
				}
			}
		}
	}
	
	/**
	* login
	*
	* @return void
	* @author Mathew of Redux Auth
	**/
	public function login($identity, $password)
	{
		return $this->ci->ocs_auth_model->login($identity, $password);
	}
	
	/**
	* logout
	*
	* @return void
	* @author Mathew of Redux Auth
	**/
	public function logout()
	{
	    $identity = $this->ci->config->item('auth_identity_column','ocs');
	    $this->ci->ocs_logging->log_message('info', "'". $this->ci->session->userdata($identity) . "' logging out");
	    $this->ci->session->unset_userdata($identity);
		$this->ci->session->sess_destroy();
	}
	
	
	/**
	* logged_in
	*
	* @return void
	* @author Mathew of Redux Auth
	**/
	
	public function logged_in()
	{
	    $identity = $this->ci->config->item('auth_identity_column','ocs');
		return ($this->ci->session->userdata($identity)) ? true : false;
	}
	
	/**
	* Profile
	*
	* @return void
	* @author Mathew of Redux Auth
	**/
	
	public function profile()
	{
	    $session = $this->ci->config->item('auth_identity_column','ocs');
	    $identity = $this->ci->session->userdata($session);
	    
	    return $this->ci->ocs_auth_model->profile($identity);
	}
	
	
	/**
	* get_languages - return array of available language choices
	* accepts ip address, future versions may recommend based on geoip
	*
	* @author Aaron Wolfe
	**/
	
	public function get_languages($client_ip = false)
	{
		return $this->ci->ocs_auth_model->get_languages();
	}
		
	
	
}
