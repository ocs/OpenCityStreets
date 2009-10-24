<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" :
 * <thepixeldeveloper@googlemail.com> wrote this file. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return Mathew Davies
 * ----------------------------------------------------------------------------
 */


/*
 *  10/22/09 - Added to OCS project for use as initial auth module - AAW
 *  10/23/09 - Many small bug fixes and improvements, commented within - AAW 
 *
 */


 
/**
* Redux Authentication 2
*/
class redux_auth
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
	 * @author Mathew
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
	 * @author Mathew
	 **/
	public function activate($code)
	{
		return $this->ci->redux_auth_model->activate($code);
	}
	
	/**
	 * Deactivate user.
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function deactivate($identity)
	{
	    return $this->ci->redux_auth_model->deactivate($code);
	}
	
	/**
	 * Change password.
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function change_password($identity, $old, $new)
	{
        return $this->ci->redux_auth_model->change_password($identity, $old, $new);
	}

	/**
	 * forgotten password feature
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function forgotten_password($email)
	{
		// check to see if the email is actually valid, helps to avoid complete failure in next part as in original - AAW

		$useridentity = $this->ci->redux_auth_model->find_identity_by_column('email',$email);
		
		if ($useridentity === false)
		{
			return array(false,'No matching account was found.');
		}

	
		$forgotten_password = $this->ci->redux_auth_model->forgotten_password($email);
		
		if ($forgotten_password)
		{
			// Get user information.
			// fixed not to assume we are using email as identity.. why have this feature if you do not implement everywhere? - AAW

			$profile = $this->ci->redux_auth_model->profile($useridentity);

			$data = array('identity'                => $profile->{$this->ci->config->item('identity')},
    			          'forgotten_password_code' => $this->ci->redux_auth_model->forgotten_password_code);
                
			$message = $this->ci->load->view($this->ci->config->item('email_templates').'forgotten_password', $data, true);
				
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

			return array(true,null);
		}
		else
		{
			return array(false,'Error in model while trying to reset password');
		}
	}
	


	public function forgotten_password_complete($code,$password)
	{
		// apply new password - complete rewrite - AAW
		
	    	$useridentity = $this->ci->redux_auth_model->find_identity_by_column('forgotten_password_code',$code);
		
		if ($useridentity === false)
		{
			return array(false,'The verification code is not valid.');
		}

		$result = $this->ci->redux_auth_model->set_password($useridentity,$password);

		if ($result === false)
		{
			$this->ci->ocs_logging->log_message('error','model failed to update password');
			return array(false, 'Model failed to update password');
		}

		return array(true,null);
	}



	/**
	 * register
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function register($username, $password, $email)
	{
	    	$email_activation = $this->ci->config->item('email_activation');
	    	$email_folder     = $this->ci->config->item('email_templates');

		$result = $this->ci->redux_auth_model->register($username, $password, $email);
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
			
				$deactivate = $this->ci->redux_auth_model->deactivate($username);

				if ($deactivate === false) 
				{	 
					return array(false,'Error deactivating account for email reg');
				}

				$activation_code = $this->ci->redux_auth_model->activation_code;

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
					return array(true,null);
				}
				else
				{
					// So we have inserted the user details including the activation code, but can't 
					// send it to user.. crap?
					// Really should roll back the DB or something
					$this->ci->ocs_logging->log_message('error','error sending registration email');
					return array(false,'An error occured while sending the registration email.');
				}
			}
		}
	}
	
	/**
	 * login
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function login($identity, $password)
	{
		return $this->ci->redux_auth_model->login($identity, $password);
	}
	
	/**
	 * logout
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function logout()
	{
	    	$identity = $this->ci->config->item('identity');
	    	$this->ci->session->unset_userdata($identity);
		$this->ci->session->sess_destroy();
	}
	
	/**
	 * logged_in
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function logged_in()
	{
	    $identity = $this->ci->config->item('identity');
		return ($this->ci->session->userdata($identity)) ? true : false;
	}
	
	/**
	 * Profile
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function profile()
	{
	    $session  = $this->ci->config->item('identity');
	    $identity = $this->ci->session->userdata($session);
	    return $this->ci->redux_auth_model->profile($identity);
	}
	
}
