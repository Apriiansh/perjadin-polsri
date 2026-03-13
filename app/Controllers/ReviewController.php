<?php

namespace App\Controllers;

use App\Models\TravelCompletenessModel;
use App\Models\TravelCompletenessFileModel;
use App\Models\TravelRequestModel;
use CodeIgniter\HTTP\ResponseInterface;

class ReviewController extends BaseController
{
    protected $completenessModel;
    protected $travelRequestModel;
    protected $completenessFileModel;

    public function __construct()
    {
        $this->completenessModel = new TravelCompletenessModel();
        $this->travelRequestModel = new TravelRequestModel();
        $this->completenessFileModel = new TravelCompletenessFileModel();
    }

    /**
     * Handle document upload from Dosen
     */
    public function upload(int $completenessId): ResponseInterface
    {
        $item = $this->completenessModel->find($completenessId);
        if (!$item) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Item tidak ditemukan.'])->setStatusCode(404);
        }

        // Check if user is creator or lecturer
        $travel = $this->travelRequestModel->find($item->travel_request_id);
        if (!$travel) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data perjalanan tidak ditemukan.'])->setStatusCode(404);
        }

        if (!auth()->user()->inGroup('superadmin', 'admin', 'verificator') && $travel['created_by'] != auth()->id()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.'])->setStatusCode(403);
        }

        $file = $this->request->getFile('document');

        if (!$file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => $file->getErrorString()])->setStatusCode(400);
        }

        if (!$file->hasMoved()) {
            $path = 'completeness/' . $item->travel_request_id;
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/' . $path, $newName);

            $data = [
                'document_path' => $path . '/' . $newName,
                'original_name' => $file->getClientName(),
                'file_size'     => $file->getSize(),
                'uploaded_by'   => auth()->id(),
                'uploaded_at'   => date('Y-m-d H:i:s'),
                'status'        => 'uploaded'
            ];

            $this->completenessModel->update($completenessId, $data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Dokumen berhasil diunggah.'
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengunggah file.'])->setStatusCode(500);
    }

    /**
     * Download or view the uploaded document
     */
    public function download(int $completenessId)
    {
        $item = $this->completenessModel->find($completenessId);
        if (!$item || empty($item->document_path)) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        $path = WRITEPATH . 'uploads/' . $item->document_path;
        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'File fisik tidak ditemukan.');
        }

        return $this->response->download($path, null)->setFileName($item->original_name);
    }

    /**
     * Verify document (Phase 9)
     */
    public function verify(int $completenessId): ResponseInterface
    {
        if (!auth()->user()->inGroup('superadmin', 'verificator')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Hanya Verifikator/Keuangan yang dapat memverifikasi.'])->setStatusCode(403);
        }

        $json = $this->request->getJSON();
        $status = $json->status ?? $this->request->getPost('status');
        $note   = $json->verification_note ?? $this->request->getPost('verification_note');

        $data = [
            'status'            => $status,
            'verified_by'       => auth()->id(),
            'verified_at'       => date('Y-m-d H:i:s'),
            'verification_note' => $note
        ];

        $this->completenessModel->update($completenessId, $data);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Status berhasil diupdate.'
        ]);
    }

    /**
     * Show documentation upload page (Phase 12)
     */
    /**
     * Show consolidated documentation for a travel request (Lecturer view)
     */
    public function documentation($id): string|ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        $completeness = $this->completenessModel->getByRequestWithFiles($id);

        return view('travel/documentation', [
            'title'         => 'Kelengkapan Dokumentasi',
            'travelRequest' => $travelRequest,
            'completeness'  => $completeness,
        ]);
    }

    /**
     * Show consolidated verification page (Verificator view)
     */
    public function verification($id): string|ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        // Only superadmin/verificator can access
        if (!auth()->user()->inGroup('superadmin', 'verificator')) {
            return redirect()->to('/travel')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $completeness = $this->completenessModel->getByRequestWithFiles($id);

        return view('travel/verification', [
            'title'         => 'Verifikasi Dokumentasi Perdin',
            'travelRequest' => $travelRequest,
            'completeness'  => $completeness,
        ]);
    }

    /**
     * Submit documentation (Multi-file)
     */
    public function submitDocumentation(int $id)
    {
        $completeness = $this->completenessModel->getByRequestId($id);

        $filesUploaded = 0;
        $path = 'completeness/' . $id;

        foreach ($completeness as $item) {
            $files = $this->request->getFiles();

            // Check if there are files for this specific item (using item ID as input name)
            if (isset($files['documents_' . $item->id])) {
                // Get existing count for numbering (Phase 18)
                $count = $this->completenessFileModel->where('completeness_id', $item->id)->countAllResults();
                $seq = $count + 1;

                // Create slug from item name
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $item->item_name), '-'));

                foreach ($files['documents_' . $item->id] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        // Validation (Phase 16)
                        $ext = strtolower($file->getExtension());
                        $allowed = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
                        if (!in_array($ext, $allowed)) {
                            continue; // Skip invalid files
                        }

                        $ext = $file->getExtension();
                        $prettyName = $item->item_name . ' - ' . $seq . '.' . $ext;
                        $newName = $slug . '-' . $seq . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $file->move(WRITEPATH . 'uploads/' . $path, $newName);

                        $this->completenessFileModel->insert([
                            'completeness_id' => $item->id,
                            'file_path'       => $path . '/' . $newName,
                            'original_name'   => $prettyName,
                            'file_size'       => $file->getSize(),
                            'uploaded_by'     => auth()->id(),
                        ]);

                        $seq++;
                        $filesUploaded++;
                    }
                }

                // If at least one file uploaded, update item status to 'uploaded'
                if ($item->status === 'pending') {
                    $this->completenessModel->update($item->id, ['status' => 'uploaded']);
                }
            }
        }

        if ($filesUploaded > 0) {
            return redirect()->to(base_url('travel/' . $id))->with('success', $filesUploaded . ' file berhasil diunggah.');
        }

        return redirect()->to(base_url('travel/' . $id))->with('error', 'Tidak ada file yang diunggah atau file tidak valid.');
    }

    /**
     * Delete a specific file
     */
    public function deleteFile(int $fileId)
    {
        $file = $this->completenessFileModel->find($fileId);
        if (!$file) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak ditemukan.'])->setStatusCode(404);
        }

        // Check permission (uploaded_by or admin)
        if (!auth()->user()->inGroup('superadmin', 'admin') && $file->uploaded_by != auth()->id()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.'])->setStatusCode(403);
        }

        // Drop physical file
        $physicalPath = WRITEPATH . 'uploads/' . $file->file_path;
        if (file_exists($physicalPath)) {
            unlink($physicalPath);
        }

        $this->completenessFileModel->delete($fileId);

        return $this->response->setJSON(['status' => 'success', 'message' => 'File berhasil dihapus.']);
    }

    /**
     * View specific documentation file inline (Phase 15)
     */
    public function viewFile(int $fileId): ResponseInterface
    {
        $file = $this->completenessFileModel->find($fileId);
        if (!$file) {
            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan.');
        }

        $filePath = WRITEPATH . 'uploads/' . $file->file_path;
        if (!is_file($filePath)) {
            return $this->response->setStatusCode(404)->setBody('File fisik tidak ditemukan.');
        }

        // Get mime type for better preview support
        $mimeType = mime_content_type($filePath);

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . $file->original_name . '"')
            ->setBody(file_get_contents($filePath));
    }

    /**
     * Download specific documentation file (Phase 16 fallback)
     */
    public function downloadFile(int $fileId): ResponseInterface
    {
        $file = $this->completenessFileModel->find($fileId);
        if (!$file) {
            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan.');
        }

        $filePath = WRITEPATH . 'uploads/' . $file->file_path;
        if (!is_file($filePath)) {
            return $this->response->setStatusCode(404)->setBody('File fisik tidak ditemukan.');
        }

        return $this->response->download($filePath, null)->setFileName($file->original_name);
    }

    /**
     * Verify all documents for a travel request (Phase 20)
     */
    public function verifyAll(int $id): ResponseInterface
    {
        if (!auth()->user()->inGroup('superadmin', 'verificator')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Hanya Verifikator/Keuangan yang dapat memverifikasi.'])->setStatusCode(403);
        }

        $this->completenessModel->where('travel_request_id', $id)
            ->where('status !=', 'verified')
            ->set([
                'status'            => 'verified',
                'verified_by'       => auth()->id(),
                'verified_at'       => date('Y-m-d H:i:s'),
                'verification_note' => 'Verified all items at once'
            ])
            ->update();

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Seluruh item berhasil diverifikasi.'
        ]);
    }

    /**
     * Reject all documents for a travel request (Phase 21)
     */
    public function rejectAll(int $id): ResponseInterface
    {
        if (!auth()->user()->inGroup('superadmin', 'verificator')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Hanya Verifikator/Keuangan yang dapat menolak.'])->setStatusCode(403);
        }

        $note = $this->request->getJSON()->verification_note ?? 'Rejected all items at once';
        if (empty($note)) $note = 'Rejected all items at once';

        $this->completenessModel->where('travel_request_id', $id)
            ->where('status !=', 'verified')
            ->set([
                'status'            => 'rejected',
                'verified_by'       => auth()->id(),
                'verified_at'       => date('Y-m-d H:i:s'),
                'verification_note' => $note
            ])
            ->update();

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Seluruh item berhasil ditolak.'
        ]);
    }
}
