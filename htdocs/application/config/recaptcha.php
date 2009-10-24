<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 *  10/22/09 - Added to OCS project for use as initial captcha module - AAW
 */

$config['recaptcha'] = array(
  'public'=>'6LeG_ggAAAAAAIASey-PLzIoFc6vXm8iHdC8UEJi',
  'private'=>'6LeG_ggAAAAAAJd5iu9FeWfO14bKtAN_fsbuGYWm',
  'RECAPTCHA_API_SERVER' =>'http://api.recaptcha.net',
  'RECAPTCHA_API_SECURE_SERVER'=>'https://api-secure.recaptcha.net',
  'RECAPTCHA_VERIFY_SERVER' =>'api-verify.recaptcha.net',
  'theme' => 'white'
); 
?>
