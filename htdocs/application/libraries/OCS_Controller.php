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
*  10/23/09 - Extends base Controller to check for logged in - AAW
*/



class OCS_Controller extends Controller
{
	
    public function __construct()
    {
    	
        parent::__construct();
        
        if ($this->ocs_auth->logged_in() === false)   
        {
            redirect('auth/login/notloggedin');
        }
        
        
    }
}

?>
