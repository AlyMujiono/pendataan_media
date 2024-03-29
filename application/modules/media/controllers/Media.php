<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Media extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		if (!$this->session->userdata('user_login')) {
			redirect('user/login');
			exit;
		}
		ce_active_menu('admin.media.view');

		$this->load->library('upload');
	}

	public function index()
	{
		ce_hak_akses('admin.media.view');

		$data['halaman'] = 'media';
		$data['javascript'] = array(
			'js/js_datatable' => array('ajax_url' => base_url('media/ajax_data'))
		);
		$data['header'] = 'Media<small>Index Data</small>';

		$this->load->view('template', $data);
	}

	public function tambah()
	{
		ce_hak_akses('admin.media.add');

		if ($this->input->method(TRUE) == 'POST') {
			$post_data['id'] = rand(100, 999) . date('dmYHis');
			$post_data['id_user'] = $this->session->userdata('id_user');
			$post_data['nama_media'] = $this->input->post('nama_media');
			$post_data['website'] = $this->input->post('website');
			$post_data['nama_perusahaan'] = $this->input->post('nama_perusahaan');
			$post_data['nik'] = $this->input->post('nik');
			$post_data['no_npwp'] = $this->input->post('no_npwp');
			$post_data['alamat'] = $this->input->post('alamat');
			$post_data['nama_pendaftar'] = $this->input->post('nama_pendaftar');
			$post_data['nib'] = $this->input->post('nib');
			$post_data['kbli'] = $this->input->post('kbli');
			$post_data['tgl_daftar'] = $this->input->post('tgl_daftar');
			if ($this->session->userdata('level') != 'member') {
				$post_data['verifikasi'] = abs((int)$this->input->post('verifikasi'));
				if ($post_data['verifikasi'] != 0)
					$post_data['tgl_verifikasi'] = date('Y-m-d');
				$post_data['status'] = abs((int)$this->input->post('status'));
			}

			if (!empty($_FILES['ktp']['tmp_name'])) {
				$config['upload_path']          = './assets/img/';
				$config['allowed_types']        = 'jpg|jpeg|png|gif';
				$config['encrypt_name']        	= true;
				$this->upload->initialize($config);
				if ($this->upload->do_upload('ktp')) {
					$fileData = $this->upload->data();
					$post_data['ktp'] = $fileData['file_name'];
				}
			}

			if ($this->media_m->media_insert_data($post_data)) {
				$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
				ce_set_msg('success', $success);
			} else {
				$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
				ce_set_msg('danger', $danger);
			}

			redirect('media');
		}

		$data['halaman'] = 'media_tambah';
		$data['header'] = 'Media <small>Tambah Data</small>';

		$this->load->view('template', $data);
	}

	public function edit($id)
	{
		ce_hak_akses('admin.media.update');

		$media = $this->media_m->media_by_id($id);
		if ($this->input->method(TRUE) == 'POST') {
			$post_data['id_user'] = $this->session->userdata('id_user');
			$post_data['nama_media'] = $this->input->post('nama_media');
			$post_data['no_telp'] = $this->input->post('no_telp');
			$post_data['website'] = $this->input->post('website');
			$post_data['nama_perusahaan'] = $this->input->post('nama_perusahaan');
			$post_data['nik'] = $this->input->post('nik');
			$post_data['no_npwp'] = $this->input->post('no_npwp');
			$post_data['alamat'] = $this->input->post('alamat');
			$post_data['nama_pendaftar'] = $this->input->post('nama_pendaftar');
			$post_data['nib'] = $this->input->post('nib');
			$post_data['kbli'] = $this->input->post('kbli');
			if ($this->session->userdata('level') != 'member') {
				$post_data['verifikasi'] = abs((int)$this->input->post('verifikasi'));
				if ($post_data['verifikasi'] != 0)
					$post_data['tgl_verifikasi'] = date('Y-m-d');
				$post_data['status'] = abs((int)$this->input->post('status'));
			}

			if (!empty($_FILES['ktp']['tmp_name'])) {
				$config['upload_path']          = './assets/img/';
				$config['allowed_types']        = 'jpg|jpeg|png|gif';
				$config['encrypt_name']        	= true;
				$this->upload->initialize($config);
				if ($this->upload->do_upload('ktp')) {
					$fileData = $this->upload->data();
					$post_data['ktp'] = $fileData['file_name'];
					unlink($config['upload_path'] . $media->ktp);
				}
			}

			if ($this->media_m->media_update_data($post_data, $id)) {
				if ($this->session->userdata('level') != 'member') {
					$user = $this->user_m->user_by_id($media->id_user);
					if ($post_data['verifikasi'] != 0) {
						$pesan = "Hallo *" . $user->username . "*,
Pengajuan berkas media anda telah *DITERIMA*. Jika masih ada kendala silakan hubungi admin kami di nomor whatsapp : *" . ce_opsi('telepon_perusahaan') . "*

Terima kasih";
					} else {
						$pesan = "Hallo *" . $user->username . "*,
Pengajuan berkas media anda telah *DITOLAK*. Mohon perbaiki Kembali, jika masih ada kendala, silakan hubungi admin kami 
di nomor whatsapp : *" . ce_opsi('telepon_perusahaan') . "*

Terima kasih";
					}
					kirim_pesan($user->no_telp, $pesan);
				}
				$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
				ce_set_msg('success', $success);
			} else {
				$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
				ce_set_msg('danger', $danger);
			}

			redirect('media');
		}

		$data['media'] = $media;
		$data['halaman'] = 'media_edit';
		$data['header'] = 'Media <small>Edit Data</small>';

		$this->load->view('template', $data);
	}

	public function syarat($id)
	{
		ce_hak_akses('admin.media.view');

		if ($this->input->method(TRUE) == 'POST') {
			$post_data['id_media'] = ((int)$id);
			$post_data['nama'] = $this->input->post('nama');

			if (!empty($_FILES['berkas']['tmp_name'])) {
				$config['upload_path']          = './assets/berkas/';
				$config['allowed_types']        = 'pdf';
				$config['encrypt_name']        	= true;
				$this->upload->initialize($config);
				if ($this->upload->do_upload('berkas')) {
					$fileData = $this->upload->data();
					$post_data['berkas'] = $fileData['file_name'];
				}
			}

			if ($this->media_m->media_insert_syarat($post_data)) {
				$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
				ce_set_msg('success', $success);
			} else {
				$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
				ce_set_msg('danger', $danger);
			}

			redirect('media/syarat/' . $id);
		}

		$data['id_media'] = $id;
		$data['syaratlist'] = $this->media_m->syarat_by_media($id);
		$data['halaman'] = 'syarat';
		$data['header'] = 'Media <small>Persyaratan</small>';

		$this->load->view('template', $data);
	}

	public function hapus_syarat($id_media, $id)
	{
		ce_hak_akses('admin.media.delete');

		$syarat = $this->db->get_where('syarat_media', ['id' => $id])->row();
		@unlink('./assets/berkas/' . $syarat->berkas);

		$this->media_m->syarat_delete_data($id);
		$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda pilih telah dihapus.';
		ce_set_msg('success', $success);

		redirect('media/syarat/' . $id_media);
	}

	public function hapus($id)
	{
		ce_hak_akses('admin.media.delete');

		$this->media_m->media_delete_data($id);
		$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda pilih telah dihapus.';
		ce_set_msg('success', $success);

		redirect('media');
	}

	public function ajax_data()
	{
		ce_hak_akses('admin.media.view');

		$dataConfig = array(
			'table' => 'media',
			'column_order' => array(null, 'nama_media', 'website', 'nama_perusahaan', null),
			'column_search' => array('nama_media', 'website', 'nama_perusahaan'),
			'order' => array('id' => 'asc')
		);
		if ($this->session->userdata('level') == 'member')
			$dataConfig['condition'][] = ['id_user' => $this->session->userdata('id_user')];

		$this->ajax_data_m->data_config($dataConfig);
		$list = $this->ajax_data_m->get_datatables();

		$data = array();
		$no = $this->input->post('start');
		foreach ($list as $field) {
			$no++;
			$row = array();
			$row[] = $no;
			$row[] = $field->nama_media;
			$row[] = $field->website;
			$row[] = $field->nama_perusahaan;
			$row[] = ce_boolean($field->verifikasi, '<span class="label label-danger">Tidak</span>|<span class="label label-success">Ya</span>');
			$row[] = ce_boolean($field->status, '<span class="label label-danger">Non-Aktif</span>|<span class="label label-success">Aktif</span>');
			$button = '<div class="btn-group pull-right">
								<button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown">
								Aksi <span class="caret"></span>
								</button>
								<ul class="dropdown-menu pull-right" role="menu">
									<li>' . ce_anchor('admin.media.view', 'media/syarat/' . $field->id, '<i class="fa fa-legal"></i>Persyaratan') . '</li>
									<li class="divider"></li>
									<li>' . ce_anchor('admin.media.update', 'media/edit/' . $field->id, '<i class="fa fa-edit"></i>Edit Data') . '</li>
									<li>' . ce_anchor('admin.media.delete', 'media/hapus/' . $field->id, '<i class="fa fa-trash"></i>Hapus Data', 'onclick="return delete_confirm();"') . '</li>
								</ul>
							</div>';
			$row[] = $button;

			$data[] = $row;
		}

		$output = array(
			"draw" => $this->input->post('draw'),
			"recordsTotal" => $this->ajax_data_m->count_all(),
			"recordsFiltered" => $this->ajax_data_m->count_filtered(),
			"data" => $data,
		);

		//output dalam format JSON
		header('Content-Type: application/json');
		echo json_encode($output);
	}
}
