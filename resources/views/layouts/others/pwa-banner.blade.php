<!-- PWA Floating Install Banner for Mobile -->
<div id="pwaInstallBanner" class="pwa-install-banner">
    <div class="pwa-info">
        <img src="{{ asset('public/images/icons/icon-96x96.png') }}" class="pwa-icon" alt="App Icon">
        <div>
            <h4 class="pwa-title">{{ get_option('site_title', 'Smart Banking') }}</h4>
            <p class="pwa-subtitle">{{ _lang('Install app for faster access') }}</p>
        </div>
    </div>
    <div class="pwa-actions">
        <button id="pwaInstallBtn" class="pwa-btn-install">
            <i class="fas fa-download mr-1"></i> {{ _lang('Install') }}
        </button>
        <button id="pwaCloseBtn" class="pwa-btn-close" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
