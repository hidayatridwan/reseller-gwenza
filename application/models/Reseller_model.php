<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reseller_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Main_model');
		$this->username = $this->session->userdata('username');
	}

	public function save_reseller($params)
	{
		$valid = true;
		$mode = 'edit';

		if (empty($params->kode)) {
			$mode = 'add';
			$params->kode = 'RSL' . $this->Main_model->generate_kode_master('reseller');
		}

		$this->db->set('nama', $params->nama);
		$this->db->set('nohp', $params->nohp);
		$this->db->set('alamat', $params->alamat);
		$this->db->set('kota', $params->kota);
		if ($mode == 'add') {
			$this->db->set('kode', $params->kode);
			$this->db->set('created_at', date('Y-m-d H:i:s'));
			$this->db->set('created_by', $this->username);
			$valid = $this->db->insert('reseller');
		} else {
			$this->db->set('updated_at', date('Y-m-d H:i:s'));
			$this->db->set('updated_by', $this->username);
			$this->db->where('kode', $params->kode);
			$valid = $this->db->update('reseller');
		}

		return $valid;
	}

	public function remove_reseller($params)
	{
		$this->db->trans_start();

		$this->Main_model->remove('user', 'kode_reseller', $params->kode);
		$this->Main_model->remove('reseller', 'kode', $params->kode);

		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	public function save_register($params)
	{
		$this->db->trans_start();

		$cek = $this->Main_model->get_where('user', 'username', $params->username)->num_rows();
		if ($cek > 0) {
			return false;
			die;
		}
		$params->kode = 'RSL' . $this->Main_model->generate_kode_master('reseller');

		$this->db->set('kode', $params->kode);
		$this->db->set('nama', $params->nama);
		$this->db->set('nohp', $params->nohp);
		$this->db->set('alamat', $params->alamat);
		$this->db->set('kota', $params->kota);
		$this->db->set('created_at', date('Y-m-d H:i:s'));
		$this->db->set('created_by', $params->username);
		$this->db->insert('reseller');

		$this->db->set('username', $params->username);
		$this->db->set('password', password_hash($params->password, PASSWORD_DEFAULT));
		$this->db->set('role', 'Reseller');
		$this->db->set('kode_reseller', $params->kode);
		$this->db->set('created_at', date('Y-m-d H:i:s'));
		$this->db->set('created_by', $params->username);
		$this->db->insert('user');

		$this->db->trans_complete();
		return $this->db->trans_status();
	}
}
