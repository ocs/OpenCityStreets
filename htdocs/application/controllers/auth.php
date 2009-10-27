<?php

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

/*
 *  Open City Streets user authentication/management
 *
 * 
 *  10/23/09 - Inital version.  Ugly but functional - AAW
 *
 */


 
 class Auth extends Controller {
 	 
 	 function __construct()
 	 {
 	 	 // All other controllers should inherit OCS_Controller, so that users are sent to this one if they are not logged in.
 	 	 // This controller obviously must not, so any functions that should be called while logged in (delete account) must check themselves
 	 	 
 	 	 parent::Controller();	
 	 	 $this->lang->load('auth');
 	 }
 	 

	function login($state)
	{
		
		// Collects username and password on form
		// Validates, authorizes, and redirects to game/welcome if good login
		// view provides links to register, activate, etc
		
		$this->form_validation->set_rules('username', 'Username', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		
		if (($this->form_validation->run() == false) or ($state == 'failed'))
		{
			$data['state'] = $state;
			$data['content'] = $this->load->view('auth/login', $data, true);
			$this->load->view('template', $data);
		}
		else
		{
			$username = $this->input->post('username');
			$password = $this->input->post('password');
			
			if ($this->ocs_auth->login($username, $password))
			{
				redirect('game/welcome');
			}
			else
			{
				
				$this->set_msg('error',$this->lang->line('auth_invalid_login'));
				redirect('auth/login/failed');
			}
		}		
	}


	
	function logout()
	{
		// logs user out
		
		$this->ocs_auth->logout();
		$this->set_msg('success', $this->lang->line('auth_logged_out') . ' of ' . $this->config->item('gamename','ocs'));
		redirect('auth/login/loggedout');
	}

	function register()
	{
		
		// collects registration info on form
		// validates, calls ocs_auth->register
		
		
		// load recaptcha reqs
		$this->load->library('recaptcha');
		$this->lang->load('recaptcha');
		
		// validate input
		$this->form_validation->set_rules('username', 'Username', 'required|callback_username_check');
        $this->form_validation->set_rules('email', 'Email Address', 'required|callback_email_check|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|matches[password2]');
		$this->form_validation->set_rules('password2', 'Password Confirm', 'required');
		$this->form_validation->set_rules('recaptcha_response_field','ReCAPTCHA','required|callback_check_captcha');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		
        if ($this->form_validation->run() == false)
        {
        	$data['recaptcha'] = $this->recaptcha->get_html();
			$data['languages'] = $this->ocs_auth->get_languages($this->input->ip_address());
            $data['content'] = $this->load->view('auth/register', $data, true);
            $this->load->view('template', $data);
        }
		else
		{
			// register user
			
			$username = $this->input->post('username');
			$email    = $this->input->post('email');
			$password = $this->input->post('password');
			
			list($register, $reason) = $this->ocs_auth->register($username, $password, $email);
			
			if ($register)
			{
				$data['email'] = $email;
				$data['content'] = $this->load->view('auth/register_success', $data, true);
				$this->load->view('template', $data);
			}
			else
			{
				// not sure how we want to be displaying errors..
				// need localization, standards, etc.. for now:
				$this->set_msg('error',$reason);
				
				redirect('auth/register');
			}
		}
	}
	
	
	
	function activate($code = false)
	{
		// handle code coming from URL or from form
		if ($code === false)
		{
			$code = $this->input->post('code');
			if (empty($code))
			{
				$data['content'] = $this->load->view('auth/activate', null, true);
				$this->load->view('template', $data);
				return;
			}
		}
		
		$activate = $this->ocs_auth->activate($code);
			
		if ($activate)
		{
			$this->set_msg('success',$this->lang->line('auth_account_activated'));
			redirect('auth/login/activated');
		}
		else
		{
			$this->set_msg('error',$this->lang->line('auth_invalid_activation_code'));
			redirect('auth/activate');
		}
		
	}

	
	function lostpass()
	{
		// process lost password requests
		
		$this->form_validation->set_rules('email', 'Email Address', 'required');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		
		if ($this->form_validation->run() == false)
		{
			$data['content'] = $this->load->view('auth/lostpass', null, true);
			$this->load->view('template', $data);
		}
		else
		{
			$email = $this->input->post('email');
			list($result,$reason) = $this->ocs_auth->forgotten_password($email);
			
			if ($result)
			{
				$this->set_msg('success',$this->lang->line('auth_email_sent_to'). " $email. " . $this->lang->line('auth_check_inbox'));
				redirect('auth/login/backfromlostpass');
			}
			else
			{
				$this->set_msg('error',$reason);
				redirect('auth/lostpass');
			}
		}
	}
	
	
	
	function resetpass()
	{
		// handle url sent in lost password request emails
		
		$this->form_validation->set_rules('code', 'Verification code', 'required');
		$this->form_validation->set_rules('password', 'New password', 'required|matches[password2]');
		$this->form_validation->set_rules('password2', 'Password Confirm', 'required');
		
		
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		
		if ($this->form_validation->run() == false)
		{
			$data['content'] = $this->load->view('auth/resetpass', null, true);
			$this->load->view('template', $data);
		}
		else
		{
			$code = $this->input->post('code');
			$newpass = $this->input->post('password');
			
			list($result,$reason) = $this->ocs_auth->forgotten_password_complete($code,$newpass);
			
			if ($result)
			{
				$this->set_msg('success',$this->lang->line('auth_password_reset'));
				redirect('auth/login/backfromreset');	
			}
			else
			{
				$this->set_msg('error',$reason);
				redirect('auth/resetpass');
			}
		}
	}
	
	
	function delete()
	{
		// require user is logged in
		if ($this->ocs_auth->logged_in() === false)   
        {
            redirect('auth/login/notloggedin');
        }
        
        $this->form_validation->set_rules('password', 'Password', 'required');
				
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		
		if ($this->form_validation->run() == false)
		{
			$data['content'] = $this->load->view('auth/delete', null, true);
			$this->load->view('template', $data);
		}
		else
		{
			$password  = $this->input->post('password');
			list($result,$reason) = $this->ocs_auth->delete_account($password);
			
			if ($result === false)
			{
				$this->set_msg('error',$reason);
				redirect('auth/delete');
			}
			
			$this->logout();
		}
	}
	
	
	
	function username_check($username)
	{
		// prevent duplicates in registration, needs localization like everything else
		
		if ($this->ocs_auth_model->username_check($username))
		{
			$this->form_validation->set_message('username_check', 'The username "'.$username.'" is already registered.');
			return false;
		}
		else
		{
			return true;
		}
	}
	
	function email_check($email)
	{
		// prevent duplicate email
		
		
		if ($this->ocs_auth_model->email_check($email))
		{
			$this->form_validation->set_message('email_check', 'The email "'.$email.'" has already been used to register an account.');
			return false;
		}
		else
		{
			return true;
		}
	}
	
	
	function check_captcha($val) 
	{
		if ($this->recaptcha->check_answer($this->input->ip_address(),$this->input->post('recaptcha_challenge_field'),$val)) 
		{
			return true;
		} 
		else 
		{
			$this->form_validation->set_message('check_captcha',$this->lang->line('recaptcha_incorrect_response'));
			return false;
		}
	}
	
	function set_msg($status = 'error',$message = 'No message')
	{
		// shortcut for setting flash message and css class, probably changed in future
		 $this->session->set_flashdata('message', "<p class='$status'>$message</p>");
		 log_message('info',"SETMSG: $message");
	}
	
}

