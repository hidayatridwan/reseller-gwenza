<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->username = $this->session->userdata('username');
		$this->load->model('Main_model');
	}

	public function save_user($params)
	{
		$valid = true;

		$row = $this->Main_model->get_where('user', 'username', $params->username);
		$mode = 'add';
		if ($row->num_rows() > 0) {
			$mode = 'edit';
		}

		if (!empty($params->password)) {
			$this->db->set('password', password_hash($params->password, PASSWORD_DEFAULT));
		}
		$this->db->set('role', $params->role);
		$this->db->set('kode_reseller', $params->kode_reseller);
		if ($mode == 'add') {
			$this->db->set('username', $params->username);
			$this->db->set('created_at', date('Y-m-d H:i:s'));
			$this->db->set('created_by', $this->username);
			$valid = $this->db->insert('user');
		} else {
			$this->db->set('updated_at', date('Y-m-d H:i:s'));
			$this->db->set('updated_by', $this->username);
			$this->db->where('username', $params->username);
			$valid = $this->db->update('user');
		}
		return $valid;
	}

	public function proses_login($params)
	{
		$row = $this->Main_model->get_where('user', 'username', $params->username);
		if ($row->num_rows() > 0) {
			$data = $row->row();
			$valid = password_verify($params->password, $data->password);
			if ($valid) {
				$nama = $this->Main_model->get_where('reseller', 'kode', $data->kode_reseller);
				if ($nama->num_rows() > 0) {
					$nama = $nama->row()->nama;
				} else {
					$nama = 'Admin';
				}
				$session = array(
					'username' => $data->username,
					'role' => $data->role,
					'nama' => $nama,
					'kode_reseller' => $data->kode_reseller,
					'logged' => true
				);
				$this->session->set_userdata($session);
				return ['status' => true];
			} else {
				return ['status' => false, 'message' => 'Password was wrong.'];
			}
		} else {
			return ['status' => false, 'message' => 'Username not found.'];
		}
	}
}
