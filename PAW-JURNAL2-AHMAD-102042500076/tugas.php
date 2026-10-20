<?php

require 'controller/koneksi.php';

$q = (isset($_GET['q']) && is_string($_GET['q'])) ? trim($_GET['q']) : '';
$mk = valid_id($_GET['mk'] ?? null) ?? 0;
$like = '%' . addcslashes($q, '%_\\') . '%';

$sql = $koneksi->prepare('SELECT t.id, t.judul, t.tenggat, t.status, k.kode, k.nama FROM tugas t JOIN mata_kuliah k ON k.id = t.mata_kuliah_id WHERE t.judul LIKE ? AND (? = 0 OR t.mata_kuliah_id = ?) ORDER BY t.tenggat, t.judul');
$sql->bind_param('sii', $like, $mk, $mk);
$sql->execute();

$rows = $sql->get_result()->fetch_all(MYSQLI_ASSOC);
$daftar = daftar_mk($koneksi);

layout_start('Tugas', 'tugas');

?>

<h1>Tugas</h1>

<div class="bar">
    <span>Menampilkan <?= count($rows) ?> data.</span>
    <a class="btn pri" href="tugas_form.php">Tambah tugas</a>
</div>

<form method="get" class="search">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari judul tugas">
    <select name="mk"><?= opsi_mk($daftar, $mk ?: '', true) ?></select>
    <button class="btn pri">Cari</button> 
    <a class="btn" href="tugas.php">Reset</a>
</form>

<?php if (!$rows): ?>
    <div class="kosong">Data tidak ditemukan.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Judul</th>
                <th>Mata kuliah</th>
                <th>Tenggat / status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['judul']) ?></td>
                    <td><?= e($r['kode']) ?> - <?= e($r['nama']) ?></td>
                    <td><?= e(tgl($r['tenggat'])) ?> / <?= e($r['status']) ?></td>
                    <td class="aksi">
                        <a class="btn" href="tugas_detail.php?id=<?= (int)$r['id'] ?>">Detail</a>
                        <a class="btn" href="tugas_form.php?id=<?= (int)$r['id'] ?>">Edit</a>
                        <a class="btn bahaya" href="hapus.php?t=tugas&amp;id=<?= (int)$r['id'] ?>">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php layout_end(); ?>