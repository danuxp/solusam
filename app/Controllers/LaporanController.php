<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Transaksi;

class LaporanController extends BaseController
{
    protected $transaksiModel;

    public function __construct()
    {
        $this->transaksiModel = new Transaksi();
    }

    public function index()
    {
        $data = [
            "title" => "Data Laporan",
        ];

        return view('laporan/index', $data);
    }

    public function getLaporanData()
    {
        $tahun = $this->request->getPost('tahun');
        $bulan = $this->request->getPost('bulan');
        $tanggalMulai = $this->request->getPost('tanggal_mulai');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');
        $client_id = session()->get('clientId');

        $laporanData = $this->transaksiModel->getLaporan($client_id, $tahun, $bulan, $tanggalMulai, $tanggalSelesai);

        return $this->response->setJSON($laporanData);
    }

    public function pemasukan()
    {
        $data = [
            "title" => "Data Laporan Pemasukan",
        ];

        return view('laporan/pemasukan', $data);
    }
}
