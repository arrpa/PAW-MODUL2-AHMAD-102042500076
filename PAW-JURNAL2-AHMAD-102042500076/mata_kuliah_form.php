<?php

require 'controller/koneksi.php';

$edit = isset($_GET['id']);
$id = null;
$lama = null;

if ($edit) {
    $id = valid_id($_GET['id']);
    if ($id === null) {
        berhenti('ID mata kuliah tidak valid.');
    }

    $sql = $koneksi->prepare('SELECT id, kode, nama, dosen, semester FROM mata_kuliah WHERE id = ?');
    $sql->bind_param('i', $id);
    $sql->execute();

    $lama = $sql->get_result()->fetch_assoc();
    if (!$lama) {
        berhenti('Mata kuliah tidak ditemukan.', 404);
    }
}

$data = $lama ?? ['kode' => '', 'nama' => '', 'dosen' => '', 'semester' => ''];
$err = [];
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        berhenti('Token tidak valid. Muat ulang halaman lalu coba lagi.', 403);
    }

    $data = [
        'kode' => post('kode'),
        'nama' => post('nama'),
        'dosen' => post('dosen'),
        'semester' => post('semester')
    ];

    if (!preg_match('/\A[A-Z0-9-]{3,20}\z/', $data['kode'])) {
        $err['kode'] = 'Kode 3-20 karakter: huruf kapital, angka, atau tanda -.';
    }
    if (panjang($data['nama']) < 3 || panjang($data['nama']) > 100) {
        $err['nama'] = 'Nama harus 3-100 karakter.';
    }
    if (panjang($data['dosen']) < 3 || panjang($data['dosen']) > 100) {
        $err['dosen'] = 'Dosen harus 3-100 karakter.';
    }
    if (!ctype_digit($data['semester']) || strlen($data['semester']) > 2 || (int)$data['semester'] < 1 || (int)$data['semester'] > 14) {
        $err['semester'] = 'Semester harus bilangan bulat 1-14.';
    }

    if (!isset($err['kode'])) {
        $cek = $id ?? 0;
        $sql = $koneksi->prepare('SELECT id FROM mata_kuliah WHERE kode = ? AND id <> ?');
        $sql->bind_param('si', $data['kode'], $cek);
        $sql->execute();
        if ($sql->get_result()->fetch_assoc()) {
            $err['kode'] = 'Kode mata kuliah sudah digunakan.';
        }
    }

    if (!$err) {
        $smt = (int)$data['semester'];
        try {
            if ($edit) {
                if (sama($lama, $data)) {
                    $info = 'Tidak ada perubahan data.';
                } else {
                    $sql = $koneksi->prepare('UPDATE mata_kuliah SET kode = ?, nama = ?, dosen = ?, semester = ? WHERE id = ?');
                    $sql->bind_param('sssii', $data['kode'], $data['nama'], $data['dosen'], $smt, $id);
                    $sql->execute();

                    flash('success', 'Mata kuliah berhasil diubah.');
                    redirect('mata_kuliah.php');
                }
            } else {
                $sql = $koneksi->prepare('INSERT INTO mata_kuliah (kode, nama, dosen, semester) VALUES (?, ?, ?, ?)');
                $sql->bind_param('sssi', $data['kode'], $data['nama'], $data['dosen'], $smt);
                $sql->execute();

                flash('success', 'Mata kuliah berhasil ditambahkan.');
                redirect('mata_kuliah.php');
            }
        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() == 1062) {
                $err['kode'] = 'Kode mata kuliah sudah digunakan.';
            } else {
                berhenti('Data belum tersimpan karena terjadi kesalahan database.', 500);
            }
        }
    }
}

layout_start($edit ? 'Edit mata kuliah' : 'Tambah mata kuliah', 'mk'); 
?>

<h1><?= $edit ? 'Edit mata kuliah' : 'Tambah mata kuliah' ?></h1>

<?php if ($info): ?>
    <div class="alert info"><?= e($info) ?></div>
<?php endif; ?>

<?php if ($err): ?>
    <div class="alert error">Data belum tersimpan. Periksa isian yang bertanda merah.</div>
<?php endif; ?>

<form method="post" action="mata_kuliah_form.php<?= $edit ? '?id=' . (int)$id : '' ?>" novalidate>
    <?= csrf_field() ?>
    <div class="grid">
        <div>
            <label for="kode">Kode mata kuliah</label>
            <input type="text" id="kode" name="kode" value="<?= e($data['kode']) ?>" maxlength="20">
            <?= ferr($err, 'kode') ?>
        </div>
        <div>
            <label for="nama">Nama mata kuliah</label>
            <input type="text" id="nama" name="nama" value="<?= e($data['nama']) ?>" maxlength="100">
            <?= ferr($err, 'nama') ?>
        </div>
        <div>
            <label for="dosen">Dosen pengampu</label>
            <input type="text" id="dosen" name="dosen" value="<?= e($data['dosen']) ?>" maxlength="100">
            <?= ferr($err, 'dosen') ?>
        </div>
        <div>
            <label for="semester">Semester</label>
            <input type="text" id="semester" name="semester" value="<?= e($data['semester']) ?>" maxlength="2">
            <?= ferr($err, 'semester') ?>
        </div>
    </div>
    <p>
        <button class="btn pri">Simpan data</button> 
        <a class="btn" href="mata_kuliah.php">Batal</a>
    </p>
</form>

<?php layout_end(); ?>