<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

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
*
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
