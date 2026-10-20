<?php

require 'controller/koneksi.php';
$edit = isset($_GET['id']);
$id = null; $lama = null;
if ($edit) {
    $id = valid_id($_GET['id']);
    if ($id === null) berhenti('ID tugas tidak valid.');
    $sql = $koneksi->prepare('SELECT id, mata_kuliah_id, judul, instruksi, tenggat, status FROM tugas WHERE id = ?');
    $sql->bind_param('i', $id); $sql->execute();
    $lama = $sql->get_result()->fetch_assoc();
    if (!$lama) berhenti('Tugas tidak ditemukan.', 404);
}
$data = $lama ?? ['mata_kuliah_id' => '', 'judul' => '', 'instruksi' => '', 'tenggat' => '', 'status' => 'Draf'];
$err = []; $info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? null)) berhenti('Token tidak valid. Muat ulang halaman lalu coba lagi.', 403);
    $data = ['mata_kuliah_id' => post('mata_kuliah_id'), 'judul' => post('judul'), 'instruksi' => post('instruksi'),
            'tenggat' => post('tenggat'), 'status' => post('status')];

    $mkid = valid_id($data['mata_kuliah_id']);
    if ($mkid === null || !mk_ada($koneksi, $mkid)) $err['mata_kuliah_id'] = 'Pilih mata kuliah yang tersedia.';
    if (panjang($data['judul']) < 3 || panjang($data['judul']) > 150) $err['judul'] = 'Judul harus 3-150 karakter.';
    if (panjang($data['instruksi']) < 3 || panjang($data['instruksi']) > 2000) $err['instruksi'] = 'Instruksi harus 3-2000 karakter.';
    $tg = $data['tenggat'];
    if (!preg_match('/\A(\d{4})-(\d{2})-(\d{2})\z/', $tg, $mm) || !checkdate((int)$mm[2], (int)$mm[3], (int)$mm[1]))
        $err['tenggat'] = 'Tenggat harus tanggal yang valid.';
    if (!in_array($data['status'], STATUS_TUGAS, true)) $err['status'] = 'Status hanya boleh Draf, Aktif, atau Selesai.';

    if (!$err) {
        try {
            if ($edit) {
                if (sama($lama, $data)) {
                    $info = 'Tidak ada perubahan data.';
                } else {
                    $sql = $koneksi->prepare('UPDATE tugas SET mata_kuliah_id = ?, judul = ?, instruksi = ?, tenggat = ?, status = ? WHERE id = ?');
                    $sql->bind_param('issssi', $mkid, $data['judul'], $data['instruksi'], $data['tenggat'], $data['status'], $id);
                    $sql->execute();
                    flash('success', 'Tugas berhasil diubah.');
                    redirect('tugas.php');
                }
            } else {
                $sql = $koneksi->prepare('INSERT INTO tugas (mata_kuliah_id, judul, instruksi, tenggat, status) VALUES (?, ?, ?, ?, ?)');
                $sql->bind_param('issss', $mkid, $data['judul'], $data['instruksi'], $data['tenggat'], $data['status']);
                $sql->execute();
                flash('success', 'Tugas berhasil ditambahkan.');
                redirect('tugas.php');
            }
        } catch (mysqli_sql_exception $ex) {
            berhenti('Data belum tersimpan karena terjadi kesalahan database.', 500);
        }
    }
}
$daftar = daftar_mk($koneksi);

layout_start($edit ? 'Edit tugas' : 'Tambah tugas', 'tugas'); ?>
<h1><?= $edit ? 'Edit tugas' : 'Tambah tugas' ?></h1>
<?php if ($info): ?><div class="alert info"><?= e($info) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert error">Data belum tersimpan. Periksa isian yang bertanda merah.</div><?php endif; ?>
<?php if (!$daftar): ?><div class="alert error">Belum ada mata kuliah. <a href="mata_kuliah_form.php">Buat mata kuliah</a> terlebih dahulu.</div><?php endif; ?>
<form method="post" action="tugas_form.php<?= $edit ? '?id=' . (int)$id : '' ?>" novalidate>
    <?= csrf_field() ?>
    <div class="grid">
        <div><label for="mk">Mata kuliah</label><select id="mk" name="mata_kuliah_id"><?= opsi_mk($daftar, $data['mata_kuliah_id']) ?></select><?= ferr($err, 'mata_kuliah_id') ?></div>
        <div><label for="judul">Judul tugas</label><input type="text" id="judul" name="judul" value="<?= e($data['judul']) ?>" maxlength="150"><?= ferr($err, 'judul') ?></div>
        <div class="full"><label for="ins">Instruksi tugas</label><textarea id="ins" name="instruksi" rows="5" maxlength="2000"><?= e($data['instruksi']) ?></textarea><?= ferr($err, 'instruksi') ?></div>
        <div><label for="tg">Tenggat</label><input type="date" id="tg" name="tenggat" value="<?= e($data['tenggat']) ?>"><?= ferr($err, 'tenggat') ?></div>
        <div><label for="st">Status</label><select id="st" name="status">
        <?php foreach (STATUS_TUGAS as $s): ?><option value="<?= e($s) ?>"<?= $data['status'] === $s ? ' selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
        </select><?= ferr($err, 'status') ?></div>
    </div>
    <p><button class="btn pri">Simpan data</button> <a class="btn" href="tugas.php">Batal</a></p>
</form>
<?php layout_end();

?>