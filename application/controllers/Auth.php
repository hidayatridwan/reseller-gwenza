<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Main_model');
		$this->load->model('Auth_model');
		$this->logged = $this->session->userdata('logged');
		$this->content = [
			'base_url' => base_url(),
			'app_name' => APP_NAME,
			'auth' => $this->session->userdata()
		];
	}

	public function login()
	{
		if (!$this->logged) {
			$this->content['title'] = 'Login';
			$this->twig->display('login.html', $this->content);
		} else {
			redirect('main');
		}
	}

	public function user_login()
	{
		if ($this->logged) {
			$this->content['title'] = 'User Login';
			$this->content['reseller'] = $this->Main_model->get_all('reseller');
			$this->twig->display('master/user_login.html', $this->content);
		} else {
			redirect('login');
		}
	}

	public function data_user()
	{
		$result = $this->Main_model->get_all('user');
		$data = [];
		$no = 0;
		foreach ($result as $v) {
			$no++;
			$row = [];
			$row[] = $no;
			$row[] = $v->username;
			$row[] = '*****';
			$row[] = $v->role;
			$row[] = $v->kode_reseller;
			$row[] = '<div class="btn-group">
			<button type="button" class="btn btn-xs btn-success btn-edit" data-kode="' . $v->username . '"><i class="fas fa-edit fa-xs"></i></button>
			<button type="button" class="btn btn-xs btn-danger btn-remove" data-kode="' . $v->username . '"><i class="far fa-trash-alt fa-xs"></i></button>
		  </div>';
			$data[] = $row;
		}
		$output = array(
			'data' => $data
		);
		echo json_encode($output);
	}

	public function save_user()
	{
		$params = (object)$this->input->post();
		$result = $this->Auth_model->save_user($params);
		echo json_encode($result);
	}

	public function proses_login()
	{
		$params = (object)$this->input->post();
		$result = $this->Auth_model->proses_login($params);
		echo json_encode($result);
	}

	public function logout()
	{
		session_destroy();
		redirect('login');
	}
}
