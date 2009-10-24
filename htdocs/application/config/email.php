<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * 10/22/09 - Added for emailing account confirmations, could also be used for player notifications, etc  AAW
 */

$config['email']['protocol']  = 'smtp';
$config['email']['smtp_host'] = 'mailbox.shieldmx.com';
$config['email']['smtp_user'] = 'outbound@opencitystreets.com';
$config['email']['smtp_pass'] = 'omgwtf';
$config['email']['smtp_port'] = '25';
$config['email']['mailtype']  = 'html';
$config['email']['charset']   = 'utf-8';
