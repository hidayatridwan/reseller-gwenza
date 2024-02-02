<?php

defined('BASEPATH') or exit('No direct script access allowed');



class Main_model extends CI_Model
{



	public function __construct()
	{

		parent::__construct();
	}



	public function get_all($table, $opt = '', $limit = '')

	{

		if ($opt == 'desc') {
			$this->db->order_by('created_at', 'DESC');
		}
		if ($limit != '') {
			$this->db->limit((int)$limit);
		}

		return $this->db->get($table)->result();
	}



	public function get_where($table, $col, $val, $opt = null)

	{
		if ($opt != null) {
			if (is_array($opt)) {
				if ($opt['key'] == 'between') {
					$this->db->where($opt['col'] . " between '" . $opt['date_start'] . "' and '" . $opt['date_end'] . "'");
				}
				if ($opt['order'] == 'desc') {
					$this->db->order_by('created_at', 'DESC');
				}
			}
		}
		return $this->db->get_where($table, [$col => $val]);
	}



	public function remove($table, $col, $val)

	{

		$this->db->where($col, $val);

		return $this->db->delete($table);
	}



	public function generate_kode_master($table)
	{

		return $this->db->query("SELECT

		LPAD(IFNULL(MAX(CAST(SUBSTRING(kode, 4, 4) AS UNSIGNED)), 0) + 1, 4, 0) AS kode

		FROM `$table`;")->row()->kode;
	}



	public function generate_kode_trans($table)
	{

		return $this->db->query("SELECT

			CONCAT(

			RIGHT(YEAR(CURDATE()), 2),

			LPAD(MONTH(CURDATE()), 2, 0),

			LPAD(IFNULL(MAX(CAST(SUBSTRING(kode, 5, 4) AS UNSIGNED)), 0) + 1, 4, 0)

			) AS kode

		FROM `$table`

		WHERE SUBSTRING(kode, 1, 2) = RIGHT(YEAR(CURDATE()), 2)

			AND SUBSTRING(kode, 3, 2) = LPAD(MONTH(CURDATE()), 2, 0);")->row()->kode;
	}
}
