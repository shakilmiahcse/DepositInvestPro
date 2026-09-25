/**
 * PWA Service Worker Registration & Installation Prompt Handler
 */
(function () {
    'use strict';

    // 1. Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            var swMeta = document.querySelector('meta[name="pwa-sw"]');
            var swUrl = (swMeta && swMeta.getAttribute('content')) ? swMeta.getAttribute('content') : './sw.js';
            navigator.serviceWorker.register(swUrl)
                .then(function (registration) {
                    console.log('PWA ServiceWorker registered with scope: ', registration.scope);
                })
                .catch(function (err) {
                    console.warn('PWA ServiceWorker registration failed: ', err);
                });
        });
    }

    // 2. Handle PWA Install Prompt
    var deferredPrompt = null;
    var banner = document.getElementById('pwaInstallBanner');
    var installBtn = document.getElementById('pwaInstallBtn');
    var closeBtn = document.getElementById('pwaCloseBtn');

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;

        // Check if user dismissed it recently (last 7 days)
        var dismissedTime = localStorage.getItem('pwa_install_dismissed');
        if (dismissedTime && (Date.now() - parseInt(dismissedTime, 10)) < (7 * 24 * 60 * 60 * 1000)) {
            return;
        }

        if (banner) {
            banner.style.display = 'flex';
        }
    });

    if (installBtn) {
        installBtn.addEventListener('click', function () {
            if (!deferredPrompt) {
                return;
            }
            if (banner) banner.style.display = 'none';
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function (choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the PWA install prompt');
                }
                deferredPrompt = null;
            });
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            if (banner) banner.style.display = 'none';
            localStorage.setItem('pwa_install_dismissed', Date.now().toString());
        });
    }

    // 3. Quick Copy helper for Account Number
    window.copyAccountNumber = function (accNo, label) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(accNo).then(function () {
                if (typeof $.toast === 'function') {
                    $.toast({
                        heading: label || 'Copied!',
                        text: accNo,
                        position: 'top-right',
                        loaderBg: '#2563eb',
                        icon: 'success',
                        hideAfter: 2500,
                        stack: 3
                    });
                } else {
                    alert((label || 'Copied: ') + accNo);
                }
            });
        }
    };
})();
