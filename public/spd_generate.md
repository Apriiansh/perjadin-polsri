# Dokumentasi Teknis Pembuatan Dokumen SPPD

Dokumen ini menjelaskan struktur file dan cuplikan kode yang digunakan untuk menghasilkan output **SPPD (Surat Perjalanan Dinas)** dalam format **PDF** dan **Word (.docx)** pada modul Perjadin PolsriPay.

## 📂 Struktur Folder & File Terkait

Berikut adalah file-file utama yang terlibat dalam proses ini:

```text
perjadin/
├── app/
│   ├── Controllers/
│   │   └── TravelRequestController.php       # Controller utama (Logic download)
│   ├── Libraries/
│   │   └── Templates/
│   │       ├── SppdTemplate.php              # Library generate Word (.docx)
│   │       └── SppdPdfTemplate.php           # Library generate PDF (.pdf)
│   ├── Models/
│   │   ├── TravelRequestModel.php            # Tabel: travel_requests
│   │   ├── TravelMemberModel.php             # Tabel: travel_members
│   │   ├── TravelExpenseModel.php            # Tabel: travel_expenses
│   │   ├── EmployeeModel.php                 # Tabel: employees
│   │   └── SignatoriesModel.php              # Tabel: signatories
│   └── Views/
│       └── travel/
│           └── pdf/
│               └── sppd_pdf.php              # Template layout HTML untuk PDF
```

---

## 🛠️ Potongan Kode (Snippets)

### 1. Controller: TravelRequestController.php
Bagian ini adalah entry point saat user mengklik tombol "Download SPPD".

```php
// Lokasi: app/Controllers/TravelRequestController.php (Method: downloadSpd)

public function downloadSpd(int $id): ResponseInterface
{
    $travelRequest = $this->travelRequestModel->find($id);
    $members = $this->travelExpenseModel->getByRequestWithMember($id);
    
    // Resolving PPK (Penandatangan)
    $ppk = $this->resolveSignatory((string) $travelRequest->ppk_id);

    // Cek format (PDF atau Word)
    if ($this->request->getGet('format') === 'pdf') {
        (new \App\Libraries\Templates\SppdPdfTemplate())
            ->generate($travelRequest, $members, $ppk, $specificMemberId, $showBackPage);
    } else {
        (new \App\Libraries\Templates\SppdTemplate())
            ->generate($travelRequest, $members, $ppk, $specificMemberId, $showBackPage);
    }
    exit;
}
```

### 2. Library PDF: SppdPdfTemplate.php
Library ini menggunakan `Dompdf` untuk merender view menjadi PDF.

```php
// Lokasi: app/Libraries/Templates/SppdPdfTemplate.php

public function generate(object $travelRequest, array $members, ?object $ppk = null, ...): void
{
    // ... Persiapan data ...
    $data = [
        'travelRequest' => $travelRequest,
        'members'       => $members,
        'ppk'           => $ppk,
    ];

    // Render HTML dari View
    $html = view('travel/pdf/sppd_pdf', $data);

    // Konversi ke PDF menggunakan Dompdf
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("SPD_{$travelRequest->id}.pdf");
    exit;
}
```

### 3. Library Word: SppdTemplate.php
Menggunakan `PhpWord` untuk membuat dokumen dari nol (bukan dari HTML).

```php
// Lokasi: app/Libraries/Templates/SppdTemplate.php

public function generate(object $travelRequest, array $members, ...): void
{
    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection($sectionStyle);

    // Tambah Header
    $section->addText('SURAT PERJALANAN DINAS (SPD)', $boldCenter);

    // Buat Tabel Isi SPPD
    $table = $section->addTable();
    $table->addRow();
    $table->addCell(500)->addText('1');
    $table->addCell(4000)->addText('Pejabat Pembuat Komitmen');
    $table->addCell(5000)->addText($ppk->employee_name);

    // ... loop member dan simpan ke output stream ...
}
```

### 4. View PDF: sppd_pdf.php
Layout HTML yang dihias dengan CSS `@page` dan tabel standar.

```html
<!-- Lokasi: app/Views/travel/pdf/sppd_pdf.php -->

<style>
    @page { margin: 1.5cm 2.0cm 1.5cm 2.5cm; }
    .spd-table { width: 100%; border-collapse: collapse; border: 1px solid black; }
    /* CSS Styling lainnya */
</style>

<body>
    <?php foreach ($members as $member): ?>
        <div class="document-page">
            <div class="spd-title">SURAT PERJALANAN DINAS (SPD)</div>
            <table class="spd-table">
                <tr>
                    <td>1</td>
                    <td>Pejabat Pembuat Komitmen</td>
                    <td><?= esc($ppk->employee_name) ?></td>
                </tr>
                <!-- Row lainnya -->
            </table>
        </div>
    <?php endforeach; ?>
</body>
```

---

## 🗄️ Database Schema (Model)

| Model | Tabel Utama | Primary Key | Relasi Penting |
| :--- | :--- | :--- | :--- |
| `TravelRequestModel` | `travel_requests` | `id` | Relasi ke `signatories` (PPK) |
| `TravelMemberModel` | `travel_members` | `id` | Relasi ke `travel_requests` & `employees` |
| `TravelExpenseModel` | `travel_expenses` | `id` | Relasi ke `travel_members` |

---
