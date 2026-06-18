<turbo-frame id="chat-panel">
    @include('components.flash-payload')
    @include('inbox.partials.chat-panel-content', compact('conversation', 'messages'))
</turbo-frame>
