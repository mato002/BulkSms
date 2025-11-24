@php
    $isMainMenuActive = request()->routeIs('dashboard') || request()->routeIs('inbox.*') || request()->routeIs('contacts.*') || request()->routeIs('tags.*') || request()->routeIs('templates.*') || request()->routeIs('campaigns.*') || request()->routeIs('messages.*');
@endphp
<div class="nav-section-header" data-bs-toggle="collapse" data-bs-target="#mainMenuCollapse" aria-expanded="{{ $isMainMenuActive ? 'true' : 'false' }}">
    <span>Main Menu</span>
    <i class="bi bi-chevron-down collapse-icon"></i>
</div>
<div class="collapse {{ $isMainMenuActive ? 'show' : '' }}" id="mainMenuCollapse">
    <a href="{{ route('dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('inbox.index') }}" class="sidebar-nav-item {{ request()->routeIs('inbox.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Inbox">
        <i class="bi bi-inbox-fill"></i>
        <span>Inbox</span>
        @php
            $unreadCount = DB::table('conversations')->where('client_id', session('client_id', 1))->sum('unread_count');
        @endphp
        @if($unreadCount > 0)
            <span class="badge bg-danger ms-auto">{{ $unreadCount }}</span>
        @endif
    </a>
    <a href="{{ route('contacts.index') }}" class="sidebar-nav-item {{ request()->routeIs('contacts.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Contacts">
        <i class="bi bi-people"></i>
        <span>Contacts</span>
    </a>
    <a href="{{ route('tags.index') }}" class="sidebar-nav-item {{ request()->routeIs('tags.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Tags">
        <i class="bi bi-tags"></i>
        <span>Tags</span>
        @php
            $tagCount = \App\Models\Tag::where('client_id', session('client_id', 1))->count();
        @endphp
        @if($tagCount > 0)
            <span class="badge bg-info ms-auto">{{ $tagCount }}</span>
        @endif
    </a>
    <a href="{{ route('templates.index') }}" class="sidebar-nav-item {{ request()->routeIs('templates.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Templates">
        <i class="bi bi-file-text"></i>
        <span>Templates</span>
    </a>
    <a href="{{ route('campaigns.index') }}" class="sidebar-nav-item {{ request()->routeIs('campaigns.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Campaigns">
        <i class="bi bi-megaphone"></i>
        <span>Campaigns</span>
    </a>
    <a href="{{ route('messages.index') }}" class="sidebar-nav-item {{ request()->routeIs('messages.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Messages">
        <i class="bi bi-envelope"></i>
        <span>Messages</span>
    </a>
</div>

@if(Auth::check() && Auth::user()->isAdmin())
@php
    $clientChannels = DB::table('channels')
        ->where('client_id', session('client_id', 1))
        ->get()
        ->keyBy('name');

    $requestedChannel = request()->query('channel');

    $channelMenuConfig = [
        'sms' => [
            'label' => 'SMS',
            'icon' => 'bi-phone',
            'route' => route('sms.index'),
            'is_current' => request()->routeIs('sms.*'),
        ],
        'whatsapp' => [
            'label' => 'WhatsApp',
            'icon' => 'bi-whatsapp',
            'route' => route('whatsapp.index'),
            'is_current' => request()->routeIs('whatsapp.*'),
        ],
        'email' => [
            'label' => 'Email',
            'icon' => 'bi-envelope',
            'route' => route('email.index'),
            'is_current' => request()->routeIs('email.*'),
        ],
    ];

    $channelMenuItems = collect($channelMenuConfig)->map(function ($config, $name) use ($clientChannels) {
        $channel = $clientChannels->get($name);

        return array_merge($config, [
            'exists' => (bool) $channel,
            'active' => $channel->active ?? false,
        ]);
    });

    $isChannelsActive = $channelMenuItems->contains(fn ($item) => $item['is_current']);
    $isDeveloperActive = request()->routeIs('api.docs') || request()->routeIs('api-monitor.*');
    $isSystemActive = request()->routeIs('wallet.*') || request()->routeIs('analytics.*') || request()->routeIs('settings.index') || request()->routeIs('notifications.*');
@endphp
<div class="nav-section-header mt-4" data-bs-toggle="collapse" data-bs-target="#channelsCollapse" aria-expanded="{{ $isChannelsActive ? 'true' : 'false' }}">
    <span>Channels</span>
    <i class="bi bi-chevron-down collapse-icon"></i>
</div>
<div class="collapse {{ $isChannelsActive ? 'show' : '' }}" id="channelsCollapse">
    @foreach($channelMenuItems as $name => $item)
        <a href="{{ $item['route'] }}" class="sidebar-nav-item {{ $item['is_current'] ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ $item['label'] }}">
            <i class="bi {{ $item['icon'] }}"></i>
            <span>{{ $item['label'] }}</span>
            @if(!$item['exists'])
                <span class="badge bg-warning ms-auto">Setup</span>
            @elseif(!$item['active'])
                <span class="badge bg-secondary ms-auto">Inactive</span>
            @endif
        </a>
    @endforeach
</div>

<div class="nav-section-header mt-4" data-bs-toggle="collapse" data-bs-target="#developerCollapse" aria-expanded="{{ $isDeveloperActive ? 'true' : 'false' }}">
    <span>Developer</span>
    <i class="bi bi-chevron-down collapse-icon"></i>
</div>
<div class="collapse {{ $isDeveloperActive ? 'show' : '' }}" id="developerCollapse">
    <a href="{{ route('api.docs') }}" class="sidebar-nav-item {{ request()->routeIs('api.docs') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="API Documentation">
        <i class="bi bi-code-square"></i>
        <span>API Documentation</span>
    </a>
    <a href="{{ route('api-monitor.index') }}" class="sidebar-nav-item {{ request()->routeIs('api-monitor.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="API Monitor">
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
</div>

<div class="nav-section-header mt-4" data-bs-toggle="collapse" data-bs-target="#systemCollapse" aria-expanded="{{ $isSystemActive ? 'true' : 'false' }}">
    <span>System</span>
    <i class="bi bi-chevron-down collapse-icon"></i>
</div>
<div class="collapse {{ $isSystemActive ? 'show' : '' }}" id="systemCollapse">
    <a href="{{ route('wallet.index') }}" class="sidebar-nav-item {{ request()->routeIs('wallet.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Wallet">
        <i class="bi bi-wallet2"></i>
        <span>Wallet</span>
        @if(Auth::check() && Auth::user()->balance < 100)
            <span class="badge bg-warning ms-auto" title="Low balance">
                <i class="bi bi-exclamation-triangle"></i>
            </span>
        @endif
    </a>
    <a href="{{ route('analytics.index') }}" class="sidebar-nav-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Analytics">
        <i class="bi bi-bar-chart"></i>
        <span>Analytics</span>
    </a>
    <a href="{{ route('settings.index') }}" class="sidebar-nav-item {{ request()->routeIs('settings.index') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Settings">
        <i class="bi bi-gear"></i>
        <span>Settings</span>
    </a>
    <a href="{{ route('notifications.index') }}" class="sidebar-nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Notifications">
        <i class="bi bi-bell"></i>
        <span>Notifications</span>
        @php
            $unreadNotifications = \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->count();
        @endphp
        @if($unreadNotifications > 0)
            <span class="badge bg-danger ms-auto">{{ $unreadNotifications }}</span>
        @endif
    </a>
</div>
@endif

@if(Auth::check() && Auth::user()->isAdmin())
@php
    $isAdminActive = request()->routeIs('admin.admins.*') || request()->routeIs('admin.senders.*');
@endphp
<div class="nav-section-header mt-4" data-bs-toggle="collapse" data-bs-target="#adminCollapse" aria-expanded="{{ $isAdminActive ? 'true' : 'false' }}">
    <span>Admin</span>
    <i class="bi bi-chevron-down collapse-icon"></i>
</div>
<div class="collapse {{ $isAdminActive ? 'show' : '' }}" id="adminCollapse">
    <a href="{{ route('admin.admins.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Manage Admins">
        <i class="bi bi-people"></i>
        <span>Manage Admins</span>
    </a>
    <a href="{{ route('admin.senders.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.senders.*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Manage Senders">
        <i class="bi bi-building"></i>
        <span>Manage Senders</span>
    </a>
</div>
@endif

