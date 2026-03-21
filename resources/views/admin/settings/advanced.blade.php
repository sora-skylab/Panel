@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'advanced'])

@section('title')
    @lang('admin/settings.advanced_settings.page_title')
@endsection

@section('content-header')
    <h1>@lang('admin/settings.advanced_settings.page_title')<small>@lang('admin/settings.advanced_settings.page_description')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/settings.common.admin')</a></li>
        <li class="active">@lang('admin/settings.tabs.advanced')</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <div class="row">
        <div class="col-xs-12">
            <form action="" method="POST">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings.advanced_settings.captcha_box_title')</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.advanced_settings.captcha_provider')</label>
                                <div>
                                    <select class="form-control" name="recaptcha:provider">
                                        @php($captchaProvider = old('recaptcha:provider', config('recaptcha.provider', 'none')))
                                        <option value="none" @if($captchaProvider === 'none') selected @endif>@lang('admin/settings.advanced_settings.disabled')</option>
                                        <option value="recaptcha" @if($captchaProvider === 'recaptcha') selected @endif>@lang('admin/settings.advanced_settings.recaptcha')</option>
                                        <option value="turnstile" @if($captchaProvider === 'turnstile') selected @endif>@lang('admin/settings.advanced_settings.turnstile')</option>
                                    </select>
                                    <p class="text-muted small">@lang('admin/settings.advanced_settings.captcha_provider_description')</p>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.advanced_settings.recaptcha_site_key')</label>
                                <div>
                                    <input type="text" class="form-control" name="recaptcha:website_key" value="{{ old('recaptcha:website_key', config('recaptcha.website_key')) }}">
                                    <p class="text-muted small">@lang('admin/settings.advanced_settings.recaptcha_site_key_description')</p>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.advanced_settings.recaptcha_secret_key')</label>
                                <div>
                                    <input type="text" class="form-control" name="recaptcha:secret_key" value="{{ old('recaptcha:secret_key', config('recaptcha.secret_key')) }}">
                                    <p class="text-muted small">@lang('admin/settings.advanced_settings.recaptcha_secret_key_description')</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="control-label">@lang('admin/settings.advanced_settings.turnstile_site_key')</label>
                                <div>
                                    <input type="text" class="form-control" name="recaptcha:turnstile_website_key" value="{{ old('recaptcha:turnstile_website_key', config('recaptcha.turnstile_website_key')) }}">
                                    <p class="text-muted small">@lang('admin/settings.advanced_settings.turnstile_site_key_description')</p>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="control-label">@lang('admin/settings.advanced_settings.turnstile_secret_key')</label>
                                <div>
                                    <input type="text" class="form-control" name="recaptcha:turnstile_secret_key" value="{{ old('recaptcha:turnstile_secret_key', config('recaptcha.turnstile_secret_key')) }}">
                                    <p class="text-muted small">@lang('admin/settings.advanced_settings.turnstile_secret_key_description')</p>
                                </div>
                            </div>
                        </div>
                        @if($showRecaptchaWarning)
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="alert alert-warning no-margin">
                                        {!! trans('admin/settings.advanced_settings.recaptcha_warning', ['url' => 'https://www.google.com/recaptcha/admin']) !!}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings.advanced_settings.http_box_title')</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="control-label">@lang('admin/settings.advanced_settings.connection_timeout')</label>
                                <div>
                                    <input type="number" required class="form-control" name="pterodactyl:guzzle:connect_timeout" value="{{ old('pterodactyl:guzzle:connect_timeout', config('pterodactyl.guzzle.connect_timeout')) }}">
                                    <p class="text-muted small">@lang('admin/settings.advanced_settings.connection_timeout_description')</p>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="control-label">@lang('admin/settings.advanced_settings.request_timeout')</label>
                                <div>
                                    <input type="number" required class="form-control" name="pterodactyl:guzzle:timeout" value="{{ old('pterodactyl:guzzle:timeout', config('pterodactyl.guzzle.timeout')) }}">
                                    <p class="text-muted small">@lang('admin/settings.advanced_settings.request_timeout_description')</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings.advanced_settings.allocations_box_title')</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.advanced_settings.allocations_status')</label>
                                <div>
                                    <select class="form-control" name="pterodactyl:client_features:allocations:enabled">
                                        <option value="false">@lang('admin/settings.advanced_settings.disabled')</option>
                                        <option value="true" @if(old('pterodactyl:client_features:allocations:enabled', config('pterodactyl.client_features.allocations.enabled'))) selected @endif>@lang('admin/settings.advanced_settings.enabled')</option>
                                    </select>
                                    <p class="text-muted small">@lang('admin/settings.advanced_settings.allocations_status_description')</p>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.advanced_settings.starting_port')</label>
                                <div>
                                    <input type="number" class="form-control" name="pterodactyl:client_features:allocations:range_start" value="{{ old('pterodactyl:client_features:allocations:range_start', config('pterodactyl.client_features.allocations.range_start')) }}">
                                    <p class="text-muted small">@lang('admin/settings.advanced_settings.starting_port_description')</p>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.advanced_settings.ending_port')</label>
                                <div>
                                    <input type="number" class="form-control" name="pterodactyl:client_features:allocations:range_end" value="{{ old('pterodactyl:client_features:allocations:range_end', config('pterodactyl.client_features.allocations.range_end')) }}">
                                    <p class="text-muted small">@lang('admin/settings.advanced_settings.ending_port_description')</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-footer">
                        {{ csrf_field() }}
                        <button type="submit" name="_method" value="PATCH" class="btn btn-sm btn-primary pull-right">@lang('admin/settings.common.save')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
