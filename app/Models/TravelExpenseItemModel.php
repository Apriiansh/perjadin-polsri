<?php

namespace App\Models;

use CodeIgniter\Model;

class TravelExpenseItemModel extends Model
{
    protected $table            = 'travel_expense_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'travel_member_id',
        'category',
        'item_name',
        'amount',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'travel_member_id' => 'required|integer',
        'category'         => 'required|in_list[tiket,penginapan,transport_darat,transport_lokal,lain-lain]',
        'item_name'        => 'required|min_length[3]|max_length[255]',
        'amount'           => 'required|numeric',
    ];

    /**
     * Get items for a specific member
     */
    public function getByMember(int $memberId): array
    {
        return $this->where('travel_member_id', $memberId)
            ->orderBy('category', 'ASC')
            ->findAll();
    }

    /**
     * Get items for a travel request via member join
     */
    public function getByRequest(int $requestId): array
    {
        return $this->select('travel_expense_items.*, travel_members.travel_request_id')
            ->join('travel_members', 'travel_members.id = travel_expense_items.travel_member_id')
            ->where('travel_members.travel_request_id', $requestId)
            ->findAll();
    }
}
