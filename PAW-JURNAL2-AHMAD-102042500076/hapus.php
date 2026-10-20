<?php

require 'controller/koneksi.php';

$t = $_GET['t'] ?? '';
if (!is_string($t) || !isset($ENTITAS[$t])) {
    berhenti('Jenis data tidak dikenal.', 404);
}

$cfg = $ENTITAS[$t];
$id = valid_id($_GET['id'] ?? null);
if ($id === null) {
    berhenti('ID tidak valid.');
}

$tabel = $cfg['tabel'];
$kol = $cfg['nama'];

$sql = $koneksi->prepare("SELECT $kol AS nama FROM $tabel WHERE id = ?");
$sql->bind_param('i', $id);
$sql->execute();

$row = $sql->get_result()->fetch_assoc();
if (!$row) {
    berhenti($cfg['label'] . ' tidak ditemukan.', 404);
}

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        berhenti('Token tidak valid. Penghapusan dibatalkan.', 403);
    }
    
    if (valid_id($_POST['id'] ?? null) !== $id) {
        berhenti('ID pada form tidak sesuai. Penghapusan dibatalkan.', 400);
    }
    
    try {
        $sql = $koneksi->prepare("DELETE FROM $tabel WHERE id = ?");
        $sql->bind_param('i', $id);
        $sql->execute();
        
        flash('success', $cfg['label'] . ' berhasil dihapus.');
        redirect($cfg['daftar']);
    } catch (mysqli_sql_exception $ex) {
        $pesan = ($ex->getCode() == 1451)
            ? 'Data belum terhapus. Mata kuliah masih memiliki materi atau tugas. Hapus data terkait terlebih dahulu.'
            : 'Data belum terhapus karena terjadi kesalahan database.';
    }
}

layout_start('Hapus ' . $cfg['label']);

?>

<h1><?= e($cfg['label']) ?></h1>

<?php if ($pesan): ?>
    <div class="alert error"><?= e($pesan) ?></div>
<?php endif; ?>

<p>Hapus <strong><?= e($row['nama']) ?></strong>?</p>
<p class="muted">Data baru dihapus setelah tombol konfirmasi dikirim.</p>

<form method="post" action="hapus.php?t=<?= e($t) ?>&amp;id=<?= (int)$id ?>" class="inline">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <button class="btn bahaya">Ya, hapus data</button>
</form>

<a class="btn" href="<?= e($cfg['daftar']) ?>">Batal</a>

<?php layout_end(); ?>