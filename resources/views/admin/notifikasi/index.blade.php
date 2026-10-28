@extends('layouts.admin')
@section('title', 'Notifikasi')
@section('page-title', 'Semua Notifikasi')

@section('content')

<div class="card" style="border-radius:14px;overflow:hidden;">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;flex-wrap:wrap;gap:10px;">
        <h6 style="margin:0;font-weight:700;font-size:15px;">
            <i class="fas fa-bell" style="color:#4299e1;margin-right:8px;"></i>Semua Notifikasi
            @php $unreadCount = $notifikasi->where('read_at', null)->count(); @endphp
            @if($unreadCount > 0)
            <span style="background:#4299e1;color:white;font-size:10px;padding:2px 8px;border-radius:10px;margin-left:6px;font-weight:700;">{{ $unreadCount }} baru</span>
            @endif
        </h6>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            @if($unreadCount > 0)
            <button onclick="tandaiSemuaDibaca()" style="padding:5px 12px;background:#ebf8ff;color:#2b6cb0;border:1px solid #bee3f8;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;">
                <i class="fas fa-check-double"></i> Tandai Semua Dibaca
            </button>
            @endif
            @if($notifikasi->total() > 0)
            <form method="POST" action="{{ route('admin.notifikasi.destroy') }}"
                  onsubmit="return confirm('Hapus SEMUA notifikasi? Tindakan ini tidak bisa dibatalkan.')"
                  style="display:inline;">
                @csrf @method('DELETE')
                <input type="hidden" name="hapus_semua" value="1">
                <button type="submit" style="padding:5px 12px;background:#fed7e2;color:#c53030;border:1px solid #feb2b2;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;">
                    <i class="fas fa-trash-alt"></i> Hapus Semua
                </button>
            </form>
            @endif
        </div>
    </div>

    @forelse($notifikasi as $n)
    @php
        $d      = is_array($n->data) ? $n->data : json_decode($n->data, true);
        $unread = is_null($n->read_at);
        $icon   = $d['icon']  ?? 'fas fa-bell';
        $color  = $d['color'] ?? '#4299e1';
        $bg     = $d['bg']    ?? '#ebf8ff';
        $url    = $d['url']   ?? '#';
        $title  = $d['title'] ?? ($d['judul'] ?? 'Notifikasi');
        $body   = $d['body']  ?? ($d['pesan'] ?? '');
    @endphp
    <div style="display:flex;align-items:flex-start;gap:14px;padding:16px 20px;border-bottom:1px solid #f0f0f0;
                background:{{ $unread ? '#f0f9ff' : 'white' }};transition:background .2s;"
         onmouseover="this.style.background='#f7fafc'"
         onmouseout="this.style.background='{{ $unread ? '#f0f9ff' : 'white' }}'">

        <div style="width:44px;height:44px;border-radius:12px;background:{{ $bg }};color:{{ $color }};
                    display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;
                    border:1px solid {{ $bg }};filter:brightness(0.95);">
            <i class="{{ $icon }}"></i>
        </div>

        <div style="flex:1;min-width:0;">
            <div style="font-weight:{{ $unread ? '700' : '600' }};color:#2d3748;font-size:13px;margin-bottom:4px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span>{{ $title }}</span>
                @if($unread)
                <span style="background:#4299e1;color:white;font-size:9px;padding:1px 7px;border-radius:4px;font-weight:700;flex-shrink:0;">BARU</span>
                @endif
            </div>
            @if($body)
            <div style="font-size:12px;color:#4a5568;line-height:1.6;white-space:pre-line;background:#f7fafc;border-radius:8px;padding:8px 10px;margin-bottom:6px;border:1px solid #e2e8f0;max-height:120px;overflow-y:auto;">{{ $body }}</div>
            @endif
            <div style="font-size:11px;color:#a0aec0;">
                <i class="fas fa-clock" style="margin-right:3px;"></i>{{ $n->created_at->diffForHumans() }}
                &nbsp;·&nbsp; {{ $n->created_at->translatedFormat('d M Y, H:i') }}
            </div>
        </div>

        <div style="display:flex;gap:6px;flex-shrink:0;flex-direction:column;align-items:flex-end;">
            @if($url && $url !== '#')
            <a href="{{ $url }}" style="padding:6px 14px;background:#4299e1;color:white;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap;">
                <i class="fas fa-arrow-right"></i> Lihat
            </a>
            @endif
            <form method="POST" action="{{ route('admin.notifikasi.destroy') }}"
                  onsubmit="return confirm('Hapus notifikasi ini?')" style="display:inline;">
                @csrf @method('DELETE')
                <input type="hidden" name="id" value="{{ $n->id }}">
                <button type="submit" style="padding:5px 10px;background:#fff5f5;color:#c53030;border:1px solid #fed7d7;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div style="text-align:center;padding:70px 20px;color:#a0aec0;">
        <i class="fas fa-bell-slash" style="font-size:48px;display:block;margin-bottom:16px;opacity:0.4;"></i>
        <div style="font-size:16px;font-weight:700;color:#4a5568;margin-bottom:4px;">Tidak ada notifikasi</div>
        <div style="font-size:13px;">Reminder dan notifikasi sistem akan muncul di sini.</div>
    </div>
    @endforelse
</div>

@if($notifikasi->hasPages())
<div style="margin-top:16px;">{{ $notifikasi->appends(request()->query())->links() }}</div>
@endif

@push('scripts')
<script>
function tandaiSemuaDibaca() {
    fetch('{{ route('admin.notifikasi.mark-read') }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
        body: JSON.stringify({})
    }).then(r => r.json()).then(() => location.reload());
}
</script>
@endpush
@endsection
