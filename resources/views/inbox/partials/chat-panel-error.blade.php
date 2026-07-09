<turbo-frame id="chat-panel">
    <div class="modern-page-container">
        <div class="modern-card">
            <div class="modern-card-body text-center py-5">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2.5rem;"></i>
                <h5 class="mt-3 mb-2">Unable to load conversation</h5>
                <p class="text-muted mb-4">{{ $message }}</p>
                <a href="{{ route('inbox.index') }}" class="btn-secondary-modern" data-turbo-frame="main-content" data-turbo-action="advance">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Inbox</span>
                </a>
            </div>
        </div>
    </div>
</turbo-frame>
