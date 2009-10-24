<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Open City Streets logging 
 *
 * basically just a wrapper for CI's log_message in Common.php at this point
 * option to add some backtrace info
 *
 * 10/23/09 - Initial version - AAW
 *
 */

class ocs_logging 
{

	protected $ci;
	
  
	function __construct() 
  	{
		$this->ci =& get_instance();
  		log_message('debug','ocs_logging initialized');
  	}


  	public function log_message($level = 'error',$message)
  	{
		//  wrap CI's own log_message with option to incude backtrace info 
		//  in the future we could do better things

  		if ($this->ci->config->item('log_backtrace','ocs'))
		{
			list($file, $line, $func, $class) = $this->_getBacktraceVars(1);
			$message = "$class/$func: $message at line $line of $file";
		}

		log_message($level,$message);
  	}



  	function _getBacktraceVars($depth)
    	{
		// From http://pear.php.net/package-info.php?package=Log
		// modified slightly to work here

        	/* Start by generating a backtrace from the current call (here). */
        	$bt = debug_backtrace();

        	$class = isset($bt[$depth+1]['class']) ? $bt[$depth+1]['class'] : null;

       	 	/*
         	* We're interested in the frame which invoked the log() function, so
         	* we need to walk back some number of frames into the backtrace.  The
         	* $depth parameter tells us where to start looking.   We go one step
         	* further back to find the name of the encapsulating function from
         	* which log() was called.
         	*/
        	$file = isset($bt[$depth])     ? $bt[$depth]['file'] : null;
        	$line = isset($bt[$depth])     ? $bt[$depth]['line'] : 0;
        	$func = isset($bt[$depth + 1]) ? $bt[$depth + 1]['function'] : null;


        	/*
         	* If we couldn't extract a function name (perhaps because we were
         	* executed from the "main" context), provide a default value.
         	*/
        	if (is_null($func)) {
            		$func = '(none)';
        	}	

        	/* Return a 4-tuple containing (file, line, function, class). */
        	return array($file, $line, $func, $class);
    	}


}
