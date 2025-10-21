<div class="nav-section-title">Main Menu</div>
<a href="{{ route('dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i>
    <span>Dashboard</span>
</a>
<a href="{{ route('inbox.index') }}" class="sidebar-nav-item {{ request()->routeIs('inbox.*') ? 'active' : '' }}">
    <i class="bi bi-inbox-fill"></i>
    <span>Inbox</span>
    @php
        $unreadCount = DB::table('conversations')->where('client_id', session('client_id', 1))->sum('unread_count');
    @endphp
    @if($unreadCount > 0)
        <span class="badge bg-danger ms-auto">{{ $unreadCount }}</span>
    @endif
</a>
<a href="{{ route('contacts.index') }}" class="sidebar-nav-item {{ request()->routeIs('contacts.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i>
    <span>Contacts</span>
</a>
<a href="{{ route('tags.index') }}" class="sidebar-nav-item {{ request()->routeIs('tags.*') ? 'active' : '' }}">
    <i class="bi bi-tags"></i>
    <span>Tags</span>
    @php
        $tagCount = \App\Models\Tag::where('client_id', session('client_id', 1))->count();
    @endphp
    @if($tagCount > 0)
        <span class="badge bg-info ms-auto">{{ $tagCount }}</span>
    @endif
</a>
<a href="{{ route('templates.index') }}" class="sidebar-nav-item {{ request()->routeIs('templates.*') ? 'active' : '' }}">
    <i class="bi bi-file-text"></i>
    <span>Templates</span>
</a>
<a href="{{ route('campaigns.index') }}" class="sidebar-nav-item {{ request()->routeIs('campaigns.*') ? 'active' : '' }}">
    <i class="bi bi-megaphone"></i>
    <span>Campaigns</span>
</a>
<a href="{{ route('messages.index') }}" class="sidebar-nav-item {{ request()->routeIs('messages.*') ? 'active' : '' }}">
    <i class="bi bi-envelope"></i>
    <span>Messages</span>
</a>

<div class="nav-section-title mt-4">Channels</div>
<a href="{{ route('whatsapp.index') }}" class="sidebar-nav-item {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}">
    <i class="bi bi-whatsapp"></i>
    <span>WhatsApp</span>
</a>

<div class="nav-section-title mt-4">Developer</div>
<a href="{{ route('api.docs') }}" class="sidebar-nav-item {{ request()->routeIs('api.docs') ? 'active' : '' }}">
    <i class="bi bi-code-square"></i>
    <span>API Documentation</span>
</a>
<a href="{{ route('api-monitor.index') }}" class="sidebar-nav-item {{ request()->routeIs('api-monitor.*') ? 'active' : '' }}">
    <i class="bi bi-activity"></i>
    <span>API Monitor</span>
    @php
        $todayRequests = \App\Models\ApiLog::whereDate('created_at', today())->count();
        $failedToday = \App\Models\ApiLog::whereDate('created_at', today())->where('success', false)->count();
    @endphp
    @if($todayRequests > 0)
        <span class="badge bg-{{ $failedToday > 0 ? 'danger' : 'success' }} ms-auto">{{ $todayRequests }}</span>
    @endif
</a>

<div class="nav-section-title mt-4">System</div>
<a href="{{ route('wallet.index') }}" class="sidebar-nav-item {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
    <i class="bi bi-wallet2"></i>
    <span>Wallet</span>
    @if(Auth::check() && Auth::user()->balance < 100)
        <span class="badge bg-warning ms-auto" title="Low balance">
            <i class="bi bi-exclamation-triangle"></i>
        </span>
    @endif
</a>
<a href="{{ route('analytics.index') }}" class="sidebar-nav-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
    <i class="bi bi-bar-chart"></i>
    <span>Analytics</span>
</a>
<a href="{{ route('settings.index') }}" class="sidebar-nav-item {{ request()->routeIs('settings.index') ? 'active' : '' }}">
    <i class="bi bi-gear"></i>
    <span>Settings</span>
</a>
<a href="{{ route('notifications.settings') }}" class="sidebar-nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
    <i class="bi bi-bell"></i>
    <span>Notifications</span>
</a>

@if(Auth::check() && Auth::user()->isAdmin())
<div class="nav-section-title mt-4">Admin</div>
<a href="{{ route('admin.senders.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.senders.*') ? 'active' : '' }}">
    <i class="bi bi-building"></i>
    <span>Manage Senders</span>
</a>
@endif

