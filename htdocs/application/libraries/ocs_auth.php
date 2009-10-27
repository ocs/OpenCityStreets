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
	**/
	protected $ci;
	

	public function __construct()
	{
		$this->ci =& get_instance();
		$email = $this->ci->config->item('email');
		$this->ci->load->library('email', $email);
		$this->ci->lang->load('auth');
	}
	
	
	public function activate($code)
	{
		return $this->ci->ocs_auth_model->activate($code);
	}
	
	public function deactivate($identity)
	{
	    return $this->ci->ocs_auth_model->deactivate($identity);
	}
	
	
	
	public function delete_account($password = false)
	{
		// for now all we have is the user itself to remove
		
		$session = $this->ci->config->item('auth_identity_column','ocs');
	    $identity = $this->ci->session->userdata($session);
		
		$user_id = $this->ci->ocs_auth_model->get_user_id($identity);
		
		if ($user_id === false)
		{
			return array(false,$this->ci->lang->line('auth_invalid_login'));
		}
				
		$idcheck = $this->ci->ocs_auth_model->check_password($identity,$password);
		
		if ($idcheck === false)
		{
			return array(false,$this->ci->lang->line('auth_invalid_login'));
		}
		
		$result = $this->ci->ocs_auth_model->delete_user($user_id);
		
		if ($result === false)
		{
			return array(false,$this->ci->lang->line('auth_model_error'));
		}
		
        return array(true,null);
	}
	

	public function forgotten_password($email)
	{
		
		$useridentity = $this->ci->ocs_auth_model->find_identity_by_column('email',$email);
		
		if ($useridentity === false)
		{
			$this->ci->ocs_logging->log_message('info',"no matching valid account '$email'");
			return array(false, $this->ci->lang->line('auth_invalid_login'));
		}
		
		if ($this->ci->ocs_auth_model->identity_is_active($useridentity) === false)
		{
			$this->ci->ocs_logging->log_message('info',"attempt to reset password on disabled account '$email'");
			return array(false,$this->ci->lang->line('auth_invalid_login'));
		}
		
		// function now returns key directly on success
		$forgotten_password_code = $this->ci->ocs_auth_model->forgotten_password($email);
		
		if ($forgotten_password_code === false)
		{
			$this->ci->ocs_logging->log_message('error',"model failed to set reset code for '$email'");
			return array(false,$this->ci->lang->line('auth_model_error'));
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
				return array(false,$this->ci->lang->line('auth_email_error'));
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
			return array(false,$this->ci->lang->line('auth_invalid_verification_code'));
		}
		
		$result = $this->ci->ocs_auth_model->set_password($useridentity,$password);
		
		if ($result === false)
		{
			$this->ci->ocs_logging->log_message('error',"model failed to update password for '$useridentity'");
			return array(false, $this->ci->lang->line('auth_model_error'));
		}
		
		$this->ci->ocs_logging->log_message('info',"password reset for '$useridentity'");
		return array(true,null);
	}
	
	
	
	public function register($username, $password, $email)
	{
		$email_activation = $this->ci->config->item('auth_email_activation','ocs');
		$email_folder     = $this->ci->config->item('email_template_dir','ocs');
		
		$result = $this->ci->ocs_auth_model->register($username, $password, $email);
		if ($result === false)
		{
			$this->ci->ocs_logging->log_message('error','model reports failure registering user');
			return array(false,$this->ci->lang->line('auth_model_error'));
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
					return array(false, $this->ci->lang->line('auth_model_error'));
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
					// send it to user.. 
					
					// delete the record if we can..
					$user_id = $this->get_user_id($username);
					if ($user_id === false)
					{
						$this->ci->ocs_logging->log_message('error','error sending reg email, then error finding user id, hmmm');
					}
					else
					{
						$deltry = $this->delete_user($user_id);
						if ($deltry === false)
						{
							$this->ci->ocs_logging->log_message('error','error sending reg email, found user id but error deleting account.. hmmmmmmm');
						}
						else
						{
							$this->ci->ocs_logging->log_message('error',"error sending registration email to '$email', deleted account");
						}
					}
					
					return array(false,$this->ci->lang->line('auth_email_error'));
				}
			}
		}
	}



	public function login($identity, $password)
	{
		return $this->ci->ocs_auth_model->login($identity, $password);
	}
	

	public function logout()
	{
	    $identity = $this->ci->config->item('auth_identity_column','ocs');
	    $this->ci->ocs_logging->log_message('info', "'". $this->ci->session->userdata($identity) . "' logging out");
	    $this->ci->session->unset_userdata($identity);
	    $this->ci->session->unset_userdata('user_id');
		$this->ci->session->sess_destroy();
	}
	
	
	
	public function logged_in()
	{
		// check for logged in..
	    $identity = $this->ci->config->item('auth_identity_column','ocs');
		return ($this->ci->session->userdata($identity)) ? true : false;
	}
	
	
	public function profile()
	{
	    $session = $this->ci->config->item('auth_identity_column','ocs');
	    $identity = $this->ci->session->userdata($session);
	    
	    return $this->ci->ocs_auth_model->profile($identity);
	}
	
	
	/**
	* get_languages - return array of available language choices
	* accepts ip address, future versions may recommend based on geoip
	**/
	
	public function get_languages($client_ip = false)
	{
		return $this->ci->ocs_auth_model->get_languages();
	}
		
	
	
}
