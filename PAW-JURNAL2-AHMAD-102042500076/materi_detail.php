<?php

require 'controller/koneksi.php';

$id = valid_id($_GET['id'] ?? null);
if ($id === null) {
    berhenti('ID materi tidak valid.');
}

$sql = $koneksi->prepare('SELECT m.id, m.judul, m.isi, m.tautan, k.kode, k.nama FROM materi m JOIN mata_kuliah k ON k.id = m.mata_kuliah_id WHERE m.id = ?');
$sql->bind_param('i', $id);
$sql->execute();

$d = $sql->get_result()->fetch_assoc();
if (!$d) {
    berhenti('Materi tidak ditemukan.', 404);
}

layout_start('Detail materi', 'materi');

?>

<h1>Materi</h1>
<h2><?= e($d['judul']) ?></h2>

<dl class="detail">
    <dt>Mata kuliah</dt>
    <dd><?= e($d['kode']) ?> - <?= e($d['nama']) ?></dd>
    
    <dt>Judul materi</dt>
    <dd><?= e($d['judul']) ?></dd>
    
    <dt>Isi materi</dt>
    <dd><?= nl2br(e($d['isi'])) ?></dd>
    
    <dt>Tautan referensi</dt>
    <dd>
        <a href="<?= e($d['tautan']) ?>" target="_blank" rel="noopener noreferrer">
            <?= e($d['tautan']) ?>
        </a>
    </dd>
</dl>

<p>
    <a class="btn pri" href="materi_form.php?id=<?= (int)$d['id'] ?>">Edit data</a> 
    <a class="btn" href="materi.php">Kembali</a>
</p>

<?php layout_end(); ?>