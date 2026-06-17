// =========================================================================
// VALIDASI CLIENT-SIDE & INTERAKSI FORM LMS SUKSES
// =========================================================================
document.addEventListener('DOMContentLoaded', function () {
  // 1. Validasi Form Tambah/Edit Modul Materi (Adaptasi admin_add.php)
  const formTambahMateri = document.getElementById('formTambahMateri');
  if (formTambahMateri) {
    formTambahMateri.addEventListener('submit', function (e) {
      let valid = true;

      // Validasi Kolom Judul Modul
      const judul = document.getElementById('judul_materi');
      const errorJudul = document.getElementById('errorJudul');
      if (judul && judul.value.trim() === '') {
        errorJudul.textContent = 'Judul modul pembelajaran wajib diisi.';
        errorJudul.classList.remove('d-none');
        judul.classList.add('is-invalid');
        valid = false;
      } else if (judul) {
        errorJudul.classList.add('d-none');
        judul.classList.remove('is-invalid');
      }

      // Validasi Pilihan Mata Kuliah
      const selectMatkul = document.getElementById('matkul_id');
      const errorMatkul = document.getElementById('errorMatkul');
      if (selectMatkul && selectMatkul.value === '') {
        errorMatkul.textContent = 'Anda wajib memilih salah satu mata kuliah aktif.';
        errorMatkul.classList.remove('d-none');
        selectMatkul.classList.add('is-invalid');
        valid = false;
      } else if (selectMatkul) {
        errorMatkul.classList.add('d-none');
        selectMatkul.classList.remove('is-invalid');
      }

      // Jika ada field yang kosong, gagalkan pengiriman data ke PHP
      if (!valid) e.preventDefault();
    });
  }

  // 2. Pop-Up Konfirmasi Hapus Data Global (Adaptasi admin_manage.php)
  // Cukup tambahkan class="btn-hapus" dan data-nama="Nama Modul" pada tombol hapus kamu
  document.querySelectorAll('.btn-hapus').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      const namaItem = this.dataset.nama || 'item ini';
      if (!confirm('Yakin ingin menghapus ' + namaItem + '?\nData kurikulum yang terhapus tidak dapat dikembalikan.')) {
        e.preventDefault();
      }
    });
  });

  // 3. Preview File PDF / Gambar Tugas (Optional jika nanti LMS ada upload berkas)
  const inputBerkas = document.getElementById('file_pdf_upload');
  const previewArea = document.getElementById('previewKontenBerkas');
  if (inputBerkas && previewArea) {
    inputBerkas.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          previewArea.src = e.target.result;
          previewArea.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
      }
    });
  }
});
