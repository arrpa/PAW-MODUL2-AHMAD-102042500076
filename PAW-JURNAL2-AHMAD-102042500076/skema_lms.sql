CREATE DATABASE IF NOT EXISTS lms_paw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lms_paw;

CREATE TABLE IF NOT EXISTS mata_kuliah (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    dosen VARCHAR(100) NOT NULL,
    semester TINYINT UNSIGNED NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS materi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT UNSIGNED NOT NULL,
    judul VARCHAR(150) NOT NULL,
    isi TEXT NOT NULL,
    tautan VARCHAR(255) NOT NULL,
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tugas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT UNSIGNED NOT NULL,
    judul VARCHAR(150) NOT NULL,
    instruksi TEXT NOT NULL,
    tenggat DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO mata_kuliah (id, kode, nama, dosen, semester) VALUES
    (1, 'ALP-101', 'Algoritma dan Pemrograman', 'Dosen ALP', 1),
    (2, 'PAW-101', 'Pengembangan Aplikasi Website', 'Dosen PAW', 3);

INSERT INTO materi (mata_kuliah_id, judul, isi, tautan) VALUES
    (1, 'Pengantar Algoritma', 'Materi membahas konsep algoritma dan flowchart dasar.', 'https://www.w3schools.com/python/'),
    (2, 'Dasar PHP', 'Materi membahas sintaks dasar PHP dan koneksi database.', 'https://www.php.net/manual/en/book.mysqli.php');

INSERT INTO tugas (mata_kuliah_id, judul, instruksi, tenggat, status) VALUES
    (1, 'Latihan Flowchart', 'Buat flowchart untuk menghitung rata-rata tiga bilangan.', '2026-12-01', 'Aktif'),
    (2, 'Latihan CRUD', 'Buat aplikasi CRUD sederhana dengan PHP dan MySQL.', '2026-12-15', 'Draf');