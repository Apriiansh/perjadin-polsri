# Dokumentasi Teknis Pembuatan ZIP Bundle SPJ

Dokumen ini menjelaskan alur kerja dan cuplikan kode yang digunakan untuk menggabungkan seluruh dokumen pertanggungjawaban (SPJ) dan lampiran ke dalam satu file **ZIP**.

## 📂 Alur Kerja ZIP Generation

Proses ini terjadi di **ReportController** dengan urutan sebagai berikut:
1.  Inisialisasi class `ZipArchive` bawaan PHP.
2.  Pembuatan file ZIP sementara di folder `writable/uploads/`.
3.  Iterasi setiap anggota untuk menghasilkan PDF SPD, Rincian, dan Pernyataan secara dinamis (on-the-fly).
4.  Penambahan file fisik (lampiran gambar/PDF) dari storage server ke dalam ZIP.
5.  Streaming file ZIP ke user dan penghapusan file sementara di server.

---

## 🛠️ Potongan Kode (Snippets)

### 1. Controller: ReportController.php
Method ini menangani pembuatan struktur folder di dalam ZIP.

```php
// Lokasi: app/Controllers/Admin/ReportController.php (Method: downloadSpjBundle)

public function downloadSpjBundle(int $id)
{
    $zip = new ZipArchive();
    $zipFileName = 'SPJ_Bundle_' . $id . '_' . date('YmdHis') . '.zip';
    $zipPath = WRITEPATH . 'uploads/' . $zipFileName;

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        
        // ── A. DOKUMEN INDIVIDU (PER ANGGOTA) ──
        foreach ($members as $member) {
            $memberFolder = $member->employee_name . '/';

            // 1. Tambah PDF hasil generate (SPD, Rincian, dll)
            $sppdContent = $spjTemplate->generateSppd($travelRequest, $member, $ppk);
            $zip->addFromString($memberFolder . '1_SPD.pdf', $sppdContent);

            // 2. Tambah File Lampiran Fisik (Bukti Pengeluaran)
            $files = $db->table('travel_completeness_files')->where('completeness_id', $item->id)->get()->getResult();
            foreach ($files as $file) {
                $filePath = WRITEPATH . 'uploads/' . $file->file_path;
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $memberFolder . 'Dokumentasi/' . $file->original_name);
                }
            }
        }

        // ── B. DOKUMEN KOLEKTIF (LAPORAN REKAP) ──
        $zip->addFromString('Laporan/1_Daftar_Kontrol.pdf', $kontrolContent);

        $zip->close();

        // ── C. STREAM KE BROWSER ──
        return $this->response->download($zipPath, null)->setFileName($zipFileName);
    }
}
```

### 2. Library Pendukung: SpjPdfTemplate.php
Library ini berbeda dengan `SppdPdfTemplate` karena ia mengembalikan **string konten PDF** (bukan langsung streaming) agar bisa dimasukkan ke dalam ZIP.

```php
// Lokasi: app/Libraries/Templates/SpjPdfTemplate.php

public function generateSppd($travelRequest, $member, $ppk)
{
    $html = view('travel/pdf/sppd_pdf', ['member' => $member, ...]);
    
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->render();
    
    return $dompdf->output(); // Mengembalikan data biner PDF
}
```

---

## 📁 Struktur Isi File ZIP
Hasil download akan memiliki struktur folder seperti berikut:

```text
SPJ_Bundle_123.zip
├── Nama_Pegawai_A/
│   ├── 1_SPD_Nama_Pegawai_A.pdf
│   ├── 2_Rincian_Biaya_Kuitansi.pdf
│   ├── 3_Surat_Pernyataan.pdf
│   └── Dokumentasi/
│       ├── Tiket_Pesawat/
│       │   └── e-ticket.pdf
│       └── Hotel/
│           └── invoice_hotel.jpg
├── Nama_Pegawai_B/
│   └── ...
└── Laporan/
    ├── 1_Daftar_Kontrol_Pembayaran.pdf
    └── 2_Daftar_Nominatif.pdf
```

---
> [!IMPORTANT]
> Pastikan ekstensi PHP `zip` sudah aktif di server agar class `ZipArchive` dapat digunakan.
