<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $koneksi = new mysqli('localhost', 'root', 'root', 'ahmad_lmsdb');
    $koneksi->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    http_response_code(500);
    die('Koneksi database gagal. Pastikan MySQL aktif, skema_lms.sql sudah diimport, dan pengaturan di koneksi.php benar.');
}

$ENTITAS = [
    'mata_kuliah' => ['label' => 'Mata kuliah', 'tabel' => 'mata_kuliah', 'nama' => 'nama',  'daftar' => 'mata_kuliah.php'],
    'materi'      => ['label' => 'Materi',      'tabel' => 'materi',      'nama' => 'judul', 'daftar' => 'materi.php'],
    'tugas'       => ['label' => 'Tugas',       'tabel' => 'tugas',       'nama' => 'judul', 'daftar' => 'tugas.php'],
];

const STATUS_TUGAS = ['Draf', 'Aktif', 'Selesai'];

function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_ok($t) {
    return is_string($t) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function flash($tipe, $pesan) {
    $_SESSION['flash'] = [$tipe, $pesan];
}

function flash_html() {
    if (empty($_SESSION['flash'])) {
        return '';
    }
    [$t, $p] = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert ' . e($t) . '">' . e($p) . '</div>';
}

function valid_id($v) {
    if (!is_string($v) || !ctype_digit($v) || strlen($v) > 10) {
        return null;
    }
    $n = (int)$v;
    return ($n >= 1 && $n <= 4294967295) ? $n : null;
}

function post($k) {
    return (isset($_POST[$k]) && is_string($_POST[$k])) ? trim($_POST[$k]) : '';
}

function panjang($s) {
    return mb_strlen($s, 'UTF-8');
}

function redirect($u) {
    header('Location: ' . $u);
    exit;
}

function ferr($err, $k) {
    return isset($err[$k]) ? '<div class="err">' . e($err[$k]) . '</div>' : '';
}

function tgl($d) {
    $t = strtotime($d);
    return $t ? date('d/m/Y', $t) : $d;
}

function sama($lama, $baru) {
    foreach ($baru as $k => $v) {
        if ((string)$lama[$k] !== (string)$v) {
            return false;
        }
    }
    return true;
}

function daftar_mk($koneksi) {
    $sql = $koneksi->prepare('SELECT id, kode, nama FROM mata_kuliah ORDER BY nama');
    $sql->execute();
    return $sql->get_result()->fetch_all(MYSQLI_ASSOC);
}

function mk_ada($koneksi, $id) {
    $sql = $koneksi->prepare('SELECT id FROM mata_kuliah WHERE id = ?');
    $sql->bind_param('i', $id);
    $sql->execute();
    return (bool)$sql->get_result()->fetch_assoc();
}

function opsi_mk($daftar, $pilih, $semua = false) {
    $h = '<option value="">' . ($semua ? 'Semua mata kuliah' : 'Pilih Mata kuliah') . '</option>';
    foreach ($daftar as $m) {
        $h .= '<option value="' . (int)$m['id'] . '"' . ((string)$m['id'] === (string)$pilih ? ' selected' : '') . '>'
            . e($m['kode'] . ' - ' . $m['nama']) . '</option>';
    }
    return $h;
}

function layout_start($judul, $aktif = '') {
    $nav = [
        'index.php' => ['Beranda', 'beranda'],
        'mata_kuliah.php' => ['Mata kuliah', 'mk'],
        'materi.php' => ['Materi', 'materi'],
        'tugas.php' => ['Tugas', 'tugas']
    ];

    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . e($judul) . ' - LMS</title><link rel="stylesheet" href="style.css"></head><body><nav class="nav"><strong>LMS</strong>';

    foreach ($nav as $u => [$l, $k]) {
        echo '<a href="' . $u . '"' . ($aktif === $k ? ' class="on"' : '') . '>' . $l . '</a>';
    }

    echo '</nav><main class="box">' . flash_html();
}

function layout_end() {
    echo '</main></body></html>';
}

function berhenti($pesan, $kode = 400) {
    http_response_code($kode);
    layout_start('Terjadi masalah');
    echo '<h1>Terjadi masalah</h1><div class="alert error">' . e($pesan) . '</div><p><a class="btn" href="index.php">Kembali ke beranda</a></p>';
    layout_end();
    exit;
}