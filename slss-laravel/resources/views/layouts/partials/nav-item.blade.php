{{-- One sidebar entry. The title doubles as the tooltip when the sidebar is collapsed to icons. --}}
<div class="sidebar-menu-item">
    <a href="{{ $route }}" class="sidebar-menu-link {{ $active ? 'active' : '' }}" title="{{ $label }}" @if($active) aria-current="page" @endif>
        <i class="fas {{ $icon }}" aria-hidden="true"></i>
        <span>{{ $label }}</span>
    </a>
</div>
