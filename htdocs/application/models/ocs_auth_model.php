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

/*
*  Open City Streets - User auth model
*
*  Portions and general structure from Redux Auth 2  http://code.google.com/p/reduxauth/
*
*  10/22/09 -  Added to OCS project for use as initial auth module, slightly changed some column names to be more consistent - AAW
*  10/23/09 -  Improved error handling/reporting.  We will probably end up rewriting this whole thing at some point :( - AAW
*  10/24/09 -  Rewriting the whole thing.  It was just too messy and weird - AAW
*/


class ocs_auth_model extends Model
{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	* Password functions
	* 
	* Hash password : Hashes the password to be stored in the database.
	* Hash password db : This function takes a password and validates it
	* against an entry in the users table.
	* Salt : Generates a random salt value.
	*
	* @author Mathew of Redux Auth, minor changes by AAW
	*/
	
	
	/**
	* Hashes the password to be stored in the database.
	*
	* @author Mathew of Redux Auth, minor changes by AAW
	**/
	
	public function hash_password($password = false)
	{
	    $salt_length = $this->config->item('auth_salt_length','ocs');
	    
	    if ($password === false)
	    {
	        return false;
	    }
	    
		$salt = $this->salt();
		
		$password = $salt . substr(sha1($salt . $password), 0, -$salt_length);
		
		return $password;		
	}
	
	
	/**
	* This function takes a password and validates it
	* against an entry in the users table.
	*
	* @author Mathew of Redux Auth, minor changes by AAW
	**/
	
	public function hash_password_db($identity = false, $password = false)
	{
	    $identity_column   = $this->config->item('auth_identity_column','ocs');
	    $users_table       = $this->config->item('auth_user_table','ocs');
	    $salt_length       = $this->config->item('auth_salt_length','ocs');
	    
	    if ($identity === false || $password === false)
	    {
	        return false;
	    }
	    
	    $query  = $this->db->select('password')
	    ->where($identity_column, $identity)
	    ->get($users_table);
	    
        $result = $query->row();
        
		if ($query->num_rows() !== 1)
		{
			// no user or duplicate users?, either way, no login
		    return false;
	    }
	    
		$salt = substr($result->password, 0, $salt_length);
		
		$password = $salt . substr(sha1($salt . $password), 0, -$salt_length);
        
		return $password;
	}
	
	
	/**
	* Generates a random salt value.
	*
	* @author Mathew of Redux Auth
	**/
	public function salt()
	{
		return substr(md5(uniqid(rand(), true)), 0, $this->config->item('auth_salt_length','ocs'));
	}
    
	
	
	/**
	* Sets a user's password 
	*
	* @author Aaron Wolfe
	*/
	
	public function set_password($useridentity = false,$password = false)
	{
		$identity_column   = $this->config->item('auth_identity_column','ocs');
	    $users_table       = $this->config->item('auth_user_table','ocs');
	    
		if ($this->identity_check($useridentity) == false)
		{
			$this->ocs_logging->log_message('error','asked to set password for nonexistant user?');
			return false;
		}
		
		if ($password === false)
		{
			return false;
		}
		
		$data = array('password' => $this->hash_password($password));
		
		$this->db->update($users_table, $data, array($identity_column => $useridentity));
		
		if ($this->db->affected_rows() == 1)
		{
			return true;
		}
		else
		{
			$this->ocs_logging->log_message('error','error updating user record with new password');
            return false;
		}
	}
	
	
	/**
	* Activation functions
	* 
	* Activate : Validates and removes activation code.
	* Deactivae : Updates a users row with an activation code.
	*
	* @author Mathew of Redux Auth, minor changes AAW
	*/
	
	
	/**
	* activate
	*
	* @author Mathew of Redux Auth, minor changes AAW
	**/
	
	public function activate($code = false)
	{
	    $identity_column   = $this->config->item('auth_identity_column','ocs');
	    $users_table       = $this->config->item('auth_user_table','ocs');
	    
	    if ($code === false)
	    {
	        return false;
	    }
	    
	    $query = $this->db->select($identity_column)
	    ->where('activation_code', $code)
	    ->get($users_table);
	    
		$result = $query->row();
        
		if ($query->num_rows() !== 1)
		{
		    return false;
		}
	    
		$identity = $result->{$identity_column};
		
		$data = array('activation_code' => '');
        
		$this->db->update($users_table, $data, array($identity_column => $identity));
		
		if ($this->db->affected_rows() == 1)
		{
			$this->ocs_logging->log_message('info',"activated user '$identity'");
			return true;
		}
		else
		{
			$this->ocs_logging->log_message('error','db error activating user account');
			return false;
		}
	}
	
	
	/**
	* Deactivate
	*
	* @author Mathew, changes AAW
	* now returns activation code directly on success
	**/
	
	public function deactivate($username = false)   // not identity column agnostic.. don't think we care AAW
	{
		$users_table = $this->config->item('auth_user_table','ocs');
		
	    if ($username === false)
	    {
	        return false;
	    }
	    
		$activation_code = sha1(md5(microtime()));
		
		$data = array('activation_code' => $activation_code);
        
		$this->db->update($users_table, $data, array('username' => $username));
		
		if ($this->db->affected_rows() == 1)
		{
			$this->ocs_logging->log_message('info',"deactivated user '$username'");
			return($activation_code);
		}
		else
		{
			$this->ocs_logging->log_message('error','db error deactivating user account');
			return false;
		}
	}
	
	
	/**
	* change password
	*
	* @author Mathew, minor changes AAW 
	* want to rewrite this, logic for comparing old doesn't belong in here
	**/
	
	public function change_password($identity = false, $old = false, $new = false)
	{
	    $identity_column   = $this->config->item('auth_identity_column','ocs');
	    $users_table       = $this->config->item('auth_user_table','ocs');
	    
	    if ($identity === false || $old === false || $new === false)
	    {
	        return false;
	    }
	    
	    $query  = $this->db->select('password')
	    ->where($identity_column, $identity)
	    ->get($users_table);
        
        if ($this->db->affected_rows() != 1)
		{
			$this->ocs_logging->log_message('error','could not find user account');
			return false;
		}          	   
		
	    $result = $query->row();
	    
	    $db_password = $result->password; 
	    $old         = $this->hash_password_db($identity, $old);
	    $new         = $this->hash_password($new);
	    
	    if ($db_password === $old)
	    {
	        $data = array('password' => $new);
	        
	        $this->db->update($users_table, $data, array($identity_column => $identity));
	        
	        return ($this->db->affected_rows() == 1) ? true : false;
	    }
	    
	    return false;
	}
	
	
	
	/**
	* Checks username.
	*
	* @author Mathew of Redux Auth
	**/
	public function username_check($username = false)
	{
	    $users_table = $this->config->item('auth_user_table','ocs');
	    
	    if ($username === false)
	    {
	        return false;
	    }
	    
	    $query = $this->db->select('user_id')
	    ->where('username', $username)
	    ->get($users_table);
		
		if ($query->num_rows() == 1)
		{
			return true;
		}
		
		return false;
	}
	
	
	/**
	* Checks email.
	*
	* @author Mathew of Redux Auth
	**/
	
	public function email_check($email = false)
	{
	    $users_table = $this->config->item('auth_user_table','ocs');
	    
	    if ($email === false)
	    {
	        return false;
	    }
	    
	    $query = $this->db->select('user_id')
	    ->where('email', $email)
	    ->get($users_table);
		
		if ($query->num_rows() == 1)
		{
			return true;
		}
		
		return false;
	}
	
	
	/**
	* Find identity of user using any unique column
	*
	* @author Aaron Wolfe
	**/
	
	public function find_identity_by_column($column = false, $data = false)
	{
		// Lookup user account by named column, return whatever contents identity column or false if no match/multiple match - AAW
		
		$identity_column   = $this->config->item('auth_identity_column','ocs');
	    $users_table       = $this->config->item('auth_user_table','ocs');
	    
        if (($column === false) or ($data === false))
        {
        	return false;
        }
        
		$query = $this->db->select($identity_column)
		->where($column, $data)
		->get($users_table);
		
		if ($query->num_rows() == 1)
		{
			return array_pop($query->row_array());			
		}
		else
		{
			return false;
		}
	}
	
	
	/**
	* Identity check
	*
	* @author Mathew of Redux Auth
	**/
	
	protected function identity_check($identity = false)
	{
	    $identity_column   = $this->config->item('auth_identity_column','ocs');
	    $users_table       = $this->config->item('auth_user_table','ocs');
	    
	    if ($identity === false)
	    {
	        return false;
	    }
	    
	    $query = $this->db->select('user_id')
	    ->where($identity_column, $identity)
	    ->get($users_table);
		
		if ($query->num_rows() == 1)
		{
			return true;
		}
		
		return false;
	}
	
	
	/**
	* Check if user is active
	*
	* @author Aaron Wolfe
	*
	* looks for presence of activation code in record of given identity, which indicates the account is disabled or awaiting confirmation
	**/
	
	public function identity_is_active($identity = false)
	{
	    $identity_column   = $this->config->item('auth_identity_column','ocs');
	    $users_table       = $this->config->item('auth_user_table','ocs');
	    
	    if ($identity === false)
	    {
	        return false;
	    }
	    
	    $query = $this->db->select('activation_code')
	    ->where($identity_column, $identity)
	    ->get($users_table);
		
		if ($query->num_rows() == 1)
		{
			// user exists
			
			$row = $query->result();
			
			if (empty($row->activation_code))
			{
				// active user
				return true;
			}
			else
			{
				// user is deactivated
				return false;
			}
		}
		
		// identity not found
		return false;
	}
	
	
	/**
	* Insert a forgotten password key.
	*
	* @author Mathew of Redux Auth, many changes by AAW
	*
	* added error reporting and fixed some bugs to where it might actually work - AAW
	* removed instance variable storing reset code, why this was used I do not know
	* just return the code as success result AAW
	**/
	
	public function forgotten_password($email = false)
	{
	    $users_table       = $this->config->item('auth_user_table','ocs');
	    
	    if ($email === false)
	    {
	    	$this->ocs_logging->log_message('info','no email specified?');
	        return false;
	    }

		$key = $this->hash_password(microtime().$email);
		
		$data = array('forgotten_password_code' => $key);
		
		$this->db->update($users_table, $data, array('email' => $email));
		
		if ($this->db->affected_rows() == 1)
		{
			$this->ocs_logging->log_message('info',"inserted forgotten pw key for '$email'");
			return($key);
		}
		else
		{
			$this->ocs_logging->log_message('error','failed to insert forgotten pw key');
			return false;
		}
	}
	
	
	/**
	* returns user profile
	*
	* @author Mathew of Redux Auth, changes by AAW
	**/
	
	public function profile($identity = false)
	{
	    $users_table     = $this->config->item('auth_user_table','ocs');
	    $groups_table    = $this->config->item('auth_group_table','ocs');
	    $meta_table      = $this->config->item('auth_meta_table','ocs');
	    $meta_join       = $this->config->item('auth_meta_join','ocs');
	    $meta_columns	 = $this->config->item('auth_meta_columns','ocs');
	    $identity_column = $this->config->item('auth_identity_column','ocs');    
	    
	    if ($identity === false)
	    {
	        return false;
	    }
	    
		$this->db->select($users_table.'.user_id, '.
			$users_table.'.username, ' .
			$users_table.'.password, '.
			$users_table.'.email, '.
			$users_table.'.activation_code, '.
			$users_table.'.forgotten_password_code , '.
			$users_table.'.ip_address, '.
			$groups_table.'.name AS `group`');
		
		if (!empty($meta_columns))
		{
		    foreach ($this->config->item('auth_meta_columns','ocs') as $value)
    		{
    			$this->db->select($meta_table.'.'.$value);
    		}
		}
		
		$this->db->from($users_table);
		$this->db->join($meta_table, $users_table.'.user_id = '.$meta_table.'.'.$meta_join, 'left');
		$this->db->join($groups_table, $users_table.'.group_id = '.$groups_table.'.group_id', 'left');
		
		$this->db->where($users_table.'.'.$identity_column, $identity);
	    
		$this->db->limit(1);
		
		$i = $this->db->get();
		
		if ($i->num_rows == 1)
		{
			return($i->row());
		}
		else
		{
			$this->ocs_logging->log_message('error','db error getting user profile');
			return false;
		}
	}
	
	
	/**
	* register
	*
	* @author Mathew of Redux Auth, mostly redone by AAW
	*
	* 10/23/09 - check for existing users and return reasons for failure - AAW
	*
	*
	**/
	
	public function register($username = false, $password = false, $email = false)
	{
	    $users_table     = $this->config->item('auth_user_table','ocs');
	    $groups_table    = $this->config->item('auth_group_table','ocs');
	    $meta_table      = $this->config->item('auth_meta_table','ocs');
	    $meta_join       = $this->config->item('auth_meta_join','ocs');
	    $identity_column = $this->config->item('auth_identity_columnn'); 
	    $additional_columns = $this->config->item('auth_meta_columns','ocs');
	    
	    if ($username === false || $password === false || $email === false)
	    {
	    	$this->ocs_logging->log_message('info', 'missing parameters?');
	        return false;
	    }
	    
        // Group ID
	    $query    = $this->db->select('group_id')->where('name', $this->config->item('auth_default_group','ocs'))->get($groups_table);
	    $result   = $query->row();
	    $group_id = $result->group_id;
	    
        // IP Address
        $ip_address = $this->input->ip_address();
	    
		$password = $this->hash_password($password);
		
        // Users table.
		$data = array('username' => $username, 
			'password' => $password, 
			'email'    => $email,
			'group_id' => $group_id,
			'ip_address' => $ip_address);
		
		$this->db->insert($users_table, $data);
        
		// Meta table.
		// use defaults if available and not set in POST data (BTW can/should we be looking at post directly in the model?) AAW
		$id = $this->db->insert_id();
		
		if ($id === false)
		{
			$this->ocs_logging->log_message('error', 'insert new user reg failed');
			return false;
		}
		
		$data = array($meta_join => $id);
		
		if (!empty($additional_columns))
	    {
	    	$default_values = array();
	    	$default_values = $this->config->item('auth_meta_defaults','ocs');
	    	
	        foreach ($additional_columns as $input)
	        {
	        	$post_value = $this->input->post($input);
	        	
	        	if (!empty($post_value))
	        	{
	        		$data[$input] = $post_value;
	        	}
	        	else if (isset($default_values[$input]))
	        	{
	        		$data[$input] = $default_values[$input];
	        	}
	        	else
	        	{
	        		$this->ocs_logging->log_message('debug', "no default or post value for '$input'");
	        	}	
	        }
	    }
        
		$this->db->insert($meta_table, $data);
		
		if ($this->db->affected_rows() > 0)
		{
			$this->ocs_logging->log_message('info',"new registration, $username/$email");
			return true;
		}
		else
		{
			$this->ocs_logging->log_message('error','insert new user meta record failed');
			return false;
		}
	}
	
	
	
	/**
	* login
	*
	* rewrite by AAW
	*
	* checks credentials, true for valid
	**/
	
	public function login($identity = false, $password = false)
	{
	    $identity_column   = $this->config->item('auth_identity_column','ocs');
	    $users_table       = $this->config->item('auth_user_table','ocs');
	    
	    if ($identity === false || $password === false)
	    {
	    	$this->ocs_logging->log_message('info','login attempt with null credentials?');
	        return false;
	    }
	    
	    if ( $this->identity_is_active($identity) == false)
	    {
	    	$this->ocs_logging->log_message('info',"login attempt by invalid/inactive user '$identity'");
	        return false;
	    }	
	    
	    
	    $query = $this->db->select('password')
	    ->where($identity_column, $identity)
	    ->get($users_table);
	    
        $result = $query->row();
        
        if ($query->num_rows() == 1)
        {
            $password = $this->hash_password_db($identity, $password);
            
    		if ($result->password === $password)
    		{
    			$this->ocs_logging->log_message('info',"user '$identity' logged in");
    		    $this->session->set_userdata($identity_column,  $identity);
    		    return true;
    		}
    		else
    		{
    			$this->ocs_logging->log_message('info',"user '$identity' bad password");
    			return false;
    		}
        }
        
        $this->ocs_logging->log_message('info',"user '$identity' verified but couldn't select password?");
		return false;		
	}


   	/**
	* get_languages - return array of languages
	*
	* @author Aaron Wolfe
	**/
	
	public function get_languages()
	{
	    $language_table       = $this->config->item('auth_language_table','ocs');
	    $languages = array();
	    
	    $query = $this->db->get($language_table);
		
		foreach ($query->result() as $row)
		{
			$languages[$row->lang_name]=$row->lang_extendedname;
		}
		
		return($languages);
	}
		
}
