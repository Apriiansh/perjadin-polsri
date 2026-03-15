<?php

namespace App\Controllers;

use App\Models\TravelCompletenessModel;
use App\Models\TravelCompletenessFileModel;
use App\Models\TravelRequestModel;
use App\Models\TravelMemberModel;
use CodeIgniter\HTTP\ResponseInterface;

class ReviewController extends BaseController
{
    protected $completenessModel;
    protected $travelRequestModel;
    protected $completenessFileModel;
    protected $travelMemberModel;

    public function __construct()
    {
        $this->completenessModel = new TravelCompletenessModel();
        $this->travelRequestModel = new TravelRequestModel();
        $this->completenessFileModel = new TravelCompletenessFileModel();
        $this->travelMemberModel = new TravelMemberModel();
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

        // Check for completion
        $item = $this->completenessModel->find($completenessId);
        $this->checkAndCompleteTravel($item->travel_request_id);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Status berhasil diupdate.'
        ]);
    }

    /**
     * Show consolidated documentation for a travel request (Lecturer view - Phase 28 Individual)
     */
    public function documentation($id): string|ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        // Find the specific member record for the logged-in user
        $employeeModel = new \App\Models\EmployeeModel();
        $employee = $employeeModel->where('user_id', auth()->id())->first();

        if (!$employee && !auth()->user()->inGroup('superadmin', 'verificator')) {
            return redirect()->to('/travel')->with('error', 'Data pegawai Anda tidak ditemukan.');
        }

        $member = null;
        if ($employee) {
            $member = $this->travelMemberModel->where('travel_request_id', $id)
                ->where('employee_id', $employee['id'])
                ->first();
        }

        // Only superadmin/verificator or the member themselves can access
        if (!$member && !auth()->user()->inGroup('superadmin', 'verificator')) {
            return redirect()->to('/travel')->with('error', 'Anda tidak terdaftar sebagai anggota dalam perjalanan ini.');
        }

        // If Superadmin/Verificator is NOT a member, they'll see the first member's data by default?
        // Actually, we should handle if $member is NULL (e.g. Verificator viewing but not a member)
        if (!$member && auth()->user()->inGroup('superadmin', 'verificator')) {
            // For now, redirect to verification page as it's the right place for them
            return redirect()->to(base_url('documentation/' . $id . '/verification'));
        }

        $currentUserId = auth()->id();

        // Filter completeness by member
        $query = $this->completenessModel->where('travel_request_id', $id);
        $query->groupStart()
            ->where('member_id', $member->id)
            ->orWhere('member_id', null)
            ->groupEnd();

        $completeness = $query->orderBy('created_at', 'ASC')->findAll();

        // Load files for each item - STRICT FILTERING
        foreach ($completeness as &$item) {
            $fileQuery = $this->completenessFileModel->where('completeness_id', $item->id);

            // If it's a global/legacy item (member_id IS NULL), only show files UPLOADED BY THIS USER
            if ($item->member_id === null) {
                $fileQuery->where('uploaded_by', $currentUserId);
            }
            // For items specifically assigned to this member ($member->id), 
            // we show all files for that item (as it's their private checklist item).

            $item->files = $fileQuery->findAll();
        }

        return view('travel/documentation', [
            'title'         => 'Kelengkapan Dokumentasi',
            'travelRequest' => $travelRequest,
            'member'        => $member,
            'completeness'  => $completeness,
        ]);
    }

    /**
     * Submit documentation (Multi-file - Phase 28 Individual)
     */
    public function submitDocumentation(int $id)
    {
        // Find the specific member record for the logged-in user
        $employeeModel = new \App\Models\EmployeeModel();
        $employee = $employeeModel->where('user_id', auth()->id())->first();

        if (!$employee) {
            return redirect()->to(base_url('travel/' . $id))->with('error', 'Data pegawai Anda tidak ditemukan.');
        }

        $member = $this->travelMemberModel->where('travel_request_id', $id)
            ->where('employee_id', $employee['id'])
            ->first();

        if (!$member) {
            return redirect()->to(base_url('travel/' . $id))->with('error', 'Anda tidak terdaftar sebagai anggota dalam perjalanan ini.');
        }

        // Double check permissions: item belongs to member or is global
        $completeness = $this->completenessModel->where('travel_request_id', $id)
            ->groupStart()
            ->where('member_id', $member->id)
            ->orWhere('member_id', null)
            ->groupEnd()
            ->findAll();

        $filesUploaded = 0;
        $path = 'completeness/' . $id;
        $currentUserId = auth()->id();

        foreach ($completeness as $item) {
            $files = $this->request->getFiles();
            $filesUploadedForItem = 0;

            if (isset($files['documents_' . $item->id])) {
                $count = $this->completenessFileModel->where('completeness_id', $item->id)->countAllResults();
                $seq = $count + 1;
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', (string)$item->item_name), '-'));

                foreach ($files['documents_' . $item->id] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $ext = strtolower($file->getExtension());
                        $allowed = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
                        if (!in_array($ext, $allowed)) continue;

                        $prettyName = $item->item_name . ' - ' . $seq . '.' . $ext;
                        // Include user_id and member_id in filename for uniqueness & security
                        $newName = $slug . '-user' . $currentUserId . '-mbr' . $member->id . '-' . $seq . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $file->move(WRITEPATH . 'uploads/' . $path, $newName);

                        $this->completenessFileModel->insert([
                            'completeness_id' => $item->id,
                            'file_path'       => $path . '/' . $newName,
                            'original_name'   => $prettyName,
                            'file_size'       => $file->getSize(),
                            'uploaded_by'     => $currentUserId,
                        ]);

                        $seq++;
                        $filesUploadedForItem++;
                    }
                }

                if ($filesUploadedForItem > 0) {
                    $this->completenessModel->update($item->id, [
                        'status' => 'uploaded',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $filesUploaded += $filesUploadedForItem;
                }
            }
        }

        // Handle narrative field (SAVE TO MEMBER RECORD - Phase 28)
        $narrative = $this->request->getPost('report_narrative');
        if ($narrative !== null) {
            // Ensure we update strictly the member record of the current user
            $this->travelMemberModel->update($member->id, ['report_narrative' => $narrative]);
        }

        if ($filesUploaded > 0 || ($narrative !== null && !empty($narrative))) {
            return redirect()->to(base_url('travel/' . $id))->with('success', 'Dokumentasi berhasil diperbarui.');
        }

        return redirect()->to(base_url('travel/' . $id))->with('error', 'Tidak ada perubahan yang disimpan.');
    }

    /**
     * Show consolidated verification page (Verificator view - Phase 28 Grouped)
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

        // Get all members for this request
        $members = $this->travelMemberModel
            ->select('travel_members.id as id, travel_members.travel_request_id, travel_members.employee_id, travel_members.report_narrative, employees.name as employee_name, employees.nip, employees.user_id')
            ->join('employees', 'employees.id = travel_members.employee_id')
            ->where('travel_request_id', $id)
            ->findAll();

        // Group completeness items by member
        foreach ($members as &$member) {
            $memberUserid = $member->user_id ?? -1; // Use -1 if user_id is NULL to avoid accidental matches

            $member->completeness = $this->completenessModel
                ->where('travel_request_id', $id)
                ->groupStart()
                ->where('member_id', $member->id)
                ->orGroupStart()
                ->where('member_id', null)
                ->groupStart()
                ->where('uploaded_by', $memberUserid)
                ->orWhereIn('id', function ($builder) use ($memberUserid) {
                    return $builder->select('completeness_id')
                        ->from('travel_completeness_files')
                        ->where('uploaded_by', $memberUserid);
                })
                ->groupEnd()
                ->groupEnd()
                ->groupEnd()
                ->orderBy('created_at', 'ASC')
                ->findAll();

            foreach ($member->completeness as &$item) {
                $fileQuery = $this->completenessFileModel->where('completeness_id', $item->id);

                // If it is a global item (member_id is NULL), only show files uploaded by THIS member
                if ($item->member_id === null) {
                    $fileQuery->where('uploaded_by', $memberUserid);
                }

                $item->files = $fileQuery->findAll();
            }
        }

        return view('travel/verification', [
            'title'         => 'Verifikasi Dokumentasi Perdin',
            'travelRequest' => $travelRequest,
            'members'       => $members, // Pass grouped members
        ]);
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

        $this->checkAndCompleteTravel($id);

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

    /**
     * Verify all documents for ONE member (Phase 31)
     */
    public function verifyMember(int $memberId): ResponseInterface
    {
        if (!auth()->user()->inGroup('superadmin', 'verificator')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Hanya Verifikator/Keuangan yang dapat memverifikasi.'])->setStatusCode(403);
        }

        $member = $this->travelMemberModel
            ->select('travel_members.*, employees.user_id')
            ->join('employees', 'employees.id = travel_members.employee_id')
            ->find($memberId);

        if (!$member) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Member tidak ditemukan.'])->setStatusCode(404);
        }

        $userId = $member->user_id ?? -1;

        // Update items: specific to member OR global and uploaded by member
        $this->completenessModel
            ->where('travel_request_id', $member->travel_request_id)
            ->groupStart()
            ->where('member_id', $memberId)
            ->orGroupStart()
            ->where('member_id', null)
            ->groupStart()
            ->where('uploaded_by', $userId)
            ->orWhereIn('id', function ($builder) use ($userId) {
                return $builder->select('completeness_id')
                    ->from('travel_completeness_files')
                    ->where('uploaded_by', $userId);
            })
            ->groupEnd()
            ->groupEnd()
            ->groupEnd()
            ->where('status !=', 'verified')
            ->set([
                'status'            => 'verified',
                'verified_by'       => auth()->id(),
                'verified_at'       => date('Y-m-d H:i:s'),
                'verification_note' => 'Verified member documents bulk'
            ])
            ->update();

        $this->checkAndCompleteTravel($member->travel_request_id);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Seluruh item anggota berhasil diverifikasi.'
        ]);
    }

    /**
     * Reject all documents for ONE member (Phase 31)
     */
    public function rejectMember(int $memberId): ResponseInterface
    {
        if (!auth()->user()->inGroup('superadmin', 'verificator')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Hanya Verifikator/Keuangan yang dapat menolak.'])->setStatusCode(403);
        }

        $member = $this->travelMemberModel
            ->select('travel_members.*, employees.user_id')
            ->join('employees', 'employees.id = travel_members.employee_id')
            ->find($memberId);

        if (!$member) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Member tidak ditemukan.'])->setStatusCode(404);
        }

        $userId = $member->user_id ?? -1;
        $json = $this->request->getJSON();
        $note = $json->verification_note ?? 'Rejected member documents bulk';
        if (empty($note)) $note = 'Rejected member documents bulk';

        // Update items: specific to member OR global and uploaded by member
        $this->completenessModel
            ->where('travel_request_id', $member->travel_request_id)
            ->groupStart()
            ->where('member_id', $memberId)
            ->orGroupStart()
            ->where('member_id', null)
            ->groupStart()
            ->where('uploaded_by', $userId)
            ->orWhereIn('id', function ($builder) use ($userId) {
                return $builder->select('completeness_id')
                    ->from('travel_completeness_files')
                    ->where('uploaded_by', $userId);
            })
            ->groupEnd()
            ->groupEnd()
            ->groupEnd()
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
            'message' => 'Seluruh item anggota berhasil ditolak.'
        ]);
    }

    /**
     * Check if all items in a travel request are verified and complete the request
     */
    private function checkAndCompleteTravel(int $travelRequestId)
    {
        // Count all checklist items for this travel request
        $totalItems = $this->completenessModel->where('travel_request_id', $travelRequestId)->countAllResults();
        
        // Count only verified items
        $verifiedItems = $this->completenessModel->where('travel_request_id', $travelRequestId)
            ->where('status', 'verified')
            ->countAllResults();

        // If everything is verified, set travel request status to 'completed'
        if ($totalItems > 0 && $totalItems === $verifiedItems) {
            $this->travelRequestModel->update($travelRequestId, [
                'status'     => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
