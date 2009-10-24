<?php

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

            	$data['user_profile'] = $this->redux_auth->profile();

 	        $data['content'] = $this->load->view('game/welcome', $data, true);
		$this->load->view('template', $data);
	}
}

