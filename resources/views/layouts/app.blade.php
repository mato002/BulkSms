<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            transition: width 0.3s ease, transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
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

        .sidebar-toggle-btn {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-toggle-btn:hover {
            background: var(--sidebar-hover);
            color: #ffffff;
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

        .nav-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.05em;
            cursor: pointer;
            user-select: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .nav-section-header:hover {
            background: var(--sidebar-hover);
            color: #ffffff;
        }

        .nav-section-header .collapse-icon {
            font-size: 0.7rem;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }

        .nav-section-header[aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }

        .nav-section-header + .collapse {
            transition: all 0.3s ease;
        }

        .nav-section-header + .collapse.show {
            display: block;
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
            flex-shrink: 0;
        }

        /* Collapsed Sidebar Styles */
        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar.collapsed .sidebar-brand span,
        .sidebar.collapsed .sidebar-nav-item span,
        .sidebar.collapsed .nav-section-title,
        .sidebar.collapsed .nav-section-header span,
        .sidebar.collapsed .badge {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            transition: opacity 0.2s ease, width 0.3s ease;
        }

        .sidebar-brand span,
        .sidebar-nav-item span {
            transition: opacity 0.2s ease, width 0.3s ease;
        }

        .sidebar.collapsed .sidebar-header {
            justify-content: center;
            padding: 1.5rem 0.5rem;
        }

        .sidebar.collapsed .sidebar-brand {
            gap: 0;
        }

        .sidebar.collapsed .sidebar-toggle-btn {
            position: absolute;
            right: 0.5rem;
        }

        .sidebar.collapsed .sidebar-nav-item {
            justify-content: center;
            padding: 0.875rem 0;
        }

        .sidebar.collapsed .sidebar-nav-item i {
            margin-right: 0;
        }

        /* Adjust main content when sidebar is collapsed */
        .sidebar.collapsed ~ .main-header {
            left: 80px;
        }

        .sidebar.collapsed ~ .sidebar-overlay ~ .main-wrapper {
            margin-left: 80px;
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
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: left 0.3s ease;
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
            transition: margin-left 0.3s ease;
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

            .sidebar.collapsed {
                width: var(--sidebar-width);
            }

            .sidebar.collapsed .sidebar-brand span,
            .sidebar.collapsed .sidebar-nav-item span,
            .sidebar.collapsed .nav-section-title,
            .sidebar.collapsed .badge {
                opacity: 1;
                width: auto;
            }

            .sidebar.collapsed .sidebar-nav-item {
                justify-content: flex-start;
                padding: 0.875rem 1.25rem;
            }

            .sidebar.collapsed .sidebar-nav-item i {
                margin-right: 0.875rem;
            }

            .sidebar.collapsed ~ .main-header,
            .main-header {
                left: 0;
                padding: 0 1rem;
            }

            .sidebar.collapsed ~ .sidebar-overlay ~ .main-wrapper,
            .main-wrapper {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
            }

            .header-search {
                display: none;
            }

            .header-right {
                gap: 1rem;
            }

            .sidebar-toggle-btn {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            :root {
                --header-height: 60px;
            }

            .main-content {
                padding: 1rem;
            }

            .main-header {
                padding: 0 0.75rem;
            }

            .user-info {
                display: none;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .footer-links {
                flex-direction: column;
                gap: 0.5rem;
            }

            .notification-dropdown {
                width: 320px !important;
            }

            /* Make cards stack better on mobile */
            .card {
                margin-bottom: 1rem;
            }

            /* Improve button groups on mobile */
            .btn-group {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-group .btn {
                width: 100%;
            }

            /* Better table handling */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 0.75rem;
            }

            .main-header {
                padding: 0 0.5rem;
                height: 56px;
            }

            :root {
                --header-height: 56px;
            }

            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }

            .notification-dropdown {
                width: 280px !important;
            }

            .dropdown-menu {
                min-width: 200px;
            }

            /* Stack header actions vertically if needed */
            .header-actions {
                flex-direction: column;
                width: 100%;
            }

            .header-actions .btn {
                width: 100%;
                justify-content: center;
            }

            /* Smaller notification badge */
            .notification-badge {
                width: 16px;
                height: 16px;
                font-size: 0.65rem;
            }
        }

        @media (max-width: 400px) {
            .main-content {
                padding: 0.5rem;
            }

            .sidebar-brand {
                font-size: 1.25rem;
            }

            .sidebar-brand i {
                font-size: 1.5rem;
            }

            .footer-text {
                font-size: 0.75rem;
            }

            .footer-links a {
                font-size: 0.75rem;
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
                <span>BulkSms</span>
            </a>
            <button class="sidebar-toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
                <i class="bi bi-chevron-left"></i>
            </button>
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
                        <li><a class="dropdown-item" href="{{ route('home') }}"><i class="bi bi-house me-2"></i> Landing Page</a></li>
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
                <p class="footer-text">&copy; {{ date('Y') }} BulkSms by Matech Technologies. All rights reserved.</p>
                <div class="footer-links">
                    <a href="{{ route('documentation') }}">Documentation</a>
                    <a href="{{ route('support') }}">Support</a>
                    <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                    <a href="{{ route('terms-of-service') }}">Terms of Service</a>
                </div>
            </div>
        </footer>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar elements
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const toggleIcon = sidebarToggle?.querySelector('i');

        // Load saved sidebar state from localStorage
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed && window.innerWidth > 992) {
            sidebar.classList.add('collapsed');
            if (toggleIcon) {
                toggleIcon.classList.remove('bi-chevron-left');
                toggleIcon.classList.add('bi-chevron-right');
            }
        }

        // Desktop sidebar collapse toggle
        sidebarToggle?.addEventListener('click', () => {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            
            // Update icon
            if (toggleIcon) {
                if (isCollapsed) {
                    toggleIcon.classList.remove('bi-chevron-left');
                    toggleIcon.classList.add('bi-chevron-right');
                } else {
                    toggleIcon.classList.remove('bi-chevron-right');
                    toggleIcon.classList.add('bi-chevron-left');
                }
            }
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });

        // Mobile menu toggle
        menuToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });

        sidebarOverlay?.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });

        // Reset collapsed state on mobile
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 992) {
                sidebar.classList.remove('collapsed');
                if (toggleIcon) {
                    toggleIcon.classList.remove('bi-chevron-right');
                    toggleIcon.classList.add('bi-chevron-left');
                }
                disableTooltips();
            } else {
                // Restore saved state on desktop
                const savedState = localStorage.getItem('sidebarCollapsed') === 'true';
                if (savedState) {
                    sidebar.classList.add('collapsed');
                    if (toggleIcon) {
                        toggleIcon.classList.remove('bi-chevron-left');
                        toggleIcon.classList.add('bi-chevron-right');
                    }
                    enableTooltips();
                } else {
                    disableTooltips();
                }
            }
        });

        // Tooltip management
        let tooltipList = [];

        function enableTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('.sidebar-nav-item[data-bs-toggle="tooltip"]'));
            tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover',
                    container: 'body'
                });
            });
        }

        function disableTooltips() {
            tooltipList.forEach(tooltip => tooltip.dispose());
            tooltipList = [];
        }

        // Initialize tooltips if sidebar is collapsed on load
        if (sidebar.classList.contains('collapsed') && window.innerWidth > 992) {
            enableTooltips();
        }

        // Handle collapsible sidebar sections
        document.addEventListener('DOMContentLoaded', function() {
            const collapseElements = document.querySelectorAll('.nav-section-header[data-bs-toggle="collapse"]');
            
            collapseElements.forEach(function(header) {
                const targetId = header.getAttribute('data-bs-target');
                const collapseElement = document.querySelector(targetId);
                
                if (collapseElement) {
                    // Update icon rotation when collapse state changes
                    collapseElement.addEventListener('show.bs.collapse', function() {
                        header.setAttribute('aria-expanded', 'true');
                        const icon = header.querySelector('.collapse-icon');
                        if (icon) {
                            icon.style.transform = 'rotate(180deg)';
                        }
                    });
                    
                    collapseElement.addEventListener('hide.bs.collapse', function() {
                        header.setAttribute('aria-expanded', 'false');
                        const icon = header.querySelector('.collapse-icon');
                        if (icon) {
                            icon.style.transform = 'rotate(0deg)';
                        }
                    });
                }
            });
        });

        // Update tooltips when sidebar toggle is clicked
        const originalToggleListener = sidebarToggle?.onclick;
        sidebarToggle?.addEventListener('click', function() {
            setTimeout(() => {
                if (sidebar.classList.contains('collapsed')) {
                    enableTooltips();
                } else {
                    disableTooltips();
                }
            }, 100);
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
            fetch('{{ route("notifications.list") }}')
                .then(response => response.json())
                .then(data => {
                    updateNotificationCount(data.notifications.filter(n => !n.read_at).length);
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

            container.innerHTML = notifications.map(notification => {
                const data = notification.data || {};
                const color = data.type === 'low_balance' ? 'warning' : 
                             data.type === 'failed_delivery' ? 'danger' :
                             data.type === 'campaign_complete' ? 'success' :
                             data.type === 'security_login_success' ? 'success' :
                             data.type === 'security_login_success_user' ? 'info' :
                             data.type === 'security_login_failed' ? 'danger' :
                             data.type === 'security_login_failed_admin' ? 'warning' :
                             data.type === 'new_message' ? 'primary' : 'primary';
                
                const icon = data.type === 'low_balance' ? 'bi-exclamation-triangle' :
                            data.type === 'failed_delivery' ? 'bi-x-circle' :
                            data.type === 'campaign_complete' ? 'bi-check-circle' :
                            data.type === 'security_login_success' ? 'bi-shield-check' :
                            data.type === 'security_login_success_user' ? 'bi-person-check' :
                            data.type === 'security_login_failed' ? 'bi-shield-exclamation' :
                            data.type === 'security_login_failed_admin' ? 'bi-shield-exclamation' :
                            data.type === 'new_message' ? 'bi-chat-dots' : 'bi-bell';
                
                return `
                    <div class="notification-item ${!notification.read_at ? 'unread' : ''}" 
                         onclick="markNotificationRead('${notification.id}', '${data.action_url || '#'}')">
                        <div class="notification-icon-wrapper bg-${color}">
                            <i class="bi ${icon}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">${data.title || 'Notification'}</div>
                            <div class="notification-message">${data.message || ''}</div>
                            <div class="notification-time">${notification.created_at || ''}</div>
                        </div>
                    </div>
                `;
            }).join('');
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
                notificationsLoaded = false; // Reload on next open
                if (link && link !== '#') {
                    window.location.href = link;
                } else {
                    loadNotifications(); // Refresh the list
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
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Search response:', data); // Debug log
                    if (data.success && data.results && data.results.length > 0) {
                        displaySearchResults(data.results, query);
                    } else {
                        searchResults.innerHTML = '<div class="search-no-results"><i class="bi bi-search display-6"></i><p class="mt-2">No results found for "' + escapeHtml(query) + '"</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    searchResults.innerHTML = '<div class="search-no-results"><i class="bi bi-exclamation-triangle display-6"></i><p class="mt-2">Error performing search. Check console for details.</p></div>';
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
    
    <!-- SweetAlert Delete Confirmation -->
    <script>
        // Reusable delete confirmation function
        function confirmDelete(event, message = 'Are you sure you want to delete this item? This action cannot be undone.') {
            event.preventDefault();
            const form = event.target.closest('form');
            
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // Reusable confirmation function for other actions
        function confirmAction(event, title = 'Are you sure?', message = 'This action cannot be undone.', confirmText = 'Yes, proceed!') {
            event.preventDefault();
            const form = event.target.closest('form');
            
            Swal.fire({
                title: title,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>

