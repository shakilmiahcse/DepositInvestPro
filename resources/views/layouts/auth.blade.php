<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; img-src 'self' data: blob: https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com https://www.recaptcha.net; connect-src 'self' https://www.google.com https://www.gstatic.com https://www.recaptcha.net; frame-src https://www.google.com https://www.gstatic.com https://www.recaptcha.net; navigate-to 'self';">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ get_favicon() }}">

    <title>{{ get_option('site_title', config('app.name')) }}</title>

    <!-- Google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('public/backend/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link href="{{ asset('public/auth/css/app.css') . '?v=' . filemtime(public_path('auth/css/app.css')) }}" rel="stylesheet">
    <script>
        (function () {
            var allowedOrigins = [window.location.origin];

            function isAllowed(url) {
                try {
                    return allowedOrigins.indexOf(new URL(url, window.location.href).origin) !== -1;
                } catch (error) {
                    return false;
                }
            }

            function blockExternalNavigation(url) {
                if (!url || isAllowed(url)) {
                    return false;
                }

                console.warn('Blocked external navigation attempt:', url);
                return true;
            }

            var originalOpen = window.open;
            window.open = function (url, target, features) {
                if (blockExternalNavigation(url)) {
                    return null;
                }

                return originalOpen.call(window, url, target, features);
            };

            var originalAssign = window.location.assign.bind(window.location);
            var originalReplace = window.location.replace.bind(window.location);

            window.location.assign = function (url) {
                if (!blockExternalNavigation(url)) {
                    originalAssign(url);
                }
            };

            window.location.replace = function (url) {
                if (!blockExternalNavigation(url)) {
                    originalReplace(url);
                }
            };

            document.addEventListener('click', function (event) {
                var link = event.target.closest('a[href]');
                if (link && blockExternalNavigation(link.href)) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }, true);

            document.addEventListener('submit', function (event) {
                var action = event.target.getAttribute('action');
                if (action && blockExternalNavigation(action)) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }, true);

            function blockForeignScriptElement(node) {
                if (!node || node.tagName !== 'SCRIPT') {
                    return;
                }

                var src = node.getAttribute('src');
                if (src && blockExternalNavigation(src)) {
                    node.remove();
                }
            }

            var originalAppendChild = Element.prototype.appendChild;
            Element.prototype.appendChild = function (node) {
                blockForeignScriptElement(node);
                return originalAppendChild.call(this, node);
            };

            var originalInsertBefore = Element.prototype.insertBefore;
            Element.prototype.insertBefore = function (node, referenceNode) {
                blockForeignScriptElement(node);
                return originalInsertBefore.call(this, node, referenceNode);
            };

            new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    mutation.addedNodes.forEach(function (node) {
                        blockForeignScriptElement(node);
                    });
                });
            }).observe(document.documentElement, {
                childList: true,
                subtree: true
            });
        })();
    </script>
</head>
<body>
    <div id="app">
        <main class="py-4">
            @yield('content')
        </main>
    </div>
	
	@yield('js-script')
</body>
</html>
