@extends('layouts.admin')

@php
    $statusLabelClasses = [
        'idle' => 'label-default',
        'starting' => 'label-info',
        'running' => 'label-warning',
        'completed' => 'label-success',
        'failed' => 'label-danger',
    ];

    $statusBoxClasses = [
        'idle' => 'box-primary',
        'starting' => 'box-warning',
        'running' => 'box-warning',
        'completed' => 'box-success',
        'failed' => 'box-danger',
    ];

    $initialStatus = $updater['status'] ?? 'idle';
    $initialStatusClass = $statusLabelClasses[$initialStatus] ?? 'label-default';
    $initialBoxClass = $statusBoxClasses[$initialStatus] ?? 'box-primary';
    $updaterRoutesAvailable = \Illuminate\Support\Facades\Route::has('admin.panel-updates.status')
        && \Illuminate\Support\Facades\Route::has('admin.panel-updates.store');
    $statusEndpoint = $updaterRoutesAvailable ? route('admin.panel-updates.status') : url('/admin/panel-updates/status');
    $startEndpoint = $updaterRoutesAvailable ? route('admin.panel-updates.store') : url('/admin/panel-updates');
    $updaterTranslations = [
        'status_labels' => [
            'idle' => trans('admin/update.status.idle'),
            'starting' => trans('admin/update.status.starting'),
            'running' => trans('admin/update.status.running'),
            'completed' => trans('admin/update.status.completed'),
            'failed' => trans('admin/update.status.failed'),
        ],
        'status_descriptions' => [
            'idle' => trans('admin/update.status_description.idle'),
            'starting' => trans('admin/update.status_description.starting'),
            'running' => trans('admin/update.status_description.running'),
            'completed' => trans('admin/update.status_description.completed'),
            'failed' => trans('admin/update.status_description.failed'),
        ],
        'system_up_to_date' => trans('admin/update.system.up_to_date'),
        'system_update_available' => trans('admin/update.system.update_available'),
        'system_status_text_latest' => trans('admin/update.system.status_text_latest'),
        'system_status_text_update' => trans('admin/update.system.status_text_update'),
        'system_check_result_update' => trans('admin/update.system.check_result_update', ['version' => ':version']),
        'system_check_result_none' => trans('admin/update.system.check_result_none', ['version' => ':version']),
        'system_check_result_error' => trans('admin/update.system.check_result_error'),
        'start_confirm_title' => trans('admin/update.automatic.start_confirm_title'),
        'start_confirm_text' => trans('admin/update.automatic.start_confirm_text'),
        'start_confirm_button' => trans('admin/update.automatic.start_confirm_button'),
        'start_success_title' => trans('admin/update.automatic.start_success_title'),
        'start_success_text' => trans('admin/update.automatic.start_success_text'),
        'refresh_error_title' => trans('admin/update.automatic.refresh_error_title'),
        'refresh_error_text' => trans('admin/update.automatic.refresh_error_text'),
        'start_error_title' => trans('admin/update.automatic.start_error_title'),
        'generic_error' => trans('admin/update.automatic.generic_error'),
        'panel_unavailable' => trans('admin/update.automatic.panel_unavailable'),
        'log_empty' => trans('admin/update.automatic.log_empty'),
        'unsupported_platform' => trans('admin/update.automatic.unsupported_platform'),
        'no_update_available' => trans('admin/update.automatic.no_update_available'),
        'skip_chown_note' => trans('admin/update.automatic.skip_chown_note'),
        'ready_note' => trans('admin/update.automatic.ready_note'),
        'route_cache_note' => trans('admin/update.automatic.route_cache_note'),
        'ownership_repair_skipped' => trans('admin/update.automatic.ownership_repair_skipped'),
        'ownership_repair_enabled' => trans('admin/update.automatic.ownership_repair_enabled', ['user' => ':user', 'group' => ':group']),
    ];
@endphp

@section('title')
    @lang('admin/update.overview.page_title')
@endsection

@section('content-header')
    <h1>@lang('admin/update.overview.page_title')<small>@lang('admin/update.overview.page_description')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/update.overview.admin')</a></li>
        <li class="active">@lang('admin/update.overview.index')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 col-xs-12">
        <div class="box {{ $version->isLatestPanel() ? 'box-success' : 'box-danger' }}" id="panel-version-box">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/update.system.title')</h3>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>@lang('admin/update.system.current_version')</dt>
                    <dd><code id="panel-version-current">{{ config('app.version') }}</code></dd>

                    <dt>@lang('admin/update.system.latest_version')</dt>
                    <dd>
                        <a href="{{ $version->getPanelReleaseUrl() }}" target="_blank" rel="noopener noreferrer" id="panel-version-release-link">
                            <code id="panel-version-latest">{{ $version->getPanel() }}</code>
                        </a>
                    </dd>

                    <dt>@lang('admin/update.system.release_channel')</dt>
                    <dd><a href="{{ $version->getPanelReleaseUrl() }}" target="_blank" rel="noopener noreferrer">@lang('admin/update.system.view_release')</a></dd>

                    <dt>@lang('admin/update.system.status')</dt>
                    <dd>
                        <span class="label {{ $version->isLatestPanel() ? 'label-success' : 'label-danger' }}" id="panel-version-status-label">
                            {{ $version->isLatestPanel() ? trans('admin/update.system.up_to_date') : trans('admin/update.system.update_available') }}
                        </span>
                    </dd>
                </dl>
                <p class="text-muted" id="panel-version-status-text">
                    @if ($version->isLatestPanel())
                        @lang('admin/update.system.status_text_latest')
                    @else
                        @lang('admin/update.system.status_text_update')
                    @endif
                </p>
                <div class="btn-group" role="group" aria-label="Panel version actions">
                    <button
                        type="button"
                        class="btn btn-default btn-sm"
                        id="check-panel-version"
                        @if(!$updaterRoutesAvailable) disabled="disabled" @endif
                    >
                        @lang('admin/update.system.check_button')
                    </button>
                </div>
                <div class="alert alert-info" id="panel-version-check-feedback" style="display: none; margin-top: 12px; margin-bottom: 0;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xs-12">
        <div class="box {{ $initialBoxClass }}" id="panel-updater-box">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/update.automatic.title')</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">@lang('admin/update.automatic.description')</p>

                <div class="alert alert-warning" id="panel-updater-maintenance-warning">
                    @lang('admin/update.automatic.maintenance_warning')
                </div>

                <dl class="dl-horizontal">
                    <dt>@lang('admin/update.system.current_version')</dt>
                    <dd><code id="panel-updater-current-version">{{ $updater['current_version'] }}</code></dd>

                    <dt>@lang('admin/update.system.latest_version')</dt>
                    <dd><code id="panel-updater-target-version">{{ $updater['target_version'] }}</code></dd>

                    <dt>@lang('admin/update.automatic.detected_owner')</dt>
                    <dd id="panel-updater-owner">{{ ($updater['detected_user'] ?? 'unknown') . ':' . ($updater['detected_group'] ?? 'unknown') }}</dd>

                    <dt>@lang('admin/update.automatic.ownership_repair')</dt>
                    <dd id="panel-updater-ownership">
                        @if ($updater['will_skip_chown'])
                            @lang('admin/update.automatic.ownership_repair_skipped')
                        @else
                            @lang('admin/update.automatic.ownership_repair_enabled', ['user' => $updater['detected_user'] ?? 'www-data', 'group' => $updater['detected_group'] ?? 'www-data'])
                        @endif
                    </dd>

                    <dt>@lang('admin/update.automatic.update_status')</dt>
                    <dd>
                        <span class="label {{ $initialStatusClass }}" id="panel-updater-status-label">
                            @lang('admin/update.status.' . $initialStatus)
                        </span>
                    </dd>

                    <dt>@lang('admin/update.automatic.started_at')</dt>
                    <dd id="panel-updater-started-at">{{ $updater['started_at'] ?? '-' }}</dd>

                    <dt>@lang('admin/update.automatic.completed_at')</dt>
                    <dd id="panel-updater-completed-at">{{ $updater['completed_at'] ?? '-' }}</dd>
                </dl>

                <p class="text-muted" id="panel-updater-status-detail">
                    @if (!empty($updater['status_detail']))
                        {{ $updater['status_detail'] }}
                    @else
                        @lang('admin/update.status_description.' . $initialStatus)
                    @endif
                </p>

                <div class="btn-group" role="group" aria-label="Panel updater actions">
                    <button
                        type="button"
                        class="btn btn-success"
                        id="start-panel-update"
                        @if(!$updater['can_start'] || !$updaterRoutesAvailable) disabled="disabled" @endif
                    >
                        @lang('admin/update.automatic.start_button')
                    </button>
                    <button type="button" class="btn btn-default" id="refresh-panel-update" @if(!$updaterRoutesAvailable) disabled="disabled" @endif>
                        @lang('admin/update.automatic.refresh_button')
                    </button>
                </div>

                <p class="small text-muted" id="panel-updater-action-note" style="margin-top: 12px; margin-bottom: 0;">
                    @if (!$updaterRoutesAvailable)
                        @lang('admin/update.automatic.route_cache_note')
                    @elseif (!$updater['supported'])
                        @lang('admin/update.automatic.unsupported_platform')
                    @elseif (!$updater['update_available'])
                        @lang('admin/update.automatic.no_update_available')
                    @elseif ($updater['will_skip_chown'])
                        @lang('admin/update.automatic.skip_chown_note')
                    @else
                        @lang('admin/update.automatic.ready_note')
                    @endif
                </p>
            </div>
            <div class="box-footer">
                <strong>@lang('admin/update.automatic.log_title')</strong>
                <pre id="panel-updater-log" style="margin-top: 10px; margin-bottom: 0; max-height: 220px; overflow: auto;">{{ $updater['log_excerpt'] ?? trans('admin/update.automatic.log_empty') }}</pre>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="{{ $version->getDiscord() }}"><button class="btn btn-warning" style="width:100%;"><i class="fa fa-fw fa-support"></i> Get Help <small>(via Discord)</small></button></a>
    </div>
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="https://pterodactyl.io"><button class="btn btn-primary" style="width:100%;"><i class="fa fa-fw fa-link"></i> Documentation</button></a>
    </div>
    <div class="clearfix visible-xs-block">&nbsp;</div>
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="{{ $version->getPanelRepositoryUrl() }}"><button class="btn btn-primary" style="width:100%;"><i class="fa fa-fw fa-support"></i> GitHub</button></a>
    </div>
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="{{ $version->getDonations() }}"><button class="btn btn-success" style="width:100%;"><i class="fa fa-fw fa-money"></i> Support the Project</button></a>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        (function () {
            var state = @json($updater);
            var updaterRoutesAvailable = @json($updaterRoutesAvailable);
            var statusRoute = @json($statusEndpoint);
            var startRoute = @json($startEndpoint);
            var releaseUrl = @json($version->getPanelReleaseUrl());
            var csrfToken = @json(csrf_token());
            var pollHandle = null;

            var statusLabelClasses = {
                idle: 'label-default',
                starting: 'label-info',
                running: 'label-warning',
                completed: 'label-success',
                failed: 'label-danger',
            };

            var boxClasses = {
                idle: 'box-primary',
                starting: 'box-warning',
                running: 'box-warning',
                completed: 'box-success',
                failed: 'box-danger',
            };

            var translations = @json($updaterTranslations);

            function format(template, replacements) {
                var output = template;
                Object.keys(replacements || {}).forEach(function (key) {
                    output = output.replace(new RegExp(':' + key, 'g'), replacements[key]);
                });

                return output;
            }

            function getStatusLabel(status) {
                return translations.status_labels[status] || status;
            }

            function getStatusDescription(data) {
                if (data.status_detail) {
                    return data.status_detail;
                }

                return translations.status_descriptions[data.status] || '';
            }

            function getOwnershipText(data) {
                if (data.will_skip_chown) {
                    return translations.ownership_repair_skipped;
                }

                return format(translations.ownership_repair_enabled, {
                    user: data.detected_user || 'www-data',
                    group: data.detected_group || 'www-data',
                });
            }

            function getActionNote(data) {
                if (!updaterRoutesAvailable) {
                    return translations.route_cache_note;
                }

                if (!data.supported) {
                    return translations.unsupported_platform;
                }

                if (!data.update_available) {
                    return translations.no_update_available;
                }

                if (data.is_running) {
                    return translations.panel_unavailable;
                }

                if (data.will_skip_chown) {
                    return translations.skip_chown_note;
                }

                return translations.ready_note;
            }

            function updateSystemInfo(data) {
                var upToDate = !data.update_available;

                $('#panel-version-box')
                    .removeClass('box-success box-danger')
                    .addClass(upToDate ? 'box-success' : 'box-danger');

                $('#panel-version-current').text(data.current_version || '');
                $('#panel-version-latest').text(data.latest_version || '');
                $('#panel-version-release-link').attr('href', data.release_url || releaseUrl);
                $('#panel-version-status-label')
                    .removeClass('label-success label-danger')
                    .addClass(upToDate ? 'label-success' : 'label-danger')
                    .text(upToDate ? translations.system_up_to_date : translations.system_update_available);
                $('#panel-version-status-text').text(upToDate ? translations.system_status_text_latest : translations.system_status_text_update);
            }

            function setVersionCheckFeedback(message, level) {
                $('#panel-version-check-feedback')
                    .removeClass('alert-info alert-success alert-warning alert-danger')
                    .addClass(level === 'danger' ? 'alert-danger' : (level === 'warning' ? 'alert-warning' : 'alert-success'))
                    .text(message)
                    .show();
            }

            function updateActions(data) {
                $('#start-panel-update').prop('disabled', !updaterRoutesAvailable || !data.can_start);
                $('#refresh-panel-update').prop('disabled', !updaterRoutesAvailable);
                $('#check-panel-version').prop('disabled', !updaterRoutesAvailable || data.is_running);
                $('#panel-updater-action-note').text(getActionNote(data));
            }

            function render(data) {
                state = data;

                $('#panel-updater-box')
                    .removeClass('box-primary box-warning box-success box-danger')
                    .addClass(boxClasses[data.status] || 'box-primary');

                $('#panel-updater-current-version').text(data.current_version || '');
                $('#panel-updater-target-version').text(data.target_version || data.latest_version || '');
                $('#panel-updater-owner').text((data.detected_user || 'unknown') + ':' + (data.detected_group || 'unknown'));
                $('#panel-updater-ownership').text(getOwnershipText(data));
                $('#panel-updater-status-label')
                    .removeClass('label-default label-info label-warning label-success label-danger')
                    .addClass(statusLabelClasses[data.status] || 'label-default')
                    .text(getStatusLabel(data.status));
                $('#panel-updater-started-at').text(data.started_at || '-');
                $('#panel-updater-completed-at').text(data.completed_at || '-');
                $('#panel-updater-status-detail').text(getStatusDescription(data));
                $('#panel-updater-log').text(data.log_excerpt || translations.log_empty);

                updateActions(data);
                updateSystemInfo(data);
                updatePollingState();
            }

            function extractError(xhr) {
                if (xhr && xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.length > 0) {
                    return xhr.responseJSON.errors[0].detail;
                }

                return translations.generic_error;
            }

            function refreshStatus(showError, options) {
                options = options || {};

                $.ajax({
                    type: 'GET',
                    url: statusRoute,
                    dataType: 'json',
                    data: {
                        refresh_version: options.forceVersionCheck ? 1 : (showError ? 1 : 0),
                    },
                }).done(function (response) {
                    render(response.data);

                    if (options.reportVersionCheck) {
                        setVersionCheckFeedback(
                            response.data.update_available
                                ? format(translations.system_check_result_update, { version: response.data.latest_version || response.data.target_version || '' })
                                : format(translations.system_check_result_none, { version: response.data.latest_version || response.data.current_version || '' }),
                            response.data.update_available ? 'warning' : 'success'
                        );
                    }
                }).fail(function () {
                    if (options.reportVersionCheck) {
                        setVersionCheckFeedback(translations.system_check_result_error, 'danger');
                    }

                    if (!state.is_running) {
                        if (showError) {
                            swal(translations.refresh_error_title, translations.refresh_error_text, 'error');
                        }

                        return;
                    }

                    $('#panel-updater-status-detail').text(translations.panel_unavailable);
                    updateActions($.extend({}, state, { is_running: true }));
                });
            }

            function updatePollingState() {
                if (pollHandle) {
                    window.clearInterval(pollHandle);
                    pollHandle = null;
                }

                if (!state.is_running) {
                    return;
                }

                pollHandle = window.setInterval(function () {
                    refreshStatus(false);
                }, 5000);
            }

            $('#refresh-panel-update').on('click', function () {
                refreshStatus(true);
            });

            $('#check-panel-version').on('click', function () {
                if ($(this).prop('disabled')) {
                    return;
                }

                refreshStatus(false, {
                    forceVersionCheck: true,
                    reportVersionCheck: true,
                });
            });

            $('#start-panel-update').on('click', function () {
                if ($(this).prop('disabled')) {
                    return;
                }

                swal({
                    title: translations.start_confirm_title,
                    text: translations.start_confirm_text,
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#00a65a',
                    confirmButtonText: translations.start_confirm_button,
                }, function (confirmed) {
                    if (confirmed === false) {
                        return;
                    }

                    $.ajax({
                        type: 'POST',
                        url: startRoute,
                        dataType: 'json',
                        headers: {
                            'Accept': 'application/json',
                        },
                        data: {
                            _token: csrfToken,
                        },
                    }).done(function (response) {
                        render(response.data);
                        swal(translations.start_success_title, translations.start_success_text, 'success');
                    }).fail(function (xhr) {
                        swal(translations.start_error_title, extractError(xhr), 'error');
                    });
                });
            });

            render(state);
        })();
    </script>
@endsection
