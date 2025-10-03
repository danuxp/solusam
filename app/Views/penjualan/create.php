<?= $this->extend('template/index'); ?>
<?= $this->section('content'); ?>



<div class="card border-success mb-4 mt-4">
    <div class="card-header bg-success bg-opacity-10 text-success fw-bold">
        + Form <?= $title; ?>
    </div>
    <div class="card-body">
        <form action="<?= base_url('penjualan/store') ?>" method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Pilih Tanggal</label>
                    <input
                        type="date"
                        name="tanggal"
                        class="form-control"
                        required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Nama Sampah</label>
                    <select name="nama_sampah" class="form-select" id="nama_sampah" required>
                        <option value="" selected disabled>-- Pilih Sampah --</option>
                        <?php foreach ($sampah as $row) : ?>
                            <option value="<?= $row['id'] ?>">
                                <?= $row['nama_sampah'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <div class="col-md-4">
                    <label class="form-label">Harga</label>
                    <input
                        type="number"
                        name="harga"
                        min="0"
                        placeholder="0"
                        class="form-control"
                        id="harga"
                        readonly
                        required>
                </div>


                <div class="col-md-4">
                    <label class="form-label">Jumlah</label>
                    <input
                        type="number"
                        min="1"
                        name="jumlah_jual"
                        class="form-control"
                        id="jumlah_jual"
                        onkeyup="jumlah()"
                        required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Pembeli</label>
                    <select name="pembeli" class="form-select" id="pembeli" required>
                        <option value="" selected disabled>-- Pilih Pembeli --</option>
                        <?php foreach ($klien as $row) : ?>
                            <option value="<?= $row['id'] ?>">
                                <?= $row['nama_lengkap'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Total Harga</label>
                    <input
                        type="number"
                        name="total_harga"
                        min="0"
                        placeholder="0"
                        class="form-control"
                        id="total_harga"
                        required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Metode Bayar</label>
                    <select name="metode_bayar" class="form-select" id="metode_bayar" required>
                        <option value="" selected disabled>-- Pilih Metode --</option>
                        <?php foreach ($bayar as $row) : ?>
                            <option value="<?= $row['id'] ?>">
                                <?= $row['nama'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4" id="upload_qris" style="display: none;">
                    <label for="bukti_qris" class="form-label">Upload Bukti QRIS</label>
                    <input type="file" name="bukti_qris" id="bukti_qris" class="form-control">
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    Simpan
                </button>
                <a href="<?= base_url('sampah') ?>" class="btn btn-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>


<?= $this->endSection(); ?>

<?= $this->section('js'); ?>
<script>
    $('#nama_sampah').change(function() {
        let sampahId = $(this).val();

        $.ajax({
            url: "<?= base_url('penjualan/sampah-ajax') ?>",
            type: "POST",
            data: {
                id: sampahId
            },
            success: function(response) {
                $('#harga').val(response.harga_jual);
            },
            error: function(xhr, status, error) {
                console.error(error);
                alert('Terjadi kesalahan saat mengambil data harga.');
            }
        });
    });

    function jumlah() {
        let harga = parseFloat($('#harga').val()) || 0;
        let jumlahJual = parseFloat($('#jumlah_jual').val()) || 0;

        let totalHarga = harga * jumlahJual;

        $('#total_harga').val(totalHarga);
    }

    $("#metode_bayar").on("change", function() {
        let metode = $(this).find("option:selected").text();
        let valMetode = metode.toLowerCase().trim()
        console.log(metode);


        if (valMetode == "qris") {
            $("#upload_qris").show();
        } else {
            $("#upload_qris").hide();
        }
    });
</script>
<?= $this->endSection(); ?>