<?php

namespace App\Support;

class FmkStageFlow
{
    /** Urutan tahap approval FMK: status pending saat ini => [tahap, field approver di department, status berikutnya] */
    const MAP = [
        'pending_spv'     => ['stage' => 'spv',     'field' => 'spv_user_id',     'next' => 'pending_manajer'],
        'pending_manajer' => ['stage' => 'manajer', 'field' => 'manager_user_id', 'next' => 'pending_hrd'],
        'pending_hrd'     => ['stage' => 'hrd',     'field' => null,              'next' => 'disetujui'],
    ];
}
