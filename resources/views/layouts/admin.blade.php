<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{ config('app.name', 'Pterodactyl') }} - @yield('title')</title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta name="_token" content="{{ csrf_token() }}">

        <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
        <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
        <link rel="manifest" href="/favicons/manifest.json">
        <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
        <link rel="shortcut icon" href="/favicons/favicon.ico">
        <meta name="msapplication-config" content="/favicons/browserconfig.xml">
        <meta name="theme-color" content="#0e4688">

        @include('layouts.scripts')

        @section('scripts')
            {!! Theme::css('vendor/select2/select2.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/bootstrap/bootstrap.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/adminlte/admin.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/adminlte/colors/skin-blue.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/sweetalert/sweetalert.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/animate/animate.min.css?t={cache-version}') !!}
            {!! Theme::css('css/pterodactyl.css?t={cache-version}') !!}
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">

            <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->
        @show
    </head>
    <body class="hold-transition skin-blue fixed sidebar-mini">
        <div class="wrapper">
            <header class="main-header">
                <a href="{{ route('index') }}" class="logo">
                    <span>{{ config('app.name', 'Pterodactyl') }}</span>
                </a>
                <nav class="navbar navbar-static-top">
                    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li class="user-menu">
                                <a href="{{ route('account') }}">
                                    <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(Auth::user()->email)) }}?s=160" class="user-image" alt="User Image">
                                    <span class="hidden-xs">{{ Auth::user()->name_first }} {{ Auth::user()->name_last }}</span>
                                </a>
                            </li>
                            <li>
                                <li><a href="{{ route('index') }}" data-toggle="tooltip" data-placement="bottom" title="Exit Admin Control"><i class="fa fa-server"></i></a></li>
                            </li>
                            <li>
                                <li><a href="{{ route('auth.logout') }}" id="logoutButton" data-toggle="tooltip" data-placement="bottom" title="Logout"><i class="fa fa-sign-out"></i></a></li>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <aside class="main-sidebar">
                <section class="sidebar">
                    <ul class="sidebar-menu">
                        <li class="header">BASIC ADMINISTRATION</li>
                        <li class="{{ Route::currentRouteName() !== 'admin.index' ?: 'active' }}">
                            <a href="{{ route('admin.index') }}">
                                <i class="fa fa-home"></i> <span>Overview</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.settings') ?: 'active' }}">
                            <a href="{{ route('admin.settings')}}">
                                <i class="fa fa-wrench"></i> <span>Settings</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.api') ?: 'active' }}">
                            <a href="{{ route('admin.api.index')}}">
                                <i class="fa fa-gamepad"></i> <span>Application API</span>
                            </a>
                        </li>
                        <li class="header">MANAGEMENT</li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.databases') ?: 'active' }}">
                            <a href="{{ route('admin.databases') }}">
                                <i class="fa fa-database"></i> <span>Databases</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.locations') ?: 'active' }}">
                            <a href="{{ route('admin.locations') }}">
                                <i class="fa fa-globe"></i> <span>Locations</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.nodes') ?: 'active' }}">
                            <a href="{{ route('admin.nodes') }}">
                                <i class="fa fa-sitemap"></i> <span>Nodes</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.servers') ?: 'active' }}">
                            <a href="{{ route('admin.servers') }}">
                                <i class="fa fa-server"></i> <span>Servers</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.users') ?: 'active' }}">
                            <a href="{{ route('admin.users') }}">
                                <i class="fa fa-users"></i> <span>Users</span>
                            </a>
                        </li>
                        <li class="header">SERVICE MANAGEMENT</li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.mounts') ?: 'active' }}">
                            <a href="{{ route('admin.mounts') }}">
                                <i class="fa fa-magic"></i> <span>Mounts</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.nests') ?: 'active' }}">
                            <a href="{{ route('admin.nests') }}">
                                <i class="fa fa-th-large"></i> <span>Nests</span>
                            </a>
                        </li>
                    </ul>
                </section>
            </aside>
            <div class="content-wrapper">
                <section class="content-header">
                    @yield('content-header')
                </section>
                <section class="content">
                    <div class="row">
                        <div class="col-xs-12">
                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    There was an error validating the data provided.<br><br>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @foreach (Alert::getMessages() as $type => $messages)
                                @foreach ($messages as $message)
                                    <div class="alert alert-{{ $type }} alert-dismissable" role="alert">
                                        {{ $message }}
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    @yield('content')
                </section>
            </div>
            <footer class="main-footer">
                <div class="pull-right small text-gray" style="margin-right:10px;margin-top:-7px;">
                    <strong><i class="fa fa-fw {{ $appIsGit ? 'fa-git-square' : 'fa-code-fork' }}"></i></strong> {{ $appVersion }}<br />
                    <strong><i class="fa fa-fw fa-clock-o"></i></strong> {{ round(microtime(true) - LARAVEL_START, 3) }}s
                </div>
                Copyright &copy; 2015 - {{ date('Y') }} <a href="https://pterodactyl.io/">Pterodactyl Software</a>.
            </footer>
        </div>
        @section('footer-scripts')
            <script src="/js/keyboard.polyfill.js" type="application/javascript"></script>
            <script>keyboardeventKeyPolyfill.polyfill();</script>

            {!! Theme::js('vendor/jquery/jquery.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/sweetalert/sweetalert.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/bootstrap/bootstrap.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/slimscroll/jquery.slimscroll.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/adminlte/app.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/bootstrap-notify/bootstrap-notify.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/select2/select2.full.min.js?t={cache-version}') !!}
            {!! Theme::js('js/admin/functions.js?t={cache-version}') !!}
            <script src="/js/autocomplete.js" type="application/javascript"></script>

            @if(Auth::user()->root_admin)
                <script>
                    $('#logoutButton').on('click', function (event) {
                        event.preventDefault();

                        var that = this;
                        swal({
                            title: 'Do you want to log out?',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d9534f',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Log out'
                        }, function () {
                             $.ajax({
                                type: 'POST',
                                url: '{{ route('auth.logout') }}',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },complete: function () {
                                    window.location.href = '{{route('auth.login')}}';
                                }
                        });
                    });
                });
                </script>
            @endif

            <script>
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                })
            </script>

            @php($adminTranslationStrings = trans('admin-ui.strings'))
            <script>
                window.AdminI18n = (function () {
                    const translations = @json(is_array($adminTranslationStrings) ? $adminTranslationStrings : []);
                    const phraseKeys = Object.keys(translations).sort(function (a, b) {
                        return b.length - a.length;
                    });
                    const htmlTranslations = phraseKeys.reduce(function (carry, key) {
                        if (key.indexOf('<') === -1) {
                            return carry;
                        }

                        carry[normalizeWhitespace(key)] = translations[key];

                        return carry;
                    }, {});
                    const skippedTags = new Set(['CODE', 'PRE', 'SCRIPT', 'STYLE', 'TEXTAREA', 'NOSCRIPT']);
                    const htmlSafeTags = new Set(['DIV', 'P', 'SMALL', 'SPAN', 'LABEL', 'LI', 'A', 'TD']);

                    function normalizeWhitespace(value) {
                        return value.replace(/\s+/g, ' ').trim();
                    }

                    function preserveWhitespace(original, translated) {
                        const leading = original.match(/^\s*/)[0];
                        const trailing = original.match(/\s*$/)[0];

                        return leading + translated + trailing;
                    }

                    function translateText(value) {
                        if (typeof value !== 'string' || value.length === 0) {
                            return value;
                        }

                        const trimmed = value.trim();
                        if (trimmed.length === 0) {
                            return value;
                        }

                        if (Object.prototype.hasOwnProperty.call(translations, trimmed)) {
                            return preserveWhitespace(value, translations[trimmed]);
                        }

                        let translated = trimmed;
                        phraseKeys.forEach(function (key) {
                            if (key === trimmed || key.length < 6) {
                                return;
                            }

                            if (!/\s|[().:!?,'"-]/.test(key)) {
                                return;
                            }

                            if (translated.includes(key)) {
                                translated = translated.split(key).join(translations[key]);
                            }
                        });

                        if (translated === trimmed) {
                            return value;
                        }

                        return preserveWhitespace(value, translated);
                    }

                    function translateHtml(node) {
                        if (!htmlSafeTags.has(node.tagName) || node.querySelector('input, select, textarea, button, table, form')) {
                            return false;
                        }

                        const normalized = normalizeWhitespace(node.innerHTML || '');
                        if (!normalized || !Object.prototype.hasOwnProperty.call(htmlTranslations, normalized)) {
                            return false;
                        }

                        node.innerHTML = htmlTranslations[normalized];
                        return true;
                    }

                    function translateAttributes(node) {
                        ['title', 'placeholder', 'aria-label', 'alt'].forEach(function (attribute) {
                            if (!node.hasAttribute(attribute)) {
                                return;
                            }

                            const current = node.getAttribute(attribute);
                            const next = translateText(current);
                            if (current !== next) {
                                node.setAttribute(attribute, next);
                            }
                        });

                        if (node.tagName === 'INPUT') {
                            const type = (node.getAttribute('type') || '').toLowerCase();
                            if (['button', 'submit', 'reset'].includes(type) && node.hasAttribute('value')) {
                                const current = node.getAttribute('value');
                                const next = translateText(current);
                                if (current !== next) {
                                    node.setAttribute('value', next);
                                }
                            }
                        }

                        if (node.tagName === 'BUTTON' && node.hasAttribute('value')) {
                            const current = node.getAttribute('value');
                            const next = translateText(current);
                            if (current !== next) {
                                node.setAttribute('value', next);
                            }
                        }
                    }

                    function translateNode(node) {
                        if (!node) {
                            return;
                        }

                        if (node.nodeType === Node.TEXT_NODE) {
                            const parentTag = node.parentElement ? node.parentElement.tagName : '';
                            if (skippedTags.has(parentTag)) {
                                return;
                            }

                            const next = translateText(node.nodeValue);
                            if (node.nodeValue !== next) {
                                node.nodeValue = next;
                            }

                            return;
                        }

                        if (node.nodeType !== Node.ELEMENT_NODE || skippedTags.has(node.tagName)) {
                            return;
                        }

                        translateAttributes(node);
                        if (translateHtml(node)) {
                            return;
                        }
                        Array.from(node.childNodes).forEach(translateNode);
                    }

                    document.addEventListener('DOMContentLoaded', function () {
                        translateNode(document.body);
                        document.title = translateText(document.title);

                        const observer = new MutationObserver(function (mutations) {
                            mutations.forEach(function (mutation) {
                                mutation.addedNodes.forEach(translateNode);
                            });
                        });

                        observer.observe(document.body, {
                            childList: true,
                            subtree: true,
                        });
                    });

                    return {
                        translate: translateText,
                        translateNode: translateNode,
                    };
                })();
            </script>
        @show
    </body>
</html>
