<?php

require 'controller/koneksi.php';

$q = (isset($_GET['q']) && is_string($_GET['q'])) ? trim($_GET['q']) : '';
$like = '%' . addcslashes($q, '%_\\') . '%';

$sql = $koneksi->prepare('SELECT id, kode, nama, dosen, semester FROM mata_kuliah WHERE (kode LIKE ? OR nama LIKE ?) ORDER BY nama');
$sql->bind_param('ss', $like, $like);
$sql->execute();

$rows = $sql->get_result()->fetch_all(MYSQLI_ASSOC);

layout_start('Mata kuliah', 'mk'); 

?>

<h1>Mata kuliah</h1>

<div class="bar">
    <span>Menampilkan <?= count($rows) ?> data.</span>
    <a class="btn pri" href="mata_kuliah_form.php">Tambah mata kuliah</a>
</div>

<form method="get" class="search">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari kode atau nama">
    <button class="btn pri">Cari</button> 
    <a class="btn" href="mata_kuliah.php">Reset</a>
</form>

<?php if (!$rows): ?>
    <div class="kosong">Data tidak ditemukan.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Mata kuliah</th>
                <th>Dosen / semester</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['kode']) ?> - <?= e($r['nama']) ?></td>
                    <td><?= e($r['dosen']) ?> / Semester <?= (int)$r['semester'] ?></td>
                    <td class="aksi">
                        <a class="btn" href="mata_kuliah_detail.php?id=<?= (int)$r['id'] ?>">Detail</a>
                        <a class="btn" href="mata_kuliah_form.php?id=<?= (int)$r['id'] ?>">Edit</a>
                        <a class="btn bahaya" href="hapus.php?t=mata_kuliah&amp;id=<?= (int)$r['id'] ?>">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php layout_end(); ?>