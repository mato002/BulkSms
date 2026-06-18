/**
 * Turbo Frame bridge for BulkSms — reinitializes UI after partial navigations.
 */
(function () {
    'use strict';

    const ROUTE_PATTERNS = [
        { pattern: /^\/dashboard/, selector: 'a[href*="/dashboard"]' },
        { pattern: /^\/inbox/, selector: 'a[href*="/inbox"]' },
        { pattern: /^\/contacts/, selector: 'a[href*="/contacts"]' },
        { pattern: /^\/tags/, selector: 'a[href*="/tags"]' },
        { pattern: /^\/templates/, selector: 'a[href*="/templates"]' },
        { pattern: /^\/campaigns/, selector: 'a[href*="/campaigns"]' },
        { pattern: /^\/messages/, selector: 'a[href*="/messages"]' },
        { pattern: /^\/sms/, selector: 'a[href*="/sms"]' },
        { pattern: /^\/whatsapp/, selector: 'a[href*="/whatsapp"]' },
        { pattern: /^\/email/, selector: 'a[href*="/email"]' },
        { pattern: /^\/api-docs/, selector: 'a[href*="/api-docs"]' },
        { pattern: /^\/api-monitor/, selector: 'a[href*="/api-monitor"]' },
        { pattern: /^\/wallet/, selector: 'a[href*="/wallet"]' },
        { pattern: /^\/analytics/, selector: 'a[href*="/analytics"]' },
        { pattern: /^\/settings/, selector: 'a[href*="/settings"]' },
        { pattern: /^\/notifications/, selector: 'a[href*="/notifications"]' },
        { pattern: /^\/admin\/admins/, selector: 'a[href*="/admin/admins"]' },
        { pattern: /^\/admin\/senders/, selector: 'a[href*="/admin/senders"]' },
        { pattern: /^\/admin\/payments/, selector: 'a[href*="/admin/payments"]' },
    ];

    const FLASH_ICONS = {
        success: 'bi-check-circle-fill text-success',
        error: 'bi-exclamation-triangle-fill text-danger',
        warning: 'bi-exclamation-circle-fill text-warning',
        info: 'bi-info-circle-fill text-info',
    };

    function updateSidebarActiveState(pathname) {
        document.querySelectorAll('.sidebar-nav-item.active').forEach((el) => {
            el.classList.remove('active');
        });

        for (const route of ROUTE_PATTERNS) {
            if (!route.pattern.test(pathname)) {
                continue;
            }

            const link = document.querySelector(`.sidebar ${route.selector}`);
            if (link) {
                link.classList.add('active');
            }
            break;
        }
    }

    function updateHeaderTitle(frame) {
        const title =
            frame?.dataset?.pageTitle ||
            frame?.querySelector('[data-page-title]')?.dataset?.pageTitle ||
            frame?.querySelector('.page-main-title, .page-title, .chat-user-name')?.textContent?.trim();

        if (!title) {
            return;
        }

        document.querySelectorAll('.header-page-title').forEach((el) => {
            el.textContent = title;
        });

        const appName = document.title.split(' - ').pop() || 'BulkSMS CRM';
        document.title = `${title} - ${appName}`;
    }

    function showFlashToast(message, type) {
        const container = document.getElementById('flash-toast-container');
        if (!container || !message) {
            return;
        }

        const toast = document.createElement('div');
        toast.className = `flash-toast flash-toast-${type}`;
        toast.innerHTML = `
            <i class="bi ${FLASH_ICONS[type] || FLASH_ICONS.info} flash-toast-icon"></i>
            <div class="flash-toast-body"></div>
            <button type="button" class="flash-toast-close" aria-label="Dismiss">
                <i class="bi bi-x-lg"></i>
            </button>
        `;
        toast.querySelector('.flash-toast-body').textContent = message;
        toast.querySelector('.flash-toast-close').addEventListener('click', () => toast.remove());
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(1rem)';
            toast.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            setTimeout(() => toast.remove(), 200);
        }, 5000);
    }

    function showFlashFromFrame(frame) {
        const payload = frame.querySelector('#flash-payload');
        if (!payload) {
            return;
        }

        [
            ['success', payload.dataset.flashSuccess],
            ['error', payload.dataset.flashError],
            ['warning', payload.dataset.flashWarning],
            ['info', payload.dataset.flashInfo],
        ].forEach(([type, message]) => {
            if (message) {
                showFlashToast(message, type);
            }
        });
    }

    function scrollChatToBottom(frame) {
        const container = frame.querySelector('#messagesContainer');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    function syncChatRefreshState(frame) {
        if (frame.id !== 'chat-panel') {
            return;
        }

        const input = frame.querySelector('#messageInput');
        const hasDraft = (input?.value || '').trim().length > 0;
        const isFocused = document.activeElement === input;

        if (hasDraft || isFocused) {
            frame.removeAttribute('refresh');
        } else if (!frame.getAttribute('refresh')) {
            frame.setAttribute('refresh', 'every 10s');
        }
    }

    function initSidebarTooltips() {
        if (typeof bootstrap === 'undefined') {
            return;
        }

        document.querySelectorAll('.sidebar-nav-item[data-bs-toggle="tooltip"]').forEach((el) => {
            const existing = bootstrap.Tooltip.getInstance(el);
            if (existing) {
                existing.dispose();
            }
            new bootstrap.Tooltip(el, { trigger: 'hover', placement: 'right' });
        });
    }

    function initAutoFilterForms(root) {
        root.querySelectorAll('form[data-auto-filter]').forEach((form) => {
            if (form.dataset.autoFilterBound === 'true') {
                return;
            }
            form.dataset.autoFilterBound = 'true';

            if (!form.dataset.turboFrame) {
                form.setAttribute('data-turbo-frame', 'main-content');
            }
            form.setAttribute('data-turbo-action', 'replace');

            let timer;
            let pageInput = form.querySelector('input[name="page"]');
            if (!pageInput) {
                pageInput = document.createElement('input');
                pageInput.type = 'hidden';
                pageInput.name = 'page';
                pageInput.value = '1';
                form.appendChild(pageInput);
            }

            function submitForm() {
                pageInput.value = '1';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }

            form.querySelectorAll('select, input[type="date"]').forEach((el) => {
                el.addEventListener('change', submitForm);
            });

            form.querySelectorAll('[data-auto-filter-search]').forEach((el) => {
                el.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(submitForm, 400);
                });
            });

            form.querySelectorAll('input[type="text"]:not([data-auto-filter-search]), input[type="search"]').forEach((el) => {
                if (el.dataset.autoFilterSearch !== undefined) {
                    return;
                }
                el.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        submitForm();
                    }
                });
            });
        });
    }

    function loadExternalScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async function evaluateFrameScripts(frame) {
        const externalScripts = [...frame.querySelectorAll('script[src][data-turbo-eval]')];
        for (const script of externalScripts) {
            await loadExternalScript(script.src);
        }

        frame.querySelectorAll('script[data-turbo-eval]:not([src])').forEach((script) => {
            const runnable = document.createElement('script');
            runnable.textContent = script.textContent;
            script.replaceWith(runnable);
        });
    }

    function initBootstrapInRoot(root) {
        if (typeof bootstrap === 'undefined') {
            return;
        }

        root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
            const existing = bootstrap.Tooltip.getInstance(el);
            if (existing) {
                existing.dispose();
            }
            new bootstrap.Tooltip(el);
        });
    }

    function animateCards(root) {
        root.querySelectorAll('.card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('fade-in');
        });
    }

    function closeMobileSidebar() {
        document.getElementById('sidebar')?.classList.remove('show');
        document.getElementById('sidebarOverlay')?.classList.remove('show');
    }

    function handleFrameLoad(event) {
        const frame = event.target;

        if (frame.id === 'main-content') {
            updateSidebarActiveState(window.location.pathname);
            updateHeaderTitle(frame);
            closeMobileSidebar();
        }

        if (frame.id === 'chat-panel') {
            scrollChatToBottom(frame);
            syncChatRefreshState(frame);
        }

        showFlashFromFrame(frame);
        initAutoFilterForms(frame);
        initBootstrapInRoot(frame);
        animateCards(frame);
        evaluateFrameScripts(frame);

        document.dispatchEvent(new CustomEvent('bulksms:frame-load', { detail: { frame } }));
    }

    function setFrameLoadingState(event) {
        const frame = event.target;
        if (frame.id === 'main-content' || frame.id === 'chat-panel') {
            frame.classList.add('turbo-frame-loading');
        }
    }

    function clearFrameLoadingState(event) {
        const frame = event.target;
        frame.classList.remove('turbo-frame-loading');
    }

    document.addEventListener('turbo:frame-load', handleFrameLoad);
    document.addEventListener('turbo:frame-request', setFrameLoadingState);
    document.addEventListener('turbo:frame-render', clearFrameLoadingState);

    document.addEventListener('focusin', (event) => {
        const frame = event.target.closest('turbo-frame#chat-panel');
        if (frame && event.target.matches('.reply-textarea')) {
            frame.removeAttribute('refresh');
        }
    });

    document.addEventListener('focusout', (event) => {
        const frame = event.target.closest('turbo-frame#chat-panel');
        if (frame && event.target.matches('.reply-textarea')) {
            syncChatRefreshState(frame);
        }
    });

    document.addEventListener('input', (event) => {
        const frame = event.target.closest('turbo-frame#chat-panel');
        if (frame && event.target.matches('.reply-textarea')) {
            syncChatRefreshState(frame);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const mainFrame = document.getElementById('main-content');
        if (mainFrame) {
            showFlashFromFrame(mainFrame);
            initAutoFilterForms(mainFrame);
            initSidebarTooltips();
        }
    });

    window.BulkSmsTurbo = { showFlashToast };
})();
