<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixSharedDocumentation extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Find all shared completeness items (member_id IS NULL)
        $sharedItems = $db->table('travel_completeness')->where('member_id', null)->get()->getResult();

        foreach ($sharedItems as $item) {
            $requestId = $item->travel_request_id;

            // 2. Find all members of this travel request
            $members = $db->table('travel_members')->where('travel_request_id', $requestId)->get()->getResult();

            if (empty($members)) continue;

            // 3. Find files associated with this shared item
            $files = $db->table('travel_completeness_files')->where('completeness_id', $item->id)->get()->getResult();

            $newCompletenessIds = [];

            // 4. Create a specific completeness item for each member
            foreach ($members as $member) {
                $db->table('travel_completeness')->insert([
                    'travel_request_id' => $requestId,
                    'member_id' => $member->id,
                    'item_name' => $item->item_name,
                    'payment_method' => $item->payment_method,
                    'status' => 'pending', // Default to pending, will update if files exist
                    'created_at' => $item->created_at,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $newId = $db->insertID();
                $newCompletenessIds[$member->id] = $newId;
            }

            // 5. Re-attribute files to the correct member item
            foreach ($files as $file) {
                // Find which member this user (uploaded_by) belongs to
                $employee = $db->table('employees')->where('user_id', $file->uploaded_by)->get()->getRow();

                if ($employee) {
                    $matchingMember = $db->table('travel_members')
                        ->where('travel_request_id', $requestId)
                        ->where('employee_id', $employee->id)
                        ->get()->getRow();

                    if ($matchingMember && isset($newCompletenessIds[$matchingMember->id])) {
                        $targetId = $newCompletenessIds[$matchingMember->id];
                        $db->table('travel_completeness_files')->where('id', $file->id)->update([
                            'completeness_id' => $targetId
                        ]);

                        // Update the status of the new item to 'uploaded'
                        $db->table('travel_completeness')->where('id', $targetId)->update([
                            'status' => 'uploaded'
                        ]);
                    }
                }
            }

            // 6. Delete the old shared item
            $db->table('travel_completeness')->where('id', $item->id)->delete();
        }
    }

    public function down()
    {
        // Data conversions are hard to reverse perfectly without keeping a mapping table.
        // For now, we don't reverse it because the NULL member state was considered a bug.
    }
}
