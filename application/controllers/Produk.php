<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Produk extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Main_model');
		$this->load->model('Produk_model');
		$this->logged = $this->session->userdata('logged');
		$this->content = [
			'base_url' => base_url(),
			'app_name' => APP_NAME,
			'auth' => $this->session->userdata(),
			'title' => 'Produk'
		];
		if (!$this->logged) {
			redirect('login');
		}
	}

	public function produk()
	{
		$this->twig->display('master/produk.html', $this->content);
	}

	public function data_produk()
	{
		$result = $this->Main_model->get_all('produk', 'desc');
		$data = [];
		$no = 0;
		foreach ($result as $v) {
			$no++;
			$row = [];
			$row[] = $no;
			$row[] = $v->kode;
			$row[] = $v->nama;
			$data_model = $this->Main_model->get_where('produk_stok', 'kode', $v->kode)->result();
			$model = '<table class="w-100">
				<thead>
				<tr>
				<th>Gambar</th>
				<th>Model</th>
				<th>M</th>
				<th>L</th>
				<th>XL</th>
				<th>XXL</th>
				<th>XXXL</th>
				<th>Dewasa (L)</th>
				<th>Dewasa (XL)</th>
				</tr><thead>';
			foreach ($data_model as $m) {
				$model .= '<tr>
					<td><img src="' . base_url() . 'uploads/produk/' . $m->gambar . '" alt="" style="height: 80px;"/></td>
					<td>' . $m->model . '</td>
					<td>' . $m->size_m . '</td>
					<td>' . $m->size_l . '</td>
					<td>' . $m->size_xl . '</td>
					<td>' . $m->size_xxl . '</td>
					<td>' . $m->size_xxxl . '</td>
					<td>' . $m->size_all_l . '</td>
					<td>' . $m->size_all . '</td>
					</tr>';
			}
			$model .= '</table>';
			$row[] = $model;
			$row[] = $v->harga_m;
			$row[] = $v->harga_l;
			$row[] = $v->harga_xl;
			$row[] = $v->harga_xxl;
			$row[] = $v->harga_xxxl;
			$row[] = $v->harga_all_l;
			$row[] = $v->harga_all;
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

	public function save_produk()
	{
		$params = (object)$this->input->post();

		// create directory
		$path = getcwd() . '/uploads/produk/';
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		// upload gambar
		foreach ($params->gambar_hidden as $idx => $data) {
			if (!empty($_FILES['gambar']['name'][$idx])) {
				if (!empty($params->gambar_hidden[$idx])) {
					$file_exist = $path . $params->gambar_hidden[$idx];
					if (file_exists($file_exist)) {
						unlink($file_exist);
					}
				}
				$file_name = $_FILES['gambar']['name'][$idx];
				$name = implode('.', explode('.', $file_name, -1));
				$ext = pathinfo($file_name, PATHINFO_EXTENSION);
				$params->gambar[$idx] = $name . date('_Ymd_His') . '.' . $ext;
				move_uploaded_file($_FILES['gambar']['tmp_name'][$idx], $path . $params->gambar[$idx]);
			} else {
				$params->gambar[$idx]  = $params->gambar_hidden[$idx];
			}
		}

		$result = $this->Produk_model->save_produk($params);
		echo json_encode($result);
	}

	public function remove_produk()
	{
		$params = (object)$this->input->post();

		// directory
		$path = getcwd() . '/uploads/produk/';
		// remove gambar
		$data = $this->Main_model->get_where('produk_stok', 'kode', $params->kode)->result();
		foreach ($data as $row) {
			$file_exist = $path . $row->gambar;
			if (file_exists($file_exist)) {
				unlink($file_exist);
			}
		}
		$result = $this->Produk_model->remove_produk($params);
		echo json_encode($result);
	}
}
