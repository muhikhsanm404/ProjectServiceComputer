<?php
error_reporting(E_ALL);
defined('BASEPATH') or exit('No direct script access allowed');

class Kerusakan extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    cekLogin();
    $this->load->model('Kerusakan_model', 'kerusakan');
    $this->load->library('form_validation');
  }

  // Halaman Kerusakan
  public function index()
  {
    $data['judul'] = "Halaman Kerusakan";
    $data['user'] = $this->db->get_where('tbl_user', [
      'username' => $this->session->userdata('username')
    ])->row_array();
    $data['tbl_kerusakan'] = $this->kerusakan->getAllKerusakan();
    $data['kode'] = $this->kerusakan->KodeKerusakan();

    $this->load->view('templates/Admin_header', $data);
    $this->load->view('templates/Admin_sidebar', $data);
    $this->load->view('templates/Admin_topbar');
    $this->load->view('admin/kerusakan/index', $data);
    $this->load->view('templates/Admin_footer');
    $this->load->view('admin/kerusakan/modal_tambah_kerusakan', $data);
    $this->load->view('admin/kerusakan/modal_ubah_kerusakan');
  }

  // Tambah Kerusakan
  public function tambah()
  {
    $data['tbl_kerusakan'] = $this->db->get('tbl_kerusakan')->result_array();
    $data['user'] = $this->db->get_where('tbl_user', [
      'username' => $this->session->userdata('username')
    ])->row_array();

    // cek jika ada gambar yang akan diupload
    $upload_image = $_FILES['gambar']['name'];
    if ($upload_image) {
      $config['allowed_types'] = 'jpg|png';
      $config['max_size']      = '4096';
      $config['upload_path'] = './assets/images/kerusakan/';

      $this->load->library('upload', $config);
      if ($this->upload->do_upload('gambar')) {
        // $old_image = $data['tbl_kerusakan']['gambar'];
        // if ($old_image != 'user.png') {
        //   unlink(FCPATH . '/assets/images/kerusakan/' . $old_image);
        // }
        $new_image = $this->upload->data('file_name');
        $this->db->set('gambar', $new_image);
        // } else {
        //   echo $this->upload->dispay_errors();
        // }
      }
      $this->kerusakan->tambahKerusakan();
      $this->session->set_flashdata('pesan', '<div class="alert alert-success" role="alert">Data Kerusakan Berhasil ditambahkan!</div>'); //buat pesan akun berhasil dibuat
      redirect('kerusakan');
    }
  }

  // Ubah Kerusakan
  public function ubahkerusakan()
  {
    try {
      $data['user'] = $this->db->get_where('tbl_user', ['username' => $this->session->userdata('username')])->row_array();

      // Mendapatkan ID dari form
      $id = $this->input->post('id');

      // Memeriksa apakah ada gambar yang akan diupload
      if (!empty($_FILES['gambar']['name'])) {
        $upload_image = $_FILES['gambar']['name'];

        $config['allowed_types'] = 'jpg|png';
        $config['max_size'] = '4096';
        $config['upload_path'] = './assets/images/kerusakan/';

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('gambar')) {
          $new_image = $this->upload->data('file_name');

          // Mengupdate data, termasuk gambar, pada tabel 'tbl_kerusakan'
          $data['tbl_kerusakan'] = [
            'gambar' => $new_image, // Mengubah gambar
            'nama_kerusakan' => $this->input->post('nama'),
            'solusi' => $this->input->post('solusi'),
            'probabilitas' => $this->input->post('probabilitas')
            // Kolom-kolom lain yang akan diubah sesuai kebutuhan
          ];

          // Melakukan update pada ID yang sesuai
          $this->db->where('id_kerusakan', $id);
          $this->db->update('tbl_kerusakan', $data['tbl_kerusakan']);

          $this->session->set_flashdata('message', 'Data kerusakan berhasil diubah');
          redirect('kerusakan');
        } else {
          $this->session->set_flashdata('error', $this->upload->display_errors());
          redirect('kerusakan/ubah/' . $id);
        }
      } else {
        // Tidak ada gambar yang diupload
        // Lakukan proses update data lainnya tanpa gambar

        $data['tbl_kerusakan'] = [
          'nama_kerusakan' => $this->input->post('nama'),
          'solusi' => $this->input->post('solusi'),
          'probabilitas' => $this->input->post('probabilitas')
          // Kolom-kolom lain yang akan diubah sesuai kebutuhan
        ];

        // Melakukan update pada ID yang sesuai
        $this->db->where('id_kerusakan', $id);
        $this->db->update('tbl_kerusakan', $data['tbl_kerusakan']);

        $this->session->set_flashdata('pesan', '<div class="alert alert-success" role="alert">Data kerusakan berhasil diubah</div>');
        redirect('kerusakan');
      }
    } catch (Exception $e) {
      log_message('error', $e->getMessage());
      $this->session->set_flashdata('pesan', '<div class="alert alert-danger" role="alert">Terjadi kesalahan saat mengubah data kerusakan.</div>');
      redirect('kerusakan');
    }
  }

  //Hapus
  public function hapus($id)
  {
    $this->kerusakan->hapusKerusakan($id);
    $this->session->set_flashdata('pesan', '<div class="alert alert-danger" role="alert">Data Kerusakan Berhasil dihapus!</div>'); //buat pesan akun berhasil dibuat
    redirect('kerusakan');
  }
}
