<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $table = 'login_history';
    public $timestamps = false;
    protected $fillable = ['user_id','email','ip_address','user_agent','status','login_at','logout_at'];
    protected $casts = ['login_at' => 'datetime', 'logout_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }

    public function getBrowserAttribute(): string
    {
        $ua = $this->user_agent ?? '';
        if (str_contains($ua, 'Chrome') && !str_contains($ua, 'Edg'))  return 'Chrome';
        if (str_contains($ua, 'Firefox'))  return 'Firefox';
        if (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) return 'Safari';
        if (str_contains($ua, 'Edg'))  return 'Edge';
        if (str_contains($ua, 'Opera') || str_contains($ua, 'OPR')) return 'Opera';
        return 'Browser Lain';
    }

    public function getOsAttribute(): string
    {
        $ua = $this->user_agent ?? '';
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Mac OS'))  return 'macOS';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS';
        if (str_contains($ua, 'Linux'))   return 'Linux';
        return 'OS Lain';
    }
}
