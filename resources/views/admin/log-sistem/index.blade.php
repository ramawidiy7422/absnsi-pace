@extends('layouts.admin')
@section('title', 'Log & Riwayat Sistem')
@section('page-title', 'Log & Riwayat Sistem')

@push('styles')
<style>
.tab-nav {
    display: flex; gap: 4px; border-bottom: 2px solid #e2e8f0;
    margin-bottom: 20px; overflow-x: auto; flex-wrap: nowrap;
}
.tab-btn {
    padding: 10px 16px; border: none; background: none; cursor: pointer;
    font-size: 13px; font-weight: 500; color: #718096;
    border-bottom: 2px solid transparent; margin-bottom: -2px;
    white-space: nowrap; transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 6px;
    border-radius: 6px 6px 0 0; text-decoration: none;
}
.tab-btn:hover  { color: #4299e1; background: #f7fafc; }
.tab-btn.active { color: #4299e1; border-bottom-color: #4299e1; background: #ebf8ff; font-weight: 600; }
.tab-content    { display: none; }
.tab-content.active { display: block; }
</style>
@endpush

@section('content')

@if($bisaAuditLog && $bisaLoginHistory)
<div class="tab-nav">
    <button class="tab-btn" data-tab="tab-audit-log" onclick="switchTab('tab-audit-log')">
        <i class="fas fa-history"></i> Audit Log
    </button>
    <button class="tab-btn" data-tab="tab-login-history" onclick="switchTab('tab-login-history')">
        <i class="fas fa-sign-in-alt"></i> Riwayat Login
    </button>
</div>
@endif

@if($bisaAuditLog)
<div class="tab-content" id="tab-audit-log">
    @include('admin.log-sistem.partials.tab-audit-log')
</div>
@endif

@if($bisaLoginHistory)
<div class="tab-content" id="tab-login-history">
    @include('admin.log-sistem.partials.tab-login-history')
</div>
@endif

@if(!$bisaAuditLog && !$bisaLoginHistory)
<div class="card" style="padding:40px;text-align:center;color:#a0aec0;">
    Anda tidak memiliki akses untuk melihat log sistem.
</div>
@endif

@endsection

@push('scripts')
<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    const target = document.getElementById(tabId);
    if (target) target.classList.add('active');
    document.querySelectorAll('.tab-btn[data-tab="' + tabId + '"]').forEach(btn => btn.classList.add('active'));
    history.replaceState(null, null, '#' + tabId);
}

(function() {
    const validTabs = @json(array_filter([
        $bisaAuditLog ? 'tab-audit-log' : null,
        $bisaLoginHistory ? 'tab-login-history' : null,
    ]));
    if (!validTabs.length) return;
    const hash = window.location.hash.replace('#', '');
    const tabParam = new URLSearchParams(window.location.search).get('_tab');
    const initialTab = validTabs.includes(hash) ? hash : (validTabs.includes(tabParam) ? tabParam : validTabs[0]);
    switchTab(initialTab);
})();
</script>
@endpush
