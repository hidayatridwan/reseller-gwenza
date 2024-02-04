<?php

defined('BASEPATH') or exit('No direct script access allowed');



class Booking_model extends CI_Model
{



	public function __construct()
	{

		parent::__construct();

		$this->username = $this->session->userdata('username');

		$this->role = $this->session->userdata('role');
	}



	public function save_booking($params)

	{

		$this->db->trans_start();



		$params->kode = $this->Main_model->generate_kode_trans('booking');

		$params->kode_reseller = $this->Main_model->get_where('user', 'username', $this->username)->row()->kode_reseller;

		if (is_null($params->kode_reseller)) {

			return ['status' => false, 'message' => 'Nama reseller tidak ditemukan'];

			die;
		}



		$this->db->set('kode', $params->kode);

		$this->db->set('tgl_trans', date('Y-m-d'));

		$this->db->set('kode_reseller', $params->kode_reseller);

		$nama_reseller = $this->Main_model->get_where('reseller', 'kode', $params->kode_reseller)->row()->nama;

		$this->db->set('nama_reseller', $nama_reseller);

		$this->db->set('nama', $params->nama_customer);

		$this->db->set('alamat', $params->alamat);

		$this->db->set('nohp', $params->nohp);

		$this->db->set('kurir', $params->kurir);

		$this->db->set('keterangan', $params->keterangan);

		$this->db->set('status', 'Pending');

		$this->db->set('created_at', date('Y-m-d H:i:s'));

		$this->db->set('created_by', $this->username);

		$this->db->insert('booking');



		$grand_total = 0;

		foreach ($params->noline as $key => $data) {

			$produk = $this->Main_model->get_where('produk', 'kode', $params->kode_produk[$key])->row();

			$produk_stok = $this->db->query("select * from produk_stok where kode = '" . $params->kode_produk[$key] . "' and noline = '" . $params->noline[$key] . "'")->row();

			if ($params->size[$key] == 'M') {

				$harga = $produk->harga_m;
			} else if ($params->size[$key] == 'L') {

				$harga = $produk->harga_l;
			} else if ($params->size[$key] == 'XL') {

				$harga = $produk->harga_xl;
			} else if ($params->size[$key] == 'XXL') {

				$harga = $produk->harga_xxl;
			} else if ($params->size[$key] == 'XXXL') {

				$harga = $produk->harga_xxxl;
			} else if ($params->size[$key] == 'DewasaL') {

				$harga = $produk->harga_all_l;
			} else if ($params->size[$key] == 'Dewasa') {

				$harga = $produk->harga_all;
			}

			$this->db->set('kode', $params->kode);

			$this->db->set('kode_produk', $params->kode_produk[$key]);

			$this->db->set('nama_produk', $produk->nama);

			$this->db->set('gambar', $produk_stok->gambar);

			$this->db->set('model', $produk_stok->model);

			$this->db->set('noline', $params->noline[$key]);

			$this->db->set('size', $params->size[$key]);

			$this->db->set('harga', $harga);

			if ($params->size[$key] == 'DewasaL') {

				$params->size[$key] = 'all_l';
			}

			if ($params->size[$key] == 'Dewasa') {

				$params->size[$key] = 'all';
			}

			$col_size = 'size_' . strtolower($params->size[$key]);

			if ((int)$params->qty_size[$key] > (int)$produk_stok->$col_size) {

				return ['status' => false, 'message' => 'Qty kurang dari stok untuk ' . $params->kode_produk[$key] . '~' . $params->size[$key]];

				die;
			}

			$this->db->set('qty_size', $params->qty_size[$key]);

			$total = (int)$params->qty_size[$key] * (float)$harga;

			$this->db->set('total', $total);

			$this->db->insert('booking_detail');

			$grand_total += $total;



			// update stok master produk

			$stok = $produk_stok->$col_size - $params->qty_size[$key];

			$this->db->set($col_size, $stok);

			$this->db->where('kode', $params->kode_produk[$key]);

			$this->db->where('noline', $params->noline[$key]);

			$this->db->update('produk_stok');
		}

		// update grand total

		$this->db->set('grand_total', $grand_total);

		$this->db->where('kode', $params->kode);

		$this->db->update('booking');



		$this->db->trans_complete();

		return ['status' => $this->db->trans_status(), 'message' => $this->db->trans_status() ? $params->kode : 'Failed to create booking'];
	}



	public function save_proses_kirim($params)

	{

		$this->db->trans_start();

		$this->db->set('kurir', $params->kurir);

		if ($this->role == 'Reseller') {

			if (!empty($params->bukti_trf)) {

				$this->db->set('bukti_trf', $params->bukti_trf);

				if ($params->status == 'Pending') {
					$this->db->set('status', 'Transfer');
				}

				$this->db->set('updated_at', date('Y-m-d H:i:s'));

				$this->db->set('updated_by', $this->username);
			}

			$this->db->where('kode', $params->kode);

			$this->db->update('booking');
		} else if ($this->role == 'Admin') {

			$this->db->set('noresi', $params->noresi);

			$this->db->set('status', $params->status);

			$this->db->set('updated_at', date('Y-m-d H:i:s'));

			$this->db->set('updated_by', $this->username);

			// potongan

			$booking_detail = $this->Main_model->get_where('booking_detail', 'kode', $params->kode)->result();

			$total_potongan = 0;

			foreach ($booking_detail as $v) {

				$total_potongan += (int)$v->qty_size * (float)$params->harga_potongan;
			}

			$this->db->set('harga_potongan', $params->harga_potongan);

			$this->db->set('total_potongan', $total_potongan);

			$this->db->where('kode', $params->kode);

			$this->db->update('booking');
		}

		return $this->db->trans_complete();
	}



	public function batal_booking($params)

	{

		$this->db->trans_start();

		/*
		// update stok

		$booking_detail = $this->Main_model->get_where('booking_detail', 'kode', $params->kode)->result();

		foreach ($booking_detail as $v) {

			if ($v->size == 'M') {

				$this->db->set('size_m', 'size_m + ' . $v->qty_size, false);
			} else if ($v->size == 'L') {

				$this->db->set('size_l', 'size_l + ' . $v->qty_size, false);
			} else if ($v->size == 'XL') {

				$this->db->set('size_xl', 'size_xl + ' . $v->qty_size, false);
			} else if ($v->size == 'XXL') {

				$this->db->set('size_XXL', 'size_xxl + ' . $v->qty_size, false);
			} else if ($v->size == 'XXXL') {

				$this->db->set('size_XXXL', 'size_xxxl + ' . $v->qty_size, false);
			} else if ($v->size == 'DewasaL') {

				$this->db->set('size_all_l', 'size_all_l + ' . $v->qty_size, false);
			} else if ($v->size == 'Dewasa') {

				$this->db->set('size_all', 'size_all + ' . $v->qty_size, false);
			}

			$this->db->where('kode', $v->kode_produk);

			$this->db->where('noline', $v->noline);

			$valid = $this->db->update('produk_stok');
		}
*/
		// update status

		$this->db->set('status', $params->status);

		if ($this->role == 'Admin') {

			$this->db->set('updated_at', date('Y-m-d H:i:s'));

			$this->db->set('updated_by', $this->username);
		}

		$this->db->where('kode', $params->kode);

		$this->db->update('booking');



		$this->db->trans_complete();

		return $this->db->trans_status();
	}


	public function data_booking_detail($opt = null)

	{
		$where = "";
		if (!is_null($opt)) {
			$where .= $opt;
		}
		return $this->db->query("select t1.kode_produk,

t1.nama_produk,

t1.model,

t1.size,

t1.harga,

t1.qty_size,

t1.total,

t2.* from booking_detail as t1

join booking as t2 on t1.kode=t2.kode
where 1=1
$where
order by t2.created_at asc;");
	}

	public function produk_in_stock()
	{
		return $this->db->query("SELECT
			*
		FROM
			gwenza.produk
		WHERE
			kode IN (SELECT
					kode
				FROM
					produk_stok
				WHERE
					size_m > 0 OR size_l > 0 OR size_xl > 0
						OR size_xxl > 0
						OR size_xxxl > 0
						OR size_all > 0
						OR size_all_l > 0)
		ORDER BY created_at DESC;")->result();
	}
}
