<?php
// index.php

// 1. Ambil file koneksi database murni
require_once 'includes/config.php';

// 2. Render komponen navigasi atas (Menggunakan editorial-lms.css)
require_once 'includes/header.php';
?>

<div class="LMS-Banner">
    
    <div class="text-area">
        <div class="banner-title">
            <span style="font-size: 14px; font-weight: 700; letter-spacing: 2px; color: #0056b3; text-transform: uppercase; display: block; mb-2;">
                #DariMahasiswaUntukMahasiswa
            </span>
            <h1 style="font-weight: 900; font-size: 56px; line-height: 105%; margin-bottom: 20px; letter-spacing: -1.5px;">
                Eksplorasi Ilmu Tanpa Batas.
            </h1>
            <p style="font-size: 16px; font-weight: 300; opacity: 0.8; line-height: 1.6; margin-bottom: 30px; max-width: 480px;">
                LMS Sukses adalah platform peer-learning mandiri yang dirancang khusus untuk mempermudah mahasiswa menguasai materi perkuliahan, latihan soal interaktif, dan ujian terstruktur secara kolektif.
            </p>
            <div style="display: flex; gap: 15px;">
                <a href="/php/ppw/UAS-V2/lms-sukses/pages/login.php" style="background-color: #ffffff; color: #1b1b1b; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px; transition: background-color 0.2s;">
                    Mulai Belajar
                </a>
                <a href="#fitur-akademik" style="background-color: transparent; color: #ffffff; padding: 12px 25px; text-decoration: none; border: 2px solid #ffffff; border-radius: 6px; font-weight: 600; font-size: 15px;">
                    Pelajari Fitur
                </a>
            </div>
        </div>
    </div>

    <div class="image-area">
        <div style="text-align: center;">
            <h1 style="font-weight: 900; font-size: 110px; color: #1b1b1b; line-height: 80%; letter-spacing: -4px; margin-bottom: 10px;">LMS.</h1>
            <p style="font-size: 14px; color: #666666; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">Version 1.0 – Native Stack</p>
        </div>
    </div>

</div>

<div class="academic-section" id="fitur-akademik">
    
    <div style="text-align: center; margin-bottom: 40px; padding: 0 40px;">
        <h2 style="font-size: 32px; font-weight: 800; color: #1b1b1b; letter-spacing: -0.5px;">Kenapa Harus LMS Sukses?</h2>
        <p style="font-size: 15px; color: #666666; font-weight: 400; margin-top: 5px;">Tiga pilar utama penunjang performa transkrip akademikmu.</p>
    </div>

    <div class="grid-container">
        
        <div class="academic-card">
            <div class="card-top-content">
                <span style="font-size: 32px; margin-bottom: 15px; display: block;">📖</span>
                <h3 style="font-weight: 700; font-size: 22px; color: #1b1b1b; margin-bottom: 12px; letter-spacing: -0.3px;">Materi Terstruktur</h3>
                <p style="font-weight: 400; font-size: 14px; color: #444444; line-height: 1.6;">
                    Akses bebas bank materi kuliah dari semester 1 hingga 8 berupa rangkuman teks, video YouTube, hingga dokumen resmi PDF.
                </p>
            </div>
            <div class="card-bottom-bar">
                <span style="font-size: 13px; font-weight: 500; color: #666666;">Kurikulum Terbuka</span>
                <span style="font-size: 16px;">&rarr;</span>
            </div>
        </div>

        <div class="academic-card">
            <div class="card-top-content">
                <span style="font-size: 32px; margin-bottom: 15px; display: block;">🎯</span>
                <h3 style="font-weight: 700; font-size: 22px; color: #1b1b1b; margin-bottom: 12px; letter-spacing: -0.3px;">Evaluasi Interaktif</h3>
                <p style="font-weight: 400; font-size: 14px; color: #444444; line-height: 1.6;">
                    Uji tingkat pemahaman teorimu lewat simulasi pengerjaan latihan soal pilihan ganda yang interaktif tanpa muat ulang halaman.
                </p>
            </div>
            <div class="card-bottom-bar">
                <span style="font-size: 13px; font-weight: 500; color: #666666;">Umpan Balik Instan</span>
                <span style="font-size: 16px;">&rarr;</span>
            </div>
        </div>

        <div class="academic-card">
            <div class="card-top-content">
                <span style="font-size: 32px; margin-bottom: 15px; display: block;">🎓</span>
                <h3 style="font-weight: 700; font-size: 22px; color: #1b1b1b; margin-bottom: 12px; letter-spacing: -0.3px;">Validasi Kualitas</h3>
                <p style="font-weight: 400; font-size: 14px; color: #444444; line-height: 1.6;">
                    Dapatkan validasi penilaian tugas objektif dari para Asisten Praktikum serta raih rekapan kelulusan digitalmu.
                </p>
            </div>
            <div class="card-bottom-bar">
                <span style="font-size: 13px; font-weight: 500; color: #666666;">Sertifikasi Digital</span>
                <span style="font-size: 16px;">&rarr;</span>
            </div>
        </div>

    </div>
</div>

<?php 
// 3. Render komponen penutup HTML dan footer
require_once 'includes/footer.php'; 
?>