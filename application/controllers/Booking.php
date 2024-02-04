<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Booking extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Main_model');
		$this->load->model('Booking_model');
		$this->logged = $this->session->userdata('logged');
		$this->role = $this->session->userdata('role');
		$this->kode_reseller = $this->session->userdata('kode_reseller');
		$this->content = [
			'base_url' => base_url(),
			'app_name' => APP_NAME,
			'auth' => $this->session->userdata()
		];
		if (!$this->logged) {
			redirect('login');
		}
	}

	public function booking_stok()
	{
		$this->content['title'] = 'Booking Stok';
		$this->twig->display('booking/booking_stok.html', $this->content);
	}

	public function get_produk()
	{
		$result = $this->Booking_model->produk_in_stock();
		$data = [];
		$idx = 0;
		foreach ($result as $v) {
			$row = [];
			$data_model = $this->Main_model->get_where('produk_stok', 'kode', $v->kode)->result();
			$model = '<ul class="list-group">';
			$gambar_main = "";
			foreach ($data_model as $key => $m) {
				if ($key == 0) {
					$gambar_main = $m->gambar;
				}
				$model .= '<li class="list-group-item d-flex justify-content-between align-items-center" style="padding: .25rem 1rem;">
				<div class="gambar-thumb" data-idx="' . $idx . '"><img src="' . base_url() . 'uploads/produk/' . $m->gambar . '" alt="" style="height: 50px;"/></div>

				<div class="row ml-2">
				<div class="col-sm-12">
				<span class="badge bg-primary">M ' . $m->size_m . '</span>
				<span class="badge bg-success">L ' . $m->size_l . '</span> 
				<span class="badge bg-warning">XL ' . $m->size_xl . '</span>
				</div>
				<div class="col-sm-12">
				<span class="badge bg-danger">XXL ' . $m->size_xxl . '</span>
				<span class="badge bg-purple">XXXL ' . $m->size_xxxl . '</span>
				<span class="badge bg-info">Dewasa (L) ' . $m->size_all_l . '</span>
				<span class="badge bg-info">Dewasa (XL)' . $m->size_all . '</span>
				</div>
				</div>

				</li>';
			}
			$model .= '</ul>';
			$row[] = '<div class="row"><div class="col-md-4"><img src="' . base_url() . 'uploads/produk/' . $gambar_main . '" alt="" class="w-100 gambar-main-' . $idx . '"/></div><div class="col-md-8"><h1>' . $v->nama . '</h1>' . $model . '<button type="button" class="btn btn-primary btn-sm btn-block mt-2 btn-order" data-kode="' . $v->kode . '">Order</button></div></div>';
			$data[] = $row;
			$idx++;
		}
		$output = array(
			'data' => $data
		);
		echo json_encode($output);
	}

	public function save_booking()
	{
		$params = (object)$this->input->post();
		$result = $this->Booking_model->save_booking($params);
		echo json_encode($result);
	}

	public function booking_data()
	{
		$this->content['title'] = 'Booking Data';
		$this->twig->display('booking/booking_data.html', $this->content);
	}

	public function data_booking()
	{
		if ($this->role == 'Admin') {
			$col = 1;
			$val = 1;
		} else {
			$col = 'kode_reseller';
			$val = $this->kode_reseller;
		}
		$opt = [
			'key' => 'between',
			'col' => 'tgl_trans',
			'date_start' => $this->input->post('date_start'),
			'date_end' => $this->input->post('date_end'),
			'order' => 'asc'
		];
		$result = $this->Main_model->get_where('booking', $col, $val, $opt)->result();
		$data = [];
		$no = 0;
		foreach ($result as $v) {
			$no++;
			$row = [];
			$row[] = $no;
			$row[] = $v->kode;
			$row[] = $v->tgl_trans;
			$row[] = $v->kode_reseller;
			$row[] = $v->nama_reseller;
			$row[] = $v->nama;
			$row[] = $v->alamat;
			$row[] = $v->nohp;
			$row[] = (float)$v->grand_total - (float)$v->total_potongan;
			$row[] = $v->kurir;
			$row[] = $v->noresi;
			$row[] = $v->keterangan;
			$row[] = $v->status;
			$btn_proses = '';
			if ($v->status == 'Pending') {
				if ($this->role == 'Admin') {
					// $btn_proses = '<button type="button" class="btn btn-xs btn-danger btn-reject" data-kode="' . $v->kode . '"><i class="far fa-trash-alt fa-xs"></i> Reject</button>';
				} else {
					$btn_proses = '<button type="button" class="btn btn-xs btn-warning btn-batal" data-kode="' . $v->kode . '"><i class="fas fa-window-close fa-xs"></i> Batal</button>';
				}
			}
			$row[] = '<div class="btn-group">
				<button type="button" class="btn btn-xs btn-success btn-edit" data-kode="' . $v->kode . '"><i class="fas fa-edit fa-xs"></i> View</button>
				' . $btn_proses . '
			  </div>';
			$data[] = $row;
		}
		$output = array(
			'data' => $data
		);
		echo json_encode($output);
	}

	public function get_booking_one()
	{
		$kode = $this->input->post('kode');
		$row = $this->Main_model->get_where('booking', 'kode', $kode)->row();
		$result = [];
		$result['kode'] = $row->kode;
		$result['tgl_trans'] = $row->tgl_trans;
		$result['kode_reseller'] = $row->kode_reseller;
		$result['nama_reseller'] = $row->nama_reseller;
		$result['nama'] = $row->nama;
		$result['alamat'] = $row->alamat;
		$result['nohp'] = $row->nohp;
		$result['kurir'] = $row->kurir;
		$result['noresi'] = $row->noresi;
		$result['grand_total'] = $row->grand_total;
		$result['harga_potongan'] = $row->harga_potongan;
		$result['keterangan'] = $row->keterangan;
		$result['bukti_trf'] = $row->bukti_trf;
		$result['status'] = $row->status;
		$result['detail'] = $this->Main_model->get_where('booking_detail', 'kode', $kode)->result();
		echo json_encode($result);
	}

	public function save_proses_kirim()
	{
		$params = (object)$this->input->post();
		// create directory
		$path = getcwd() . '/uploads/transfer/';
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		// upload bukti
		if ($_FILES['bukti_trf']['size'] > 0) {
			if (!empty($params->bukti_trf_hidden)) {
				$file_exist = $path . $params->bukti_trf_hidden;
				if (file_exists($file_exist)) {
					unlink($file_exist);
				}
			}
			$file_name = $_FILES['bukti_trf']['name'];
			$name = implode('.', explode('.', $file_name, -1));
			$ext = pathinfo($file_name, PATHINFO_EXTENSION);
			$params->bukti_trf = $name . date('_Ymd_His') . '.' . $ext;
			move_uploaded_file($_FILES['bukti_trf']['tmp_name'], $path . $params->bukti_trf);
		} else {
			$params->bukti_trf  = $params->bukti_trf_hidden;
		}
		$result = $this->Booking_model->save_proses_kirim($params);
		echo json_encode($result);
	}

	public function batal_booking()
	{
		$params = (object)$this->input->post();
		$result = $this->Booking_model->batal_booking($params);
		echo json_encode($result);
	}

	public function booking_data_detail()
	{
		$this->content['title'] = 'Laporan PO';
		$this->twig->display('booking/booking_data_detail.html', $this->content);
	}

	public function data_booking_detail()
	{
		$opt = "and tgl_trans between '" . $this->input->post('date_start') . "' and '" . $this->input->post('date_end') . "'";
		$result = $this->Booking_model->data_booking_detail($opt)->result();
		$data = [];
		$no = 0;
		foreach ($result as $v) {
			$no++;
			$row = [];
			$row[] = $no;
			$row[] = $v->kode;
			$row[] = $v->tgl_trans;
			$row[] = $v->kode_reseller;
			$row[] = $v->nama_reseller;
			$row[] = $v->nama;
			$row[] = $v->alamat;
			$row[] = $v->nohp;
			$row[] = (float)$v->grand_total - (float)$v->total_potongan;
			$row[] = $v->kurir;
			$row[] = $v->noresi;
			$row[] = $v->keterangan;
			$row[] = $v->status;
			$row[] = $v->kode_produk;
			$row[] = $v->nama_produk;
			$row[] = $v->model;
			$size = $v->size;
			if ($v->size == "Dewasa") $size = "Dewasa (XL)";
			if ($v->size == "DewasaL") $size = "Dewasa (L)";
			$row[] = $size;
			$row[] = $v->harga;
			$row[] = $v->qty_size;
			$row[] = $v->total;
			$data[] = $row;
		}
		$output = array(
			'data' => $data
		);
		echo json_encode($output);
	}
}
