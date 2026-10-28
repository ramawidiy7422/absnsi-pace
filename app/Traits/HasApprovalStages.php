<?php

namespace App\Traits;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait HasApprovalStages
{
    /**
     * Status awal, otomatis skip tahap yang approver-nya (field di Department) belum diisi admin.
     * $stageFlow: ['pending_spv' => ['field' => 'spv_user_id', 'next' => 'pending_manajer'], ...]
     */
    protected function tentukanStatusAwalBerjenjang(string $statusAwal, ?Karyawan $karyawan, array $stageFlow): string
    {
        return $this->skipJikaKosongBerjenjang($statusAwal, $karyawan, $stageFlow);
    }

    protected function skipJikaKosongBerjenjang(string $status, ?Karyawan $karyawan, array $stageFlow): string
    {
        $dept = $karyawan?->department;
        while (isset($stageFlow[$status])) {
            $field = $stageFlow[$status]['field'];
            if ($field === null) break;
            if ($dept && $dept->{$field}) break;
            $status = $stageFlow[$status]['next'];
        }
        return $status;
    }

    /**
     * Catat audit tahap saat ini dan tentukan status berikutnya (skip otomatis jika approver kosong).
     * Tidak menjalankan efek final — itu tetap tanggung jawab controller pemanggil.
     */
    protected function approveSatuTahapBerjenjang(Model $model, string $currentStage, User $user, ?string $note, array $stageFlow): string
    {
        $model->update([
            "{$currentStage}_by"   => $user->id,
            "{$currentStage}_at"   => now(),
            "{$currentStage}_note" => $note,
        ]);

        return $this->skipJikaKosongBerjenjang($stageFlow[$model->status]['next'], $model->karyawan, $stageFlow);
    }

    protected function rejectSatuTahapBerjenjang(Model $model, string $currentStage, User $user, ?string $note): void
    {
        $model->update([
            "{$currentStage}_by"   => $user->id,
            "{$currentStage}_at"   => now(),
            "{$currentStage}_note" => $note,
            'status'               => 'ditolak',
            'rejected_stage'       => $currentStage,
            'rejected_note'        => $note,
        ]);
    }
}
