<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    @stack('styles')
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 65px;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-active: #3b82f6;
            --header-bg: #ffffff;
            --footer-bg: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: #e2e8f0;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand i {
            font-size: 1.75rem;
            color: var(--sidebar-active);
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .nav-section-title {
            padding: 0.75rem 1.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.05em;
        }

        .sidebar-nav-item {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.25rem;
            color: #e2e8f0;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            font-size: 0.95rem;
        }

        .sidebar-nav-item:hover {
            background: var(--sidebar-hover);
            color: #ffffff;
            text-decoration: none;
        }

        .sidebar-nav-item.active {
            background: rgba(59, 130, 246, 0.1);
            color: #ffffff;
            border-left-color: var(--sidebar-active);
        }

        .sidebar-nav-item i {
            width: 24px;
            font-size: 1.25rem;
            margin-right: 0.875rem;
        }

        /* Header Styles */
        .main-header {
            position: fixed;
            left: var(--sidebar-width);
            right: 0;
            top: 0;
            height: var(--header-height);
            background: var(--header-bg);
            border-bottom: 1px solid #e2e8f0;
            z-index: 999;
            display: flex;
            align-items: center;
            padding: 0 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .header-left {
            flex: 1;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .header-search {
            position: relative;
        }

        .header-search input {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            width: 300px;
            font-size: 0.9rem;
        }

        .header-search input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .header-search > i {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            z-index: 1;
        }

        .search-results-dropdown {
            position: absolute;
            top: calc(100% + 0.5rem);
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
        }

        .search-result-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: background 0.2s;
        }

        .search-result-item:hover {
            background: #f8fafc;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-icon {
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3b82f6;
            flex-shrink: 0;
        }

        .search-result-content {
            flex: 1;
            min-width: 0;
        }

        .search-result-title {
            font-weight: 600;
            font-size: 0.875rem;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .search-result-subtitle {
            font-size: 0.75rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .search-no-results {
            padding: 2rem 1rem;
            text-align: center;
            color: #94a3b8;
        }

        .search-loading {
            padding: 1.5rem 1rem;
            text-align: center;
        }

        .search-view-all {
            padding: 0.75rem 1rem;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .search-view-all a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .search-view-all a:hover {
            text-decoration: underline;
        }

        .header-notifications {
            position: relative;
            cursor: pointer;
        }

        .notification-icon {
            font-size: 1.35rem;
            color: #64748b;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background 0.2s;
        }

        .user-profile:hover {
            background: #f8fafc;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
        }

        .user-role {
            font-size: 0.75rem;
            color: #64748b;
        }

        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            min-height: calc(100vh - var(--header-height));
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
        }

        /* Footer Styles */
        .main-footer {
            background: var(--footer-bg);
            border-top: 1px solid #e2e8f0;
            padding: 1.5rem 2rem;
            margin-top: auto;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-text {
            color: #64748b;
            font-size: 0.875rem;
            margin: 0;
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
        }

        .footer-links a {
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: #3b82f6;
        }

        /* Dropdown Menu */
        .dropdown-menu {
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-header {
                left: 0;
            }

            .main-wrapper {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
            }

            .header-search {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 1rem;
            }

            .user-info {
                display: none;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            padding: 0;
        }

        .notification-header {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
        }

        .notification-header h6 {
            font-weight: 600;
            color: #1e293b;
        }

        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            gap: 0.75rem;
        }

        .notification-item:hover {
            background: #f8fafc;
        }

        .notification-item.unread {
            background: #eff6ff;
        }

        .notification-item.unread:hover {
            background: #dbeafe;
        }

        .notification-icon-wrapper {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-icon-wrapper.bg-primary {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .notification-icon-wrapper.bg-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .notification-icon-wrapper.bg-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .notification-icon-wrapper.bg-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .notification-message {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .notification-time {
            color: #94a3b8;
            font-size: 0.75rem;
        }

        .notification-empty {
            padding: 3rem 1rem;
            text-align: center;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ url('/') }}" class="sidebar-brand">
                <i class="bi bi-chat-dots-fill"></i>
                <span>Bulk SMS</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            @include('layouts.sidebar')
        </nav>
    </aside>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Header -->
    <header class="main-header">
        <div class="header-left">
            <button class="mobile-menu-btn" id="menuToggle">
                <i class="bi bi-list"></i>
            </button>
        </div>
        <div class="header-right">
            <div class="header-search">
                <i class="bi bi-search"></i>
                <input type="text" id="globalSearch" placeholder="Search..." class="form-control" autocomplete="off">
                <div class="search-results-dropdown" id="searchResults" style="display: none;"></div>
            </div>
            <div class="dropdown">
                <div class="header-notifications" data-bs-toggle="dropdown" aria-expanded="false" id="notificationBell">
                    <i class="bi bi-bell notification-icon"></i>
                    <span class="notification-badge" id="notificationCount" style="display: none;">0</span>
                </div>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 380px; max-height: 500px; overflow-y: auto;">
                    <div class="notification-header">
                        <h6 class="mb-0">Notifications</h6>
                        <button class="btn btn-sm btn-link" onclick="markAllNotificationsRead()">Mark all read</button>
                    </div>
                    <div id="notificationList">
                        <div class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dropdown">
                <div class="user-profile" data-bs-toggle="dropdown" aria-expanded="false">
                    @auth
                        @if(Auth::user()->avatar)
                            <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="Avatar" class="user-avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        @else
                            <div class="user-avatar">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="user-info">
                            <span class="user-name">{{ Auth::user()->name }}</span>
                            <span class="user-role">{{ Auth::user()->email }}</span>
                        </div>
                        <i class="bi bi-chevron-down" style="color: #94a3b8;"></i>
                    @else
                        <div class="user-avatar">G</div>
                        <div class="user-info">
                            <span class="user-name">Guest</span>
                            <span class="user-role">Not logged in</span>
                        </div>
                    @endauth
                </div>
                <ul class="dropdown-menu dropdown-menu-end">
                    @auth
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    @else
                        <li><a class="dropdown-item" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-2"></i> Login</a></li>
                        <li><a class="dropdown-item" href="{{ route('register') }}"><i class="bi bi-person-plus me-2"></i> Register</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Main Content -->
    <main class="main-content">
                    @yield('content')
        </main>

        <!-- Footer -->
        <footer class="main-footer">
            <div class="footer-content">
                <p class="footer-text">&copy; {{ date('Y') }} Bulk SMS Laravel. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Documentation</a>
                    <a href="#">Support</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </footer>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        menuToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });

        sidebarOverlay?.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });

        // Notification System
        let notificationsLoaded = false;

        // Load notifications when dropdown is opened
        document.getElementById('notificationBell')?.addEventListener('click', function() {
            if (!notificationsLoaded) {
                loadNotifications();
                notificationsLoaded = true;
            }
        });

        // Load notifications
        function loadNotifications() {
            fetch('{{ route("notifications.index") }}')
                .then(response => response.json())
                .then(data => {
                    updateNotificationCount(data.unread_count);
                    displayNotifications(data.notifications);
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    document.getElementById('notificationList').innerHTML = 
                        '<div class="notification-empty"><i class="bi bi-exclamation-triangle display-6"></i><p class="mt-2">Failed to load notifications</p></div>';
                });
        }

        // Update notification count
        function updateNotificationCount(count) {
            const badge = document.getElementById('notificationCount');
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }

        // Display notifications
        function displayNotifications(notifications) {
            const container = document.getElementById('notificationList');
            
            if (notifications.length === 0) {
                container.innerHTML = '<div class="notification-empty"><i class="bi bi-bell-slash display-6"></i><p class="mt-2">No notifications</p></div>';
                return;
            }

            container.innerHTML = notifications.map(notification => `
                <div class="notification-item ${!notification.is_read ? 'unread' : ''}" 
                     onclick="markNotificationRead(${notification.id}, '${notification.link || '#'}')">
                    <div class="notification-icon-wrapper bg-${notification.color}">
                        <i class="bi ${notification.icon || 'bi-bell'}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.message}</div>
                        <div class="notification-time">${formatNotificationTime(notification.created_at)}</div>
                    </div>
                </div>
            `).join('');
        }

        // Mark notification as read
        function markNotificationRead(id, link) {
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notificationsLoaded = false; // Reload on next open
                    if (link && link !== '#') {
                        window.location.href = link;
                    } else {
                        loadNotifications(); // Refresh the list
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Mark all notifications as read
        function markAllNotificationsRead() {
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Format notification time
        function formatNotificationTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
            if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
            if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
            
            return date.toLocaleDateString();
        }

        // Load notification count on page load
        fetch('{{ route("notifications.unread-count") }}')
            .then(response => response.json())
            .then(data => updateNotificationCount(data.count))
            .catch(error => console.error('Error loading notification count:', error));

        // Refresh notification count every 30 seconds
        setInterval(() => {
            fetch('{{ route("notifications.unread-count") }}')
                .then(response => response.json())
                .then(data => updateNotificationCount(data.count))
                .catch(error => console.error('Error:', error));
        }, 30000);

        // Global Search Functionality
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        searchInput?.addEventListener('input', function(e) {
            const query = e.target.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            // Show loading state
            searchResults.style.display = 'block';
            searchResults.innerHTML = '<div class="search-loading"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

            // Debounce search
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        // Handle Enter key to go to full results page
        searchInput?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const query = e.target.value.trim();
                if (query.length >= 2) {
                    window.location.href = '{{ route("search.results") }}?q=' + encodeURIComponent(query);
                }
            }
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput?.contains(e.target) && !searchResults?.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        function performSearch(query) {
            fetch('{{ route("search.api") }}?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.results.length > 0) {
                        displaySearchResults(data.results, query);
                    } else {
                        searchResults.innerHTML = '<div class="search-no-results"><i class="bi bi-search display-6"></i><p class="mt-2">No results found for "' + escapeHtml(query) + '"</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    searchResults.innerHTML = '<div class="search-no-results"><i class="bi bi-exclamation-triangle display-6"></i><p class="mt-2">Error performing search</p></div>';
                });
        }

        function displaySearchResults(results, query) {
            const maxResults = 8;
            const displayResults = results.slice(0, maxResults);
            
            let html = '';
            
            displayResults.forEach(result => {
                html += `
                    <a href="${result.url}" class="search-result-item text-decoration-none">
                        <div class="search-result-icon">
                            <i class="bi ${result.icon}"></i>
                        </div>
                        <div class="search-result-content">
                            <div class="search-result-title">${escapeHtml(result.title)}</div>
                            <div class="search-result-subtitle">${escapeHtml(result.subtitle)}</div>
                        </div>
                    </a>
                `;
            });

            // Add "View all results" link if there are more results
            if (results.length > maxResults) {
                html += `
                    <div class="search-view-all">
                        <a href="{{ route('search.results') }}?q=${encodeURIComponent(query)}">
                            View all ${results.length} results <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                `;
            }

            searchResults.innerHTML = html;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
    @stack('scripts')
</body>
</html>

