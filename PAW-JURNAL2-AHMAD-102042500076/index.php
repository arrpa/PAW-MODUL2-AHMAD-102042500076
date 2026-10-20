<?php

require 'controller/koneksi.php';

$sql = $koneksi->prepare('SELECT (SELECT COUNT(*) FROM mata_kuliah) AS mk, (SELECT COUNT(*) FROM materi) AS m, (SELECT COUNT(*) FROM tugas) AS t');
$sql->execute();

$c = $sql->get_result()->fetch_assoc();
$daftar = daftar_mk($koneksi);

layout_start('Beranda', 'beranda');

?>

<h1>Learning Management System</h1>
<p class="muted">Pengelolaan mata kuliah, materi pembelajaran, dan tugas.</p>

<div class="stats">
    <div>Mata kuliah<b><?= (int)$c['mk'] ?></b></div>
    <div>Materi<b><?= (int)$c['m'] ?></b></div>
    <div>Tugas<b><?= (int)$c['t'] ?></b></div>
</div>

<p>
    <a class="btn" href="mata_kuliah.php">Kelola mata kuliah</a> 
    <a class="btn" href="materi.php">Kelola materi</a> 
    <a class="btn" href="tugas.php">Kelola tugas</a>
</p>

<h2>Mata kuliah tersedia</h2>

<?php if (!$daftar): ?>
    <div class="kosong">Belum ada mata kuliah.</div>
<?php else: ?>
    <ul>
        <?php foreach ($daftar as $m): ?>
            <li>
                <a href="mata_kuliah_detail.php?id=<?= (int)$m['id'] ?>">
                    <?= e($m['kode']) ?> <?= e($m['nama']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php layout_end(); ?>