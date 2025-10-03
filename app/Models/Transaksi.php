<?php

namespace App\Models;

use CodeIgniter\Model;

class Transaksi extends Model
{
    protected $table            = 'transaksi';
    protected $allowedFields    = ['tanggal', 'sampah_id', 'jumlah', 'jenis', 'client_id', 'pembeli', 'metode_bayar_id', 'bukti'];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getPenjualan($client_id, $jenis)
    {
        $builder = $this->db->table('transaksi t');
        $builder->select('t.*, s.nama_sampah, s.harga_beli, s.harga_jual, s.satuan,
        m.nama as metode_bayar, c.nama_lengkap, c.no_telp, c.alamat, c.jenis_usaha');
        $builder->join('data_sampah s', 's.id = t.sampah_id');
        $builder->join('metode_pembayaran m', 'm.id = t.metode_bayar_id', 'left');
        $builder->join('client c', 'c.id = t.pembeli', 'left');
        $builder->where('t.client_id', $client_id);
        $builder->where('t.jenis', $jenis);
        $builder->orderBy('t.tanggal', 'DESC');
        $query = $builder->get()->getResultArray();
        return $query;
    }

    // public function getLaporan($client_id, $tahun = null, $bulan = null, $tanggal_mulai = null, $tanggal_selesai = null)
    // {
    //     $builder = $this->db->table('transaksi t');
    //     $builder->select(
    //         'COUNT(t.id) as jumlah, 
    //         SUM(CASE WHEN t.jenis = "in" THEN (t.jumlah * s.harga_jual) ELSE 0 END) as total_pendapatan,
    //         SUM(CASE WHEN t.jenis = "out" THEN (t.jumlah * s.harga_beli) ELSE 0 END) as total_pengeluaran,
    //         SUM(CASE WHEN t.jenis = "in" THEN (t.jumlah * s.harga_jual) ELSE 0 END) - SUM(CASE WHEN t.jenis = "out" THEN (t.jumlah * s.harga_beli) ELSE 0 END) as total_keuntungan'
    //     );
    //     $builder->join('data_sampah s', 's.id = t.sampah_id');
    //     $builder->where('t.client_id', $client_id);

    //     if ($tahun) {
    //         $builder->where('YEAR(t.tanggal)', $tahun);
    //     }

    //     if ($bulan) {
    //         $builder->where('MONTH(t.tanggal)', $bulan);
    //     }

    //     if ($tanggal_mulai && $tanggal_selesai) {
    //         $builder->where('DATE(t.tanggal) >=', $tanggal_mulai);
    //         $builder->where('DATE(t.tanggal) <=', $tanggal_selesai);
    //     }

    //     $query = $builder->get()->getRowArray();
    //     return $query;
    // }

    public function getLaporan($client_id, $tahun = null, $bulan = null, $tanggal_mulai = null, $tanggal_selesai = null)
    {
        $builder = $this->db->table('transaksi t');
        $builder->select(
            'COUNT(t.id) as jumlah, 
        SUM(CASE WHEN t.jenis = "in" THEN (t.jumlah * s.harga_jual) ELSE 0 END) as total_pendapatan,
        SUM(CASE WHEN t.jenis = "out" THEN (t.jumlah * s.harga_beli) ELSE 0 END) as total_pengeluaran,
        SUM(CASE WHEN t.jenis = "in" THEN (t.jumlah * s.harga_jual) ELSE 0 END) - 
        SUM(CASE WHEN t.jenis = "out" THEN (t.jumlah * s.harga_beli) ELSE 0 END) as total_keuntungan
        '
        );
        $builder->join('data_sampah s', 's.id = t.sampah_id');
        $builder->where('t.client_id', $client_id);

        // CASE: Group by Tahun → per Bulan
        if ($tahun && !$bulan && !$tanggal_mulai && !$tanggal_selesai) {
            $builder->select('MONTHNAME(t.tanggal) as periode');
            $builder->where('YEAR(t.tanggal)', $tahun);
            $builder->groupBy('YEAR(t.tanggal), MONTH(t.tanggal)');
            $builder->orderBy('MONTH(t.tanggal)', 'ASC');
        }

        // CASE: Group by Bulan → per Tanggal
        if ($bulan && $tahun) {
            $builder->select('DATE(t.tanggal) as periode');
            $builder->where('YEAR(t.tanggal)', $tahun);
            $builder->where('MONTH(t.tanggal)', $bulan);
            $builder->groupBy('DATE(t.tanggal)');
            $builder->orderBy('DATE(t.tanggal)', 'ASC');
        }

        // CASE: Range Harian
        if ($tanggal_mulai && $tanggal_selesai) {
            $builder->select('DATE(t.tanggal) as periode');
            $builder->where('DATE(t.tanggal) >=', $tanggal_mulai);
            $builder->where('DATE(t.tanggal) <=', $tanggal_selesai);
            $builder->groupBy('DATE(t.tanggal)');
            $builder->orderBy('DATE(t.tanggal)', 'ASC');
        }

        $query = $builder->get()->getResultArray();
        return $query;
    }

    public function getLastTransaction($client_id, $bulan)
    {
        $builder = $this->db->table('transaksi t');
        $builder->select('t.*, s.nama_sampah, s.harga_beli, s.harga_jual, s.satuan');
        $builder->join('data_sampah s', 's.id = t.sampah_id');
        $builder->where('t.client_id', $client_id);
        $builder->where('MONTH(t.tanggal)', $bulan);
        $builder->orderBy('t.tanggal', 'DESC');
        $builder->limit(3);
        $query = $builder->get()->getResultArray();
        return $query;
    }

    public function getRingkasanBulan($client_id, $bulan, $tahun)
    {
        $builder = $this->db->table('transaksi t');
        $builder->select(
            'COUNT(t.id) as jumlah, 
            SUM(t.jumlah) as total_jml,
            SUM(CASE WHEN t.jenis = "in" THEN (t.jumlah * s.harga_jual) ELSE 0 END) as total_pendapatan,
            SUM(CASE WHEN t.jenis = "out" THEN (t.jumlah * s.harga_beli) ELSE 0 END) as total_pengeluaran,
            SUM(
            CASE 
                WHEN t.jenis = "in"  THEN (t.jumlah * s.harga_jual)
                WHEN t.jenis = "out" THEN -(t.jumlah * s.harga_beli)
                ELSE 0 
            END
            ) as total_keuntungan
        '
        );
        $builder->join('data_sampah s', 's.id = t.sampah_id');
        $builder->where('t.client_id', $client_id);

        // CASE: Group by Bulan → per Tanggal
        if ($bulan && $tahun) {
            $builder->where('YEAR(t.tanggal)', $tahun);
            $builder->where('MONTH(t.tanggal)', $bulan);
        }

        $query = $builder->get()->getRowArray();
        return $query;
    }

    public function getTotalAll($client_id)
    {
        $builder = $this->db->table('transaksi t');
        $builder->select(
            'COUNT(t.id) as jumlah, 
            SUM(t.jumlah) as total_jml,
            SUM(CASE WHEN t.jenis = "in" THEN (t.jumlah * s.harga_jual) ELSE 0 END) as total_pendapatan,
            SUM(CASE WHEN t.jenis = "out" THEN (t.jumlah * s.harga_beli) ELSE 0 END) as total_pengeluaran,
            SUM(
            CASE 
                WHEN t.jenis = "in"  THEN (t.jumlah * s.harga_jual)
                WHEN t.jenis = "out" THEN -(t.jumlah * s.harga_beli)
                ELSE 0 
            END
            ) as total_keuntungan
        '
        );
        $builder->join('data_sampah s', 's.id = t.sampah_id');
        $builder->where('t.client_id', $client_id);

        $query = $builder->get()->getRowArray();
        return $query;
    }
}
