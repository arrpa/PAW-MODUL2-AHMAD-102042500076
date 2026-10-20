<?php
require 'controller/koneksi.php';

$edit = isset($_GET['id']);
$id = null;
$lama = null;

if ($edit) {
    $id = valid_id($_GET['id']);
    if ($id === null) {
        berhenti('ID materi tidak valid.');
    }

    $sql = $koneksi->prepare('SELECT id, mata_kuliah_id, judul, isi, tautan FROM materi WHERE id = ?');
    $sql->bind_param('i', $id);
    $sql->execute();

    $lama = $sql->get_result()->fetch_assoc();
    if (!$lama) {
        berhenti('Materi tidak ditemukan.', 404);
    }
}

$data = $lama ?? ['mata_kuliah_id' => '', 'judul' => '', 'isi' => '', 'tautan' => ''];
$err = [];
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        berhenti('Token tidak valid. Muat ulang halaman lalu coba lagi.', 403);
    }

    $data = [
        'mata_kuliah_id' => post('mata_kuliah_id'),
        'judul'          => post('judul'),
        'isi'            => post('isi'),
        'tautan'         => post('tautan')
    ];

    $mkid = valid_id($data['mata_kuliah_id']);
    if ($mkid === null || !mk_ada($koneksi, $mkid)) {
        $err['mata_kuliah_id'] = 'Pilih mata kuliah yang tersedia.';
    }

    if (panjang($data['judul']) < 3 || panjang($data['judul']) > 150) {
        $err['judul'] = 'Judul harus 3-150 karakter.';
    }

    if (panjang($data['isi']) < 3 || panjang($data['isi']) > 2000) {
        $err['isi'] = 'Isi materi harus 3-2000 karakter.';
    }

    $u = $data['tautan'];
    if ($u === '' || panjang($u) > 255 || !preg_match('~\Ahttps?://~i', $u) || !filter_var($u, FILTER_VALIDATE_URL) || empty(parse_url($u, PHP_URL_HOST))) {
        $err['tautan'] = 'Gunakan tautan http/https yang valid (maksimal 255 karakter).';
    }

    if (!$err) {
        try {
            if ($edit) {
                if (sama($lama, $data)) {
                    $info = 'Tidak ada perubahan data.';
                } else {
                    $sql = $koneksi->prepare('UPDATE materi SET mata_kuliah_id = ?, judul = ?, isi = ?, tautan = ? WHERE id = ?');
                    $sql->bind_param('isssi', $mkid, $data['judul'], $data['isi'], $data['tautan'], $id);
                    $sql->execute();

                    flash('success', 'Materi berhasil diubah.');
                    redirect('materi.php');
                }
            } else {
                $sql = $koneksi->prepare('INSERT INTO materi (mata_kuliah_id, judul, isi, tautan) VALUES (?, ?, ?, ?)');
                $sql->bind_param('isss', $mkid, $data['judul'], $data['isi'], $data['tautan']);
                $sql->execute();

                flash('success', 'Materi berhasil ditambahkan.');
                redirect('materi.php');
            }
        } catch (mysqli_sql_exception $ex) {
            berhenti('Data belum tersimpan karena terjadi kesalahan database.', 500);
        }
    }
}

$daftar = daftar_mk($koneksi);

layout_start($edit ? 'Edit materi' : 'Tambah materi', 'materi');

?>

<h1><?= $edit ? 'Edit materi' : 'Tambah materi' ?></h1>

<?php if ($info): ?>
    <div class="alert info"><?= e($info) ?></div>
<?php endif; ?>

<?php if ($err): ?>
    <div class="alert error">Data belum tersimpan. Periksa isian yang bertanda merah.</div>
<?php endif; ?>

<?php if (!$daftar): ?>
    <div class="alert error">
        Belum ada mata kuliah. <a href="mata_kuliah_form.php">Buat mata kuliah</a> terlebih dahulu.
    </div>
<?php endif; ?>

<form method="post" action="materi_form.php<?= $edit ? '?id=' . (int)$id : '' ?>" novalidate>
    <?= csrf_field() ?>
    <div class="grid">
        <div>
            <label for="mk">Mata kuliah</label>
            <select id="mk" name="mata_kuliah_id">
                <?= opsi_mk($daftar, $data['mata_kuliah_id']) ?>
            </select>
            <?= ferr($err, 'mata_kuliah_id') ?>
        </div>
        <div>
            <label for="judul">Judul materi</label>
            <input type="text" id="judul" name="judul" value="<?= e($data['judul']) ?>" maxlength="150">
            <?= ferr($err, 'judul') ?>
        </div>
        <div class="full">
            <label for="isi">Isi materi</label>
            <textarea id="isi" name="isi" rows="5" maxlength="2000"><?= e($data['isi']) ?></textarea>
            <?= ferr($err, 'isi') ?>
        </div>
        <div class="full">
            <label for="tautan">Tautan referensi</label>
            <input type="text" id="tautan" name="tautan" value="<?= e($data['tautan']) ?>" maxlength="255" placeholder="https://...">
            <?= ferr($err, 'tautan') ?>
        </div>
    </div>
    <p>
        <button class="btn pri">Simpan data</button> 
        <a class="btn" href="materi.php">Batal</a>
    </p>
</form>

<?php layout_end(); ?>