<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaverequest extends Model
{
    protected $table = 'leaverequests';

    const STAGE_SPV     = 'spv';
    const STAGE_MANAJER = 'manajer';
    const STAGE_HRD     = 'hrd';

    protected $fillable = [
        'karyawan_id', 'start_date', 'end_date', 'type',
        'status', 'keterangan', 'approved_by', 'approved_at',
        'spv_by', 'spv_at', 'spv_note',
        'manajer_by', 'manajer_at', 'manajer_note',
        'hrd_by', 'hrd_at', 'hrd_note',
        'rejected_stage', 'rejected_note',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'approved_at' => 'datetime',
        'spv_at'      => 'datetime',
        'manajer_at'  => 'datetime',
        'hrd_at'      => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function spvApprover()
    {
        return $this->belongsTo(User::class, 'spv_by');
    }

    public function manajerApprover()
    {
        return $this->belongsTo(User::class, 'manajer_by');
    }

    public function hrdApprover()
    {
        return $this->belongsTo(User::class, 'hrd_by');
    }

    /** Tahap yang sedang aktif ('spv'|'manajer'|'hrd'), atau null jika sudah final */
    public function currentStage(): ?string
    {
        return match ($this->status) {
            'pending_spv'     => self::STAGE_SPV,
            'pending_manajer' => self::STAGE_MANAJER,
            'pending_hrd'     => self::STAGE_HRD,
            default           => null,
        };
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['disetujui', 'ditolak']);
    }
}
