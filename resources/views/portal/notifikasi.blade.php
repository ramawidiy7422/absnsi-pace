@extends('layouts.admin')
@section('title', 'Notifikasi Saya')
@section('page-title', 'Notifikasi Saya')

@section('content')
<div class="card" style="border-radius:14px;overflow:hidden;">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;flex-wrap:wrap;gap:10px;">
        <h6 style="margin:0;font-weight:700;font-size:15px;">
            <i class="fas fa-bell" style="color:#4299e1;margin-right:8px;"></i>Notifikasi Saya
            @php $unreadCount = $notifikasi->where('read_at', null)->count(); @endphp
            @if($unreadCount > 0)
            <span style="background:#4299e1;color:white;font-size:10px;padding:2px 8px;border-radius:10px;margin-left:6px;font-weight:700;">{{ $unreadCount }} baru</span>
            @endif
        </h6>
        @if($notifikasi->total() > 0)
        <form method="POST" action="{{ route('portal.notifikasi.hapus') }}"
              onsubmit="return confirm('Hapus semua notifikasi?')" style="display:inline;">
            @csrf
            <input type="hidden" name="hapus_semua" value="1">
            <button type="submit" style="padding:5px 12px;background:#fed7e2;color:#c53030;border:none;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;">
                <i class="fas fa-trash-alt"></i> Hapus Semua
            </button>
        </form>
        @endif
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
                    display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
            <i class="{{ $icon }}"></i>
        </div>

        <div style="flex:1;min-width:0;">
            <div style="font-weight:{{ $unread ? '700' : '500' }};color:#2d3748;font-size:13.5px;margin-bottom:3px;">
                {{ $title }}
                @if($unread)<span style="display:inline-block;width:7px;height:7px;background:#4299e1;border-radius:50%;margin-left:6px;vertical-align:middle;"></span>@endif
            </div>
            @if($body)
            <div style="font-size:12.5px;color:#718096;line-height:1.6;">{{ $body }}</div>
            @endif
            <div style="font-size:11px;color:#a0aec0;margin-top:4px;">
                <i class="fas fa-clock"></i> {{ $n->created_at->diffForHumans() }}
            </div>
        </div>

        @if($url && $url !== '#')
        <a href="{{ $url }}" style="font-size:11px;color:#4299e1;white-space:nowrap;font-weight:600;">
            Lihat <i class="fas fa-arrow-right"></i>
        </a>
        @endif
    </div>
    @empty
    <div style="text-align:center;padding:60px 20px;color:#a0aec0;">
        <i class="fas fa-bell-slash" style="font-size:40px;margin-bottom:14px;display:block;"></i>
        <div style="font-size:14px;">Belum ada notifikasi</div>
    </div>
    @endforelse

    @if($notifikasi->hasPages())
    <div style="padding:16px 20px;">
        {{ $notifikasi->links() }}
    </div>
    @endif
</div>
@endsection
