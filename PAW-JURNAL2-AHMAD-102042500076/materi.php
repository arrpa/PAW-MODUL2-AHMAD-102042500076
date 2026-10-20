<?php

require 'controller/koneksi.php';

$q = (isset($_GET['q']) && is_string($_GET['q'])) ? trim($_GET['q']) : '';
$mk = valid_id($_GET['mk'] ?? null) ?? 0;
$like = '%' . addcslashes($q, '%_\\') . '%';

$sql = $koneksi->prepare('SELECT m.id, m.judul, k.kode, k.nama FROM materi m JOIN mata_kuliah k ON k.id = m.mata_kuliah_id WHERE m.judul LIKE ? AND (? = 0 OR m.mata_kuliah_id = ?) ORDER BY m.judul');
$sql->bind_param('sii', $like, $mk, $mk);
$sql->execute();

$rows = $sql->get_result()->fetch_all(MYSQLI_ASSOC);
$daftar = daftar_mk($koneksi);

layout_start('Materi', 'materi'); 

?>

<h1>Materi</h1>

<div class="bar">
    <span>Menampilkan <?= count($rows) ?> data.</span>
    <a class="btn pri" href="materi_form.php">Tambah materi</a>
</div>

<form method="get" class="search">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari judul materi">
    <select name="mk"><?= opsi_mk($daftar, $mk ?: '', true) ?></select>
    <button class="btn pri">Cari</button> 
    <a class="btn" href="materi.php">Reset</a>
</form>

<?php if (!$rows): ?>
    <div class="kosong">Data tidak ditemukan.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Judul</th>
                <th>Mata kuliah</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['judul']) ?></td>
                    <td><?= e($r['kode']) ?> - <?= e($r['nama']) ?></td>
                    <td class="aksi">
                        <a class="btn" href="materi_detail.php?id=<?= (int)$r['id'] ?>">Detail</a>
                        <a class="btn" href="materi_form.php?id=<?= (int)$r['id'] ?>">Edit</a>
                        <a class="btn bahaya" href="hapus.php?t=materi&amp;id=<?= (int)$r['id'] ?>">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php layout_end(); ?>