<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 *  Open City Streets Configuration
 *
 *  10/23/09 - Added for OCS global config - AAW
 *
 */


//  General settings

$config['ocs']['gamename']      = "Open City Streets";
$config['ocs']['version']       = "0.01";  

// improve on built in logging, maybe performance penalty
$config['ocs']['log_backtrace']	= 1;


//  Outbound email settings

$config['ocs']['email_addr'] 	= "admin@opencitystreets.com";
$config['ocs']['email_name']	= $config['ocs']['gamename'];
$config['ocs']['email_subj_registration'] = "Active your new Open City Streets account";
$config['ocs']['email_subj_lostpass'] = "Open City Streets password reset request";
