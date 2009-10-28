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
* 10/23/09 - Initial version - AAW
* 10/27/09 - Moved to replace CI's Log.php directly
*/

class OCS_Log
{

	var $log_path;
	var $_threshold	= 1;
	var $_date_fmt	= 'Y-m-d H:i:s';
	var $_enabled	= TRUE;
	var $_levels	= array('ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4');
	// CI does debug and info back asswards from unix.. guess its best to correct it here?
	var $_syslog_levels = array('ERROR' => LOG_ERR, 'DEBUG' => LOG_INFO, 'INFO' => LOG_DEBUG);
	var $_backtrace_func = FALSE;
	var $_backtrace_line = FALSE;
	var $_use_syslog = FALSE;
	var $_syslog_facility = LOG_LOCAL0;
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	 
	 
	function OCS_Log()
	{
		// adapted from CI's Log.php
		
		$config =& get_config();
		
		if (is_numeric($config['log_threshold']))
		{
			$this->_threshold = $config['log_threshold'];
		}
		
		if ($config['log_date_format'] != '')
		{
			$this->_date_fmt = $config['log_date_format'];
		}

		if (isset($config['log_backtrace_func']))
		{
			$this->_backtrace_func = $config['log_backtrace_func'];
		}

		if (isset($config['log_backtrace_line']))
		{
			$this->_backtrace_line = $config['log_backtrace_line'];
		}		

		if (isset($config['log_use_syslog']))
		{
			$this->_use_syslog = $config['log_use_syslog'];
		}
		
		if (isset($config['log_syslog_facility']))
		{
			$this->_syslog_facility = $config['log_syslog_facility'];
		}
		
		if ($this->_use_syslog === TRUE)
		{
			if (!openlog("ocs", LOG_PID, $this->_syslog_facility))
			{
				$this->_enable = FALSE;
			}
		}
		else
		{
		
			$this->log_path = ($config['log_path'] != '') ? $config['log_path'] : BASEPATH.'logs/';
		
			if ( ! is_dir($this->log_path) OR ! is_really_writable($this->log_path))
			{
				$this->_enabled = FALSE;
			}
		}
		
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * Adapted from CI's Log.php for OCS
	 *  can add backtrace info and/or use syslog
	 *
	 * @access	public
	 * @param	string	the error level
	 * @param	string	the error message
	 * @param	bool	whether the error is a native PHP error
	 * @return	bool
	 */		
	function write_log($level = 'error', $msg, $php_error = FALSE)
	{		
		if ($this->_enabled === FALSE)
		{
			return FALSE;
		}
	
		$level = strtoupper($level);
		
		if ( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
		{
			return FALSE;
		}

		// create message
		
		$message = '';
		
		if ($this->_backtrace_func || $this->_backtrace_line)
		{
			list($file, $line, $func, $class) = $this->_getBacktraceVars(2);
		}
		
		if ($this->_backtrace_func)
		{
			$message .= $class .'/' . $func . ': ';
		}
				
		$message .= $msg;
		
		if ($this->_backtrace_line)
		{
			$message .= ' in ' . $file .' line ' . $line;
		}
		
		
		if ($this->_use_syslog)
		{
			// do syslog output
			
			$loglevel = $this->_syslog_levels[$level]; 
			syslog($loglevel,$message);
		}
		else
		{
			// do file output
		
			$message = $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' '.$message."\n";
		
			$filepath = $this->log_path.'log-'.date('Y-m-d').EXT;
			
			if ( ! file_exists($filepath))
			{
				$message .= "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
			}
			
			if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
			{
				return FALSE;
			}

			flock($fp, LOCK_EX);	
			fwrite($fp, $message);
			flock($fp, LOCK_UN);
			fclose($fp);
	
			@chmod($filepath, FILE_WRITE_MODE);
		}
		
		return TRUE;
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
