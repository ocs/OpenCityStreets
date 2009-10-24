<?php

/*
 *  10/23/09 - Extend Controller to check for logged in - AAW
 */



class OCS_Controller extends Controller
{

    public function __construct()
    {

        parent::__construct();

	if ($this->redux_auth->logged_in() === false)   
        {
            redirect('auth/login/notloggedin');
        }


    }
}

?>
