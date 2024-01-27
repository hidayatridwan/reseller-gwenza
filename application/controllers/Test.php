<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Test extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
	    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
	    header('Access-Control-Allow-Origin: *');
	    header('Access-Control-Allow-Headers: *');
	    $token = isset($headers['x-api-key']) ?? null;
	    if ($token != 'ridwan123') {
	        echo json_encode(array('key'=>'error'));
	        exit;
	    }
		echo json_encode(array('key'=>'value2'));
	}
}