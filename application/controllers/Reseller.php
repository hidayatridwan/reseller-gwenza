<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reseller extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Main_model');
		$this->load->model('Reseller_model');
		$this->logged = $this->session->userdata('logged');
		$this->content = [
			'base_url' => base_url(),
			'app_name' => APP_NAME,
			'auth' => $this->session->userdata(),
			'title' => 'Reseller'
		];
	}

	public function reseller()
	{
		if (!$this->logged) {
			redirect('login');
		}
		$this->twig->display('master/reseller.html', $this->content);
	}

	public function data_reseller()
	{
		$result = $this->Main_model->get_all('reseller');
		$data = [];
		$no = 0;
		foreach ($result as $v) {
			$no++;
			$row = [];
			$row[] = $no;
			$row[] = $v->kode;
			$row[] = $v->nama;
			$row[] = $v->nohp;
			$row[] = $v->alamat;
			$row[] = $v->kota;
			$row[] = '<div class="btn-group">
				<button type="button" class="btn btn-xs btn-success btn-edit" data-kode="' . $v->kode . '"><i class="fas fa-edit fa-xs"></i></button>
				<button type="button" class="btn btn-xs btn-danger btn-remove" data-kode="' . $v->kode . '"><i class="far fa-trash-alt fa-xs"></i></button>
			  </div>';
			$data[] = $row;
		}
		$output = array(
			'data' => $data
		);
		echo json_encode($output);
	}

	public function save_reseller()
	{
		$params = (object)$this->input->post();
		$result = $this->Reseller_model->save_reseller($params);
		echo json_encode($result);
	}

	public function remove_reseller()
	{
		$params = (object)$this->input->post();
		$result = $this->Reseller_model->remove_reseller($params);
		echo json_encode($result);
	}

	public function register()
	{
		$this->twig->display('register.html', $this->content);
	}

	public function save_register()
	{
		$params = (object)$this->input->post();
		$result = $this->Reseller_model->save_register($params);
		echo json_encode($result);
	}
}
