<?php

defined('BASEPATH') or exit('No direct script access allowed');



class Produk_model extends CI_Model
{



	public function __construct()
	{

		parent::__construct();

		$this->load->model('Main_model');

		$this->username = $this->session->userdata('username');
	}



	public function save_produk($params)

	{

		$this->db->trans_start();

		$mode = 'edit';



		if (empty($params->kode)) {

			$mode = 'add';

			$params->kode = 'PRD' . $this->Main_model->generate_kode_master('produk');
		}



		$this->db->set('nama', $params->nama);
		$this->db->set('harga_m', $params->harga_m);
		$this->db->set('harga_l', $params->harga_l);
		$this->db->set('harga_xl', $params->harga_xl);
		$this->db->set('harga_xxl', $params->harga_xxl);
		$this->db->set('harga_xxxl', $params->harga_xxxl);
		$this->db->set('harga_all_l', $params->harga_all_l);
		$this->db->set('harga_all', $params->harga_all);

		if ($mode == 'add') {

			$this->db->set('kode', $params->kode);

			$this->db->set('created_at', date('Y-m-d H:i:s'));

			$this->db->set('created_by', $this->username);

			$this->db->insert('produk');
		} else {

			$this->db->set('updated_at', date('Y-m-d H:i:s'));

			$this->db->set('updated_by', $this->username);

			$this->db->where('kode', $params->kode);

			$this->db->update('produk');
		}



		$noline = 0;

		foreach ($params->noline as $key => $data) {

			$noline++;

			$this->db->set('gambar', $params->gambar[$key]);
			$this->db->set('model', $params->model[$key]);
			$this->db->set('size_m', $params->size_m[$key]);
			$this->db->set('size_l', $params->size_l[$key]);
			$this->db->set('size_xl', $params->size_xl[$key]);
			$this->db->set('size_xxl', $params->size_xxl[$key]);
			$this->db->set('size_xxxl', $params->size_xxxl[$key]);
			$this->db->set('size_all_l', $params->size_all_l[$key]);
			$this->db->set('size_all', $params->size_all[$key]);

			if (empty($params->noline[$key])) {

				$this->db->set('kode', $params->kode);

				$this->db->set('noline', $noline);

				$this->db->insert('produk_stok');
			} else {

				$this->db->where('kode', $params->kode);

				$this->db->where('noline', $params->noline[$key]);

				$this->db->update('produk_stok');
			}
		}



		$this->db->trans_complete();

		return $this->db->trans_status();
	}



	public function remove_produk($params)

	{

		$this->db->trans_start();



		$this->Main_model->remove('produk_stok', 'kode', $params->kode);

		$this->Main_model->remove('produk', 'kode', $params->kode);



		$this->db->trans_complete();

		return $this->db->trans_status();
	}
}
