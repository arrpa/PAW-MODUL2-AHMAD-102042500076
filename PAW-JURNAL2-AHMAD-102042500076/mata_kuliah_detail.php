<?php

require 'controller/koneksi.php';

$id = valid_id($_GET['id'] ?? null);
if ($id === null) {
    berhenti('ID mata kuliah tidak valid.');
}

$sql = $koneksi->prepare('SELECT id, kode, nama, dosen, semester FROM mata_kuliah WHERE id = ?');
$sql->bind_param('i', $id);
$sql->execute();

$d = $sql->get_result()->fetch_assoc();
if (!$d) {
    berhenti('Mata kuliah tidak ditemukan.', 404);
}

$sql = $koneksi->prepare('SELECT id, judul FROM materi WHERE mata_kuliah_id = ? ORDER BY judul');
$sql->bind_param('i', $id);
$sql->execute();
$materi = $sql->get_result()->fetch_all(MYSQLI_ASSOC);

$sql = $koneksi->prepare('SELECT id, judul, status FROM tugas WHERE mata_kuliah_id = ? ORDER BY judul');
$sql->bind_param('i', $id);
$sql->execute();
$tugas = $sql->get_result()->fetch_all(MYSQLI_ASSOC);

layout_start('Detail mata kuliah', 'mk');

?>

<h1>Mata kuliah</h1>

<dl class="detail">
    <dt>Kode</dt>
    <dd><?= e($d['kode']) ?></dd>
    <dt>Nama</dt>
    <dd><?= e($d['nama']) ?></dd>
    <dt>Dosen</dt>
    <dd><?= e($d['dosen']) ?></dd>
    <dt>Semester</dt>
    <dd><?= (int)$d['semester'] ?></dd>
</dl>

<p>
    <a class="btn pri" href="mata_kuliah_form.php?id=<?= (int)$d['id'] ?>">Edit data</a> 
    <a class="btn" href="mata_kuliah.php">Kembali</a>
</p>

<h2>Materi (<?= count($materi) ?>)</h2>
<?php if (!$materi): ?>
    <div class="kosong">Belum ada materi.</div>
<?php else: ?>
    <ul>
        <?php foreach ($materi as $m): ?>
            <li>
                <a href="materi_detail.php?id=<?= (int)$m['id'] ?>"><?= e($m['judul']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2>Tugas (<?= count($tugas) ?>)</h2>
<?php if (!$tugas): ?>
    <div class="kosong">Belum ada tugas.</div>
<?php else: ?>
    <ul>
        <?php foreach ($tugas as $t): ?>
            <li>
                <a href="tugas_detail.php?id=<?= (int)$t['id'] ?>"><?= e($t['judul']) ?></a> (<?= e($t['status']) ?>)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php layout_end(); ?>