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

<div class="nav-section-title mt-4">System</div>
<a href="{{ route('analytics.index') }}" class="sidebar-nav-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
    <i class="bi bi-bar-chart"></i>
    <span>Analytics</span>
</a>
<a href="{{ route('settings.index') }}" class="sidebar-nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
    <i class="bi bi-gear"></i>
    <span>Settings</span>
</a>

@if(Auth::check() && Auth::user()->isAdmin())
<div class="nav-section-title mt-4">Admin</div>
<a href="{{ route('admin.senders.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.senders.*') ? 'active' : '' }}">
    <i class="bi bi-building"></i>
    <span>Manage Senders</span>
</a>
@endif

