<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    public $timestamps = false;
    protected $fillable = ['user_id','action','model','model_id','deskripsi','data_lama','data_baru','ip_address'];
    protected $casts = ['data_lama'=>'array','data_baru'=>'array','created_at'=>'datetime'];

    public function user() { return $this->belongsTo(User::class); }

    public static function catat(string $action, string $deskripsi, $model = null, array $dataLama = null, array $dataBaru = null): void
    {
        static::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model'      => $model ? class_basename($model) : null,
            'model_id'   => $model?->id ?? null,
            'deskripsi'  => $deskripsi,
            'data_lama'  => $dataLama,
            'data_baru'  => $dataBaru,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
