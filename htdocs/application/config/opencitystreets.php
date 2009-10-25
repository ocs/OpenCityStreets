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
 *  Open City Streets Configuration
 *
 *  10/23/09 - Added for OCS global config - AAW
 *  10/25/09 - Merged auth config as part of redux rewrite 
 */


//  General settings

$config['ocs']['gamename']      = "Open City Streets";
$config['ocs']['version']       = "0.01";  
$config['ocs']['motd']		= "Work in progress, expect errors";

// improve on built in logging, maybe performance penalty
$config['ocs']['log_backtrace']	= 1;


//  Outbound email settings - some to be moved into language file

$config['ocs']['email_template_dir'] = 'email_templates/';
$config['ocs']['email_addr'] 	= "admin@opencitystreets.com";
$config['ocs']['email_name']	= $config['ocs']['gamename'];
$config['ocs']['email_subj_registration'] = "Active your new Open City Streets account";
$config['ocs']['email_subj_lostpass'] = "Open City Streets password reset request";


// Auth settings

$config['ocs']['auth_group_table'] = 'user_groups';
$config['ocs']['auth_user_table'] = 'users';
$config['ocs']['auth_meta_table'] = 'user_meta';
$config['ocs']['auth_default_group'] = 'Players';
$config['ocs']['auth_meta_join'] = 'user_id';
$config['ocs']['auth_meta_columns'] = array('language','timezone_id');
$config['ocs']['auth_meta_defaults'] = array('language' => 'english', 'timezone_id' => 12345);
$config['ocs']['auth_identity_column'] = 'username';
$config['ocs']['auth_email_activation'] = true;
$config['ocs']['auth_salt_length'] = 10;
