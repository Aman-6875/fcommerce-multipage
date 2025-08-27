<div class="language-switcher dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        @if(app()->getLocale() == 'bn')
            <i class="fas fa-globe"></i> বাংলা
        @else
            <i class="fas fa-globe"></i> English
        @endif
    </button>
    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
        <li>
            <a class="dropdown-item {{ app()->getLocale() == 'bn' ? 'active' : '' }}" 
               href="{{ request()->fullUrlWithQuery(['lang' => 'bn']) }}">
                🇧🇩 বাংলা
            </a>
        </li>
        <li>
            <a class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}" 
               href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}">
                🇺🇸 English
            </a>
        </li>
    </ul>
</div>

<style>
.language-switcher .dropdown-item.active {
    background-color: #667eea;
    color: white;
}
.language-switcher .dropdown-item:hover {
    background-color: #f8f9fa;
}
</style>