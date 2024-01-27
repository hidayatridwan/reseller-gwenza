<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Main_model');
		$this->logged = $this->session->userdata('logged');
		$this->content = [
			'base_url' => base_url(),
			'app_name' => APP_NAME,
			'auth' => $this->session->userdata(),
			'title' => 'Main Page'
		];
		if (!$this->logged) {
			redirect('login');
		}
	}

	public function index()
	{
		$this->twig->display('main.html', $this->content);
	}

	public function get_data()
	{
		$table = $this->input->post('table');
		$result = $this->Main_model->get_data($table);
		echo json_encode($result);
	}

	public function get_data_one()
	{
		$table = $this->input->post('table');
		$col = $this->input->post('col');
		$val = $this->input->post('val');
		$result = $this->Main_model->get_where($table, $col, $val)->row();
		echo json_encode($result);
	}

	public function get_produk_one()
	{
		$kode = $this->input->post('kode');
		$row = $this->Main_model->get_where('produk', 'kode', $kode)->row();
		$result = [];
		$result['kode'] = $row->kode;
		$result['nama'] = $row->nama;
		$result['harga_m'] = $row->harga_m;
		$result['harga_l'] = $row->harga_l;
		$result['harga_xl'] = $row->harga_xl;
		$result['harga_xxl'] = $row->harga_xxl;
		$result['harga_xxxl'] = $row->harga_xxxl;
		$result['harga_all_l'] = $row->harga_all_l;
		$result['harga_all'] = $row->harga_all;
		$result['model'] = $this->Main_model->get_where('produk_stok', 'kode', $kode)->result();
		echo json_encode($result);
	}
}
