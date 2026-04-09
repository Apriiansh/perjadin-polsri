<?php

namespace App\Controllers;

use App\Models\StudentTravelCompletenessModel;
use App\Models\StudentTravelCompletenessFileModel;
use App\Models\TravelRequestModel;
use App\Models\StudentTravelMemberModel;
use App\Models\StudentModel;
use CodeIgniter\HTTP\ResponseInterface;

class StudentReviewController extends BaseController
{
    protected $completenessModel;
    protected $completenessFileModel;
    protected $travelRequestModel;
    protected $studentMemberModel;
    protected $studentModel;

    public function __construct()
    {
        $this->completenessModel     = new StudentTravelCompletenessModel();
        $this->completenessFileModel = new StudentTravelCompletenessFileModel();
        $this->travelRequestModel    = new TravelRequestModel();
        $this->studentMemberModel    = new StudentTravelMemberModel();
        $this->studentModel          = new StudentModel();
    }

    /**
     * Show documentation upload page for Students (Leader view)
     */
    public function documentation(int $id): string|ResponseInterface
    {
        $request = $this->travelRequestModel->find($id);
        if (!$request || $request->category !== 'mahasiswa') {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        // Identify the student logged in
        $student = $this->studentModel->where('user_id', auth()->id())->first();
        if (!$student && !auth()->user()->inGroup('superadmin', 'verificator')) {
            return redirect()->to('/travel/student')->with('error', 'Akses ditolak. Hanya ketua tim mahasiswa yang dapat mengakses halaman ini.');
        }

        $member = null;
        if ($student) {
            $member = $this->studentMemberModel->where('travel_request_id', $id)
                ->where('student_id', $student->id)
                ->first();
        }

        // Only representative (leader) or admin can access documentation upload
        if ($student && (!$member || !$member->is_representative)) {
            return redirect()->to('/travel/student')->with('error', 'Akses ditolak. Hanya ketua tim yang dapat mengunggah dokumentasi.');
        }

        if (!$member && auth()->user()->inGroup('superadmin', 'verificator')) {
            // Admin viewing, find the representative member to show their view
            $member = $this->studentMemberModel->where('travel_request_id', $id)
                ->where('is_representative', 1)
                ->first();
        }

        if (!$member) {
            return redirect()->to('/travel')->with('error', 'Anggota representatif tidak ditemukan.');
        }

        // Get all checklist items for this request (for student, documentation is usually group-wide)
        // But we use the member_id to track who uploaded it or for whom it was set.
        $completeness = $this->completenessModel->where('travel_request_id', $id)
            ->where('student_member_id', $member->id)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        foreach ($completeness as &$item) {
            $item->files = $this->completenessFileModel->where('completeness_id', $item->id)->findAll();
        }

        return view('travel/student/documentation', [
            'request'      => $request,
            'member'       => $member,
            'student'      => $student,
            'completeness' => $completeness,
            'title'        => 'Dokumentasi Perjadin Mahasiswa',
        ]);
    }

    /**
     * Submit documentation for Student Travel
     */
    public function submitDocumentation(int $id)
    {
        $student = $this->studentModel->where('user_id', auth()->id())->first();
        if (!$student) {
            return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        $member = $this->studentMemberModel->where('travel_request_id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$member || !$member->is_representative) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $completeness = $this->completenessModel->where('travel_request_id', $id)
            ->where('student_member_id', $member->id)
            ->findAll();

        $filesUploaded = 0;
        $path = 'completeness/student/' . $id;
        $currentUserId = auth()->id();

        foreach ($completeness as $item) {
            $files = $this->request->getFiles();
            
            if (isset($files['documents_' . $item->id])) {
                $count = $this->completenessFileModel->where('completeness_id', $item->id)->countAllResults();
                $seq = $count + 1;
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', (string)$item->item_name), '-'));

                foreach ($files['documents_' . $item->id] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $ext = strtolower($file->getExtension());
                        $allowed = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
                        if (!in_array($ext, $allowed)) continue;

                        $newName = "std-{$slug}-u{$currentUserId}-m{$member->id}-{$seq}-" . bin2hex(random_bytes(4)) . "." . $ext;
                        $file->move(WRITEPATH . 'uploads/' . $path, $newName);

                        $this->completenessFileModel->insert([
                            'completeness_id' => $item->id,
                            'file_path'       => $path . '/' . $newName,
                            'original_name'   => $item->item_name . ' - ' . $seq . '.' . $ext,
                            'file_size'       => $file->getSize(),
                            'uploaded_by'     => $currentUserId,
                        ]);

                        $seq++;
                        $filesUploaded++;
                    }
                }

                if ($seq > ($count + 1)) {
                    $this->completenessModel->update($item->id, [
                        'status' => 'uploaded',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        // Handle narrative
        $narrative = $this->request->getPost('report_narrative');
        if ($narrative !== null) {
            $this->studentMemberModel->update($member->id, ['report_narrative' => $narrative]);
        }

        if ($filesUploaded > 0 || !empty($narrative)) {
            return redirect()->to("/travel/student/{$id}")->with('success', 'Dokumentasi berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Tidak ada file yang diunggah.');
    }

    /**
     * Show verification page for Student Travel (Verificator view)
     */
    public function verification(int $id): string|ResponseInterface
    {
        if (!auth()->user()->inGroup('superadmin', 'verificator')) {
            return redirect()->to('/travel')->with('error', 'Akses ditolak.');
        }

        $request = $this->travelRequestModel->find($id);
        if (!$request || $request->category !== 'mahasiswa') {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        // For students, we group by members but documentation is usually under the representative
        $members = $this->studentMemberModel->getByRequestId($id);

        foreach ($members as &$member) {
            $member->completeness = $this->completenessModel->where('student_member_id', $member->id)->findAll();
            foreach ($member->completeness as &$item) {
                $item->files = $this->completenessFileModel->where('completeness_id', $item->id)->findAll();
            }
        }

        return view('travel/student/verification', [
            'request' => $request,
            'members' => $members,
            'title'   => 'Verifikasi Dokumentasi Perjadin Mahasiswa',
        ]);
    }

    /**
     * Verify all documents for a student travel (Bulk)
     */
    public function verifyAll(int $id, string $action = 'verify'): ResponseInterface
    {
        if (!auth()->user()->inGroup('superadmin', 'verificator')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.'])->setStatusCode(403);
        }

        if ($action === 'reject') {
            $note = $this->request->getJSON()->verification_note ?? 'Rejected all items';
            if (empty(trim((string) $note))) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Alasan penolakan wajib diisi.'])->setStatusCode(422);
            }

            $this->completenessModel->where('travel_request_id', $id)
                ->where('status !=', 'verified')
                ->set([
                    'status'            => 'rejected',
                    'verified_by'       => auth()->id(),
                    'verified_at'       => date('Y-m-d H:i:s'),
                    'verification_note' => $note
                ])
                ->update();

            return $this->response->setJSON(['status' => 'success', 'message' => 'Seluruh item berhasil ditolak.']);
        }

        $this->completenessModel->where('travel_request_id', $id)
            ->where('status !=', 'verified')
            ->set([
                'status'            => 'verified',
                'verified_by'       => auth()->id(),
                'verified_at'       => date('Y-m-d H:i:s'),
                'verification_note' => 'Verified all items'
            ])
            ->update();

        // Check if all items across request are verified
        $totalItems = $this->completenessModel->where('travel_request_id', $id)->countAllResults();
        $verifiedItems = $this->completenessModel->where('travel_request_id', $id)->where('status', 'verified')->countAllResults();

        if ($totalItems > 0 && $totalItems === $verifiedItems) {
            $this->travelRequestModel->update($id, ['status' => 'completed']);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Seluruh item berhasil diverifikasi.']);
    }

    /**
     * Delete a specific student documentation file
     */
    public function deleteFile(int $fileId)
    {
        $file = $this->completenessFileModel->find($fileId);
        if (!$file) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak ditemukan.'])->setStatusCode(404);
        }

        if (!auth()->user()->inGroup('superadmin', 'admin') && $file->uploaded_by != auth()->id()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.'])->setStatusCode(403);
        }

        $physicalPath = WRITEPATH . 'uploads/' . $file->file_path;
        if (file_exists($physicalPath)) {
            unlink($physicalPath);
        }

        $this->completenessFileModel->delete($fileId);

        return $this->response->setJSON(['status' => 'success', 'message' => 'File berhasil dihapus.']);
    }

    /**
     * View/Display file
     */
    public function viewFile(int $fileId): ResponseInterface
    {
        $file = $this->completenessFileModel->find($fileId);
        if (!$file) return $this->response->setStatusCode(404);

        $filePath = WRITEPATH . 'uploads/' . $file->file_path;
        if (!is_file($filePath)) return $this->response->setStatusCode(404);

        return $this->response
            ->setHeader('Content-Type', mime_content_type($filePath))
            ->setHeader('Content-Disposition', 'inline; filename="' . $file->original_name . '"')
            ->setBody(file_get_contents($filePath));
    }
}
