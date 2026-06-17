-- =========================================================================
-- 1. PEMBUATAN TABEL-TABEL UTAMA (6 Tabel)
-- =========================================================================

-- Tabel Semesters
CREATE TABLE semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_semester VARCHAR(20) NOT NULL
);

-- Tabel Users (Siswa & Admin: Ketua Kelas/Asprak)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    peran ENUM('mahasiswa', 'admin') DEFAULT 'mahasiswa',
    status_admin ENUM('bukan', 'ketua_kelas', 'asprak') DEFAULT 'bukan'
);

-- Tabel Courses (Mata Kuliah)
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_matkul VARCHAR(10) NOT NULL UNIQUE,
    nama_matkul VARCHAR(100) NOT NULL,
    semester_id INT,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL
);

-- Tabel Course Contents (Materi Kuliah - Objek CRUD Admin)
CREATE TABLE course_contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matkul_id INT,
    judul_materi VARCHAR(150) NOT NULL,
    tipe_konten ENUM('teks', 'video', 'pdf') NOT NULL,
    isi_teks TEXT NULL,
    link_video VARCHAR(255) NULL,
    file_pdf VARCHAR(255) NULL,
    urutan INT DEFAULT 1,
    FOREIGN KEY (matkul_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Tabel Assignments (Tugas dari Admin - Objek CRUD Admin)
CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matkul_id INT,
    judul_tugas VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    FOREIGN KEY (matkul_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Tabel Student Grades (Rekam Nilai & Progress Kelulusan)
CREATE TABLE student_grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    matkul_id INT,
    nilai_tugas INT DEFAULT 0,
    nilai_latsol INT DEFAULT 0,
    nilai_ujian INT DEFAULT 0,
    status_kelulusan ENUM('Belum Lulus', 'Lulus') DEFAULT 'Belum Lulus',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (matkul_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unik_user_matkul (user_id, matkul_id)
);

-- =========================================================================
-- 2. PEMBUATAN STORED FUNCTIONS (2 Fungsi)
-- =========================================================================

DELIMITER $$

-- Fungsi 1: Menghitung Nilai Akhir Berdasarkan Bobot (30% Tugas, 30% Latsol, 40% Ujian)
CREATE FUNCTION hitung_nilai_akhir(tugas INT, latsol INT, ujian INT)
RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN
    DECLARE nilai_total DECIMAL(5,2);
    SET nilai_total = (tugas * 0.30) + (latsol * 0.30) + (ujian * 0.40);
    RETURN nilai_total;
END$$

-- Fungsi 2: Mengonversi Nilai Angka Menjadi Nilai Huruf
CREATE FUNCTION konversi_ke_huruf(nilai_angka INT)
RETURNS CHAR(1)
DETERMINISTIC
BEGIN
    DECLARE huruf CHAR(1);
    IF nilai_angka >= 80 THEN SET huruf = 'A';
    ELSEIF nilai_angka >= 68 THEN SET huruf = 'B';
    ELSEIF nilai_angka >= 56 THEN SET huruf = 'C';
    ELSEIF nilai_angka >= 45 THEN SET huruf = 'D';
    ELSE SET huruf = 'E';
    END IF;
    RETURN huruf;
END$$

DELIMITER ;

-- =========================================================================
-- 3. PEMBUATAN TRIGGERS (2 Trigger)
-- =========================================================================

DELIMITER $$

-- Trigger 1: Validasi Nilai Input Agar Berada di Rentang 0-100 (Sebelum Simpan/Update)
CREATE TRIGGER sebelum_update_nilai
BEFORE UPDATE ON student_grades
FOR EACH ROW
BEGIN
    IF NEW.nilai_tugas < 0 OR NEW.nilai_tugas > 100 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nilai tugas harus di antara 0 sampai 100!';
    END IF;
    IF NEW.nilai_latsol < 0 OR NEW.nilai_latsol > 100 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nilai latihan soal harus di antara 0 sampai 100!';
    END IF;
    IF NEW.nilai_ujian < 0 OR NEW.nilai_ujian > 100 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nilai ujian harus di antara 0 sampai 100!';
    END IF;
END$$

-- Trigger 2: Otomatis Update Status Kelulusan Berdasarkan Rumus Fungsi Jika Nilai Akhir >= 56 (C)
CREATE TRIGGER setelah_hitung_kelulusan
BEFORE UPDATE ON student_grades
FOR EACH ROW
BEGIN
    DECLARE skor_akhir INT;
    SET skor_akhir = hitung_nilai_akhir(NEW.nilai_tugas, NEW.nilai_latsol, NEW.nilai_ujian);
    
    IF skor_akhir >= 56 THEN
        SET NEW.status_kelulusan = 'Lulus';
    ELSE
        SET NEW.status_kelulusan = 'Belum Lulus';
    END IF;
END$$

DELIMITER ;

-- =========================================================================
-- 4. PEMBUATAN VIEWS / QUERY KOMPLEKS (2 View)
-- =========================================================================

-- View 1: Transkrip Nilai Mahasiswa (Menggunakan JOIN dan Fungsi Konversi)
CREATE VIEW view_transkrip_mahasiswa AS
SELECT 
    users.id AS user_id,
    users.nama_lengkap,
    courses.kode_matkul,
    courses.nama_matkul,
    semesters.nama_semester,
    student_grades.nilai_tugas,
    student_grades.nilai_latsol,
    student_grades.nilai_ujian,
    hitung_nilai_akhir(student_grades.nilai_tugas, student_grades.nilai_latsol, student_grades.nilai_ujian) AS nilai_angka,
    konversi_ke_huruf(hitung_nilai_akhir(student_grades.nilai_tugas, student_grades.nilai_latsol, student_grades.nilai_ujian)) AS nilai_huruf,
    student_grades.status_kelulusan
FROM student_grades
JOIN users ON student_grades.user_id = users.id
JOIN courses ON student_grades.matkul_id = courses.id
JOIN semesters ON courses.semester_id = semesters.id;

-- View 2: Statistik Dashboard Admin (Menggunakan JOIN, COUNT, dan Subquery Kompleks)
CREATE VIEW view_dashboard_admin AS
SELECT 
    courses.id AS matkul_id,
    courses.nama_matkul,
    COUNT(student_grades.user_id) AS total_mahasiswa_terdaftar,
    SUM(CASE WHEN student_grades.status_kelulusan = 'Lulus' THEN 1 ELSE 0 END) AS jumlah_lulus,
    (SELECT COUNT(*) FROM course_contents WHERE course_contents.matkul_id = courses.id) AS jumlah_materi_dibuat
FROM courses
LEFT JOIN student_grades ON courses.id = student_grades.matkul_id
GROUP BY courses.id;

-- =========================================================================
-- 5. PENGISIAN DATA AWAL (Seeding Data)
-- =========================================================================

INSERT INTO semesters (nama_semester) VALUES 
('Semester 1'), ('Semester 2'), ('Semester 3'), ('Semester 4'),
('Semester 5'), ('Semester 6'), ('Semester 7'), ('Semester 8');

INSERT INTO courses (kode_matkul, nama_matkul, semester_id, is_pilihan) VALUES 
('SVPL214104','Bahasa Inggris 1',1, 'tidak'),
('SVPL214102','Matematika Teknik',1, 'tidak'),
('SVPL214103','Matematika Diskrit',1, 'tidak'),
('SVPL214101','Pengantar Teknologi Informasi',1, 'tidak'),
('SVPL214107','Algoritma dan Pemrograman',1, 'tidak'),
('SVPL214108','Keselamatan dan Kesehatan Kerja',1, 'tidak'),
('SVPL214106','Praktikum Pemrograman Komputer',1, 'tidak'),
('SVPL214109','Praktikum Desain Elementer',1, 'tidak'),
('SVPL214502','Bahasa Indonesia',1, 'tidak'),
('SVPL214201','Bahasa Inggris 2',2, 'tidak'),
('SVPL214203','Aljabar Vektor dan Matrik',2, 'tidak'),
('SVPL214204','Analisis Algoritma dan Struktur Data',2, 'tidak'),
('SVPL214206','Basis Data',2, 'tidak'),
('SVPL214209','Pemrograman Berorientasi Objek',2, 'tidak'),
('SVPL214207','Praktikum Basis Data',2, 'tidak'),
('SVPL214208','Praktikum Pemrograman Web 1',2, 'tidak'),
('SVPL214205','Praktikum Struktur Data',2, 'tidak'),
('SVPL214210','Praktikum Pemrograman Berorientasi Objek',2, 'tidak'),
('SVPL214603','Statistika',2, 'tidak'),
('SVPL214301','Metode dan Model Pengembangan Perangkat Lunak',3, 'tidak'),
('SVPL214302','Rekayasa Kebutuhan Perangkat Lunak',3, 'tidak'),
('SVPL214303','Analisis dan Desain Perangkat Lunak',3, 'tidak'),
('SVPL214509','Kapita Selekta',3, 'tidak'),
('SVPL214309','Manajemen Proyek',3, 'tidak'),
('SVPL214310','Arsitektur Perangkat Lunak',3, 'tidak'),
('SVPL214306','Praktikum Pemrograman Perangkat Bergerak 1',3, 'tidak'),
('SVPL214307','Proyek Aplikasi Dasar 1',3, 'tidak'),
('SVPL214304','Praktikum Desain Perangkat Lunak',3, 'tidak'),
('SVPL214305','Praktikum Pemrograman Website 2',3, 'tidak'),
('SVPL214609','Verifikasi dan Validasi Perangkat Lunak',4, 'tidak'),
('SVPL214408','Animasi dan Desain Multimedia',4, 'tidak'),
('SVPL214608','Kecerdasan Artifisial',4, 'tidak'),
('SVPL214404','Interoperabilitas',4, 'tidak'),
('SVPL214504','Pengembangan Game dan Teknologi Immersive',4, 'tidak'),
('SVPL214703','Metodologi Penelitian',4, 'tidak'),
('SVPL214407','Praktikum Animasi dan Desain Multimedia',4, 'tidak'),
('SVPL214405','Praktikum Sistem Administrasi dan Informasi Terdistribusi',4, 'tidak'),
('SVPL214402','Proyek Aplikasi Dasar 2',4, 'tidak'),
('SVPL214507','Praktikum Pengujian Perangkat Lunak',4, 'tidak'),
('SVPL214501','Konstruksi dan Evolusi Perangkat Lunak',5, 'tidak'),
('SVPL214706','Etika Profesi',5, 'tidak'),
('SVPL214701','Ide Kreatif dan Kewirausahaan',5, 'tidak'),
('SVPL214505','Praktikum Pengembangan Game',5, 'tidak'),
('SVPL214610','Praktikum Penambangan Data',5, 'tidak'),
('SVPL214508','Proyek Mandiri Lintas Disiplin Ilmu 1',5, 'tidak'),
('SVPL214506','Agama',5, 'tidak'),
('SVPL214510','Pancasila',5, 'tidak'),
('SVPL214708','Pengantar Sains Data',5, 'tidak'),
('SVPL214503','Kewarganegaraan',5, 'tidak'),
('SVPL214715','Kepemimpinan',6, 'tidak'),
('SVPL214602','Paparan Kompetensi Global',6, 'tidak'),
('SVPL214403','Keamanan Pengembangan Perangkat Lunak',6, 'tidak'),
('SVPL214607','Penjaminan Kualitas Perangkat Lunak',6, 'tidak'),
('SVPL214604','Praktikum Komunikasi dan Presentasi',6, 'tidak'),
('SVPL214401','Praktik Industri',6, 'tidak'),
('SVPL214601','Proyek Mandiri Lintas Disiplin Ilmu 2',6, 'tidak'),
('SVPL214702','Proyek Pengembangan Perangkat Lunak',7, 'tidak'),
('SVPL214705','Tata Kelola IT',NULL,'ya'),
('SVPL214613','Sistem Informasi',NULL,'ya'),
('SVPL214704','E-Bisnis',NULL,'ya'),
('SVPL214616','Praktikum Pengolahan Citra Digital',NULL,'ya'),
('SVPL214612','Interaksi Manusia dan Komputer',NULL,'ya'),
('-','Literasi Kesehatan',8, 'tidak'),
('UNU222001','KKN-PPM',8, 'tidak'),
('UNU222002','Komunikasi Masyarakat',8, 'tidak'),
('UNU222003','Penerapan Teknologi Tepat Guna',8, 'tidak'),
('SVPL214611','Produksi Virtual',NULL,'ya'),
('SVPL214709','Komputasi Awan',NULL,'ya'),
('SVPL214615','Praktikum Pembelajaran Mesin',NULL,'ya'),
('SVPL214710','Sistem Pendukung Keputusan',NULL,'ya'),
('SVPL214711','Teknologi Blockchain',NULL,'ya'),
('SVPL214712','Sistem Informasi Geografis',NULL,'ya'),
('SVPL214713','Ulasan dan Analisis Multimedia',NULL,'ya'),
('SVPL214714','Narasi Interaktif & Storyboard',NULL,'ya'),
('SVPL214716','Enterprise Resource Planning',NULL,'ya'),
('SVPL214717','Praktikum Komputasi Awan',NULL,'ya'),
('SVPL214718','Praktikum Pemrograman Komputer Lanjut',NULL,'ya'),
('SVPL214308','Jaringan Komputer',NULL,'ya'),
('SVPL214605','Praktikum Pemrograman Web 3',NULL,'ya'),
('SVPL214406','Praktikum Pemrograman Aplikasi Perangkat Bergerak 2',NULL,'ya'),
('SVPL214606','Basis Data Lanjut',NULL,'ya');
INSERT INTO courses (kode_matkul, nama_matkul, semester_id, is_pilihan) VALUES 
('SVPL214802', 'PROYEK AKHIR', NULL, 'tidak');
INSERT INTO course_semester (course_id, semester_id)
SELECT courses.id, 7 FROM courses WHERE courses.kode_matkul = 'SVPL214802';
INSERT INTO course_semester (course_id, semester_id)
SELECT courses.id, 8 FROM courses WHERE courses.kode_matkul = 'SVPL214802';

-- Akun dummy: Password asli untuk kedua akun adalah 'rahasia123' (Wajib di-hash di PHP nanti)
-- Di sini kita masukkan string hash buatan sementara agar sistem database terbentuk utuh.
INSERT INTO users (username, password, nama_lengkap, peran, status_admin) VALUES 
('mahasiswa1', '$2y$10$wK8ZzG7yB5XF2pUo3eCgOux0D6pM7YvG4zO5ZcKj5u7B9C1D2E3FG', 'Budi Santoso', 'mahasiswa', 'bukan'),
('admin_asprak', '$2y$10$wK8ZzG7yB5XF2pUo3eCgOux0D6pM7YvG4zO5ZcKj5u7B9C1D2E3FG', 'Siti Aminah (Asprak Akpro)', 'admin', 'asprak');

INSERT INTO student_grades (user_id, matkul_id, nilai_tugas, nilai_latsol, nilai_ujian) VALUES 
(1, 1, 80, 75, 85); -- Budi di matkul AKT101