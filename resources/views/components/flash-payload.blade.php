@if(session('success') || session('error') || session('warning') || session('info'))
<div id="flash-payload" class="visually-hidden" aria-hidden="true"
    @if(session('success')) data-flash-success="{{ session('success') }}" @endif
    @if(session('error')) data-flash-error="{{ session('error') }}" @endif
    @if(session('warning')) data-flash-warning="{{ session('warning') }}" @endif
    @if(session('info')) data-flash-info="{{ session('info') }}" @endif
></div>
@endif
