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
*  Open City Streets game controller
*
*  10/23/09 - expect this to be replaced by an actual game at some point, this is just a place holder - AAW
*  
*/


class Game extends OCS_Controller {
	
	function welcome()
	{
	 	// for now, just show confirmation of login success
	 	
	 	$data['user_profile'] = $this->ocs_auth->profile();
	 	
	 	$data['content'] = $this->load->view('game/welcome', $data, true);
	 	$this->load->view('template', $data);
	}
}

