<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Client;
use App\Models\MetodePembayaran;
use App\Models\Sampah;
use App\Models\Transaksi;

class PenjualanController extends BaseController
{
    protected $sampahModel;
    protected $metodeBayarModel;
    protected $klienModel;
    protected $transaksiModel;

    public function __construct()
    {
        $this->sampahModel = new Sampah();
        $this->metodeBayarModel = new MetodePembayaran();
        $this->klienModel = new Client();
        $this->transaksiModel = new Transaksi();
    }

    public function index()
    {
        $data = [
            "title" => "Data Penjualan",
            "data" => $this->transaksiModel->getPenjualan(session('clientId'), 'in'),
        ];

        return view('penjualan/index', $data);
    }

    public function create()
    {
        $data = [
            "title" => "Tambah Data Penjualan",
            "sampah" => $this->sampahModel->where('client_id', session('clientId'))->findAll(),
            "bayar" => $this->metodeBayarModel->where('client_id', session('clientId'))->findAll(),
            "klien" => $this->klienModel->where('client_id', session('clientId'))->findAll(),
        ];

        return view('penjualan/create', $data);
    }

    public function edit($id)
    {
        $data = [
            "title" => "Edit Data Penjualan",
            "sampah" => $this->sampahModel->where('client_id', session('clientId'))->findAll(),
            "bayar" => $this->metodeBayarModel->where('client_id', session('clientId'))->findAll(),
            "klien" => $this->klienModel->where('client_id', session('clientId'))->findAll(),
            "data" => $this->transaksiModel->find($id),
        ];

        return view('penjualan/edit', $data);
    }

    public function sampahAjax()
    {
        $id = $this->request->getPost('id');
        $data = $this->sampahModel->find($id);
        return $this->response->setJSON($data);
    }

    public function store()
    {
        $tanggal = $this->request->getPost('tanggal');
        $nama_sampah = $this->request->getPost('nama_sampah');
        $jumlah_jual = $this->request->getPost('jumlah_jual');
        $pembeli = $this->request->getPost('pembeli');
        $metode_bayar = $this->request->getPost('metode_bayar');
        $id = $this->request->getPost('id');
        $bukti_qris = $this->request->getFile('bukti_qris');

        $data = [
            'tanggal' => $tanggal,
            'sampah_id' => $nama_sampah,
            'jumlah' => $jumlah_jual,
            'pembeli' => $pembeli,
            'metode_bayar_id' => $metode_bayar,
        ];

        if ($bukti_qris && $bukti_qris->isValid() && !$bukti_qris->hasMoved()) {
            $filename = $bukti_qris->getRandomName();
            $data['bukti'] = $filename;

            $bukti_qris->move(ROOTPATH . 'public/bukti', $filename);
        }

        if ($id) {
            $data['id'] = $id;
            $text = 'diupdate';
        } else {
            $text = 'ditambahkan';
            $data['client_id'] = session('clientId');
            $data['jenis'] = 'in';
        }

        try {
            $this->transaksiModel->save($data);
            $message = [
                'title' => 'Success',
                'text' => 'Data berhasil ' . $text,
                'icon' => 'success'
            ];
            session()->setFlashdata($message);
            return redirect()->to('penjualan');
        } catch (\Throwable $th) {
            $message = [
                'title' => 'Error',
                'text' => 'Data gagal ' . $text,
                'icon' => 'error'
            ];
            session()->setFlashdata($message);
            return redirect()->back();
        }
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        try {
            $this->transaksiModel->delete($id);
            $response = ["title" => "Berhasil", "text" => "Data berhasil dihapus", "icon" => "success"];
        } catch (\Throwable $th) {
            $response = ["title" => "Gagal", "text" => "Data gagal dihapus", "icon" => "error"];
        }
        return $this->response->setJSON($response);
    }
}
