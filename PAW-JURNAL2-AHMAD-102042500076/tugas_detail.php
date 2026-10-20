<?php

require 'controller/koneksi.php';

$id = valid_id($_GET['id'] ?? null);
if ($id === null) {
    berhenti('ID tugas tidak valid.');
}

$sql = $koneksi->prepare('SELECT t.id, t.judul, t.instruksi, t.tenggat, t.status, k.kode, k.nama FROM tugas t JOIN mata_kuliah k ON k.id = t.mata_kuliah_id WHERE t.id = ?');
$sql->bind_param('i', $id);
$sql->execute();

$d = $sql->get_result()->fetch_assoc();
if (!$d) {
    berhenti('Tugas tidak ditemukan.', 404);
}

layout_start('Detail tugas', 'tugas'); 

?>

<h1>Tugas</h1>
<h2><?= e($d['judul']) ?></h2>

<dl class="detail">
    <dt>Mata kuliah</dt>
    <dd><?= e($d['kode']) ?> - <?= e($d['nama']) ?></dd>

    <dt>Judul tugas</dt>
    <dd><?= e($d['judul']) ?></dd>

    <dt>Instruksi</dt>
    <dd><?= nl2br(e($d['instruksi'])) ?></dd>

    <dt>Tenggat</dt>
    <dd><?= e(tgl($d['tenggat'])) ?></dd>

    <dt>Status</dt>
    <dd><?= e($d['status']) ?></dd>
</dl>

<p>
    <a class="btn pri" href="tugas_form.php?id=<?= (int)$d['id'] ?>">Edit data</a> 
    <a class="btn" href="tugas.php">Kembali</a>
</p>

<?php layout_end(); ?>