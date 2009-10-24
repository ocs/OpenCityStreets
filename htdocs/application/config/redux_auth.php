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
 *  10/22/09 -  Added to OCS project for use as initial auth module - AAW
 */


	/**
	 * Tables.
	 **/
	$config['tables']['groups'] = 'user_groups';
	$config['tables']['users'] = 'users';
	$config['tables']['meta'] = 'user_meta';
	
	/**
	 * Default group, use name
	 */
	$config['default_group'] = 'Players';
	 
	/**
	 * Meta table column you want to join WITH.
	 * Joins from users.id
	 **/
	$config['join'] = 'user_id';
	
	/**
	 * Columns in your meta table,
	 * id not required.
	 **/
//	$config['columns'] = array('first_name', 'last_name');
	$config['columns'] = array();	
	/**
	 * A database column which is used to
	 * login with.
	 **/
	$config['identity'] = 'username';

	/**
	 * Email Activation for registration
	 **/
	$config['email_activation'] = true;
	
	/**
	 * Folder where email templates are stored.
     * Default : redux_auth/
	 **/
	$config['email_templates'] = 'email_templates/';

	/**
	 * Salt Length
	 **/
	$config['salt_length'] = 10;
	
?>
