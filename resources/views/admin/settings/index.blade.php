@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'basic'])

@section('title')
    @lang('admin/settings.general.page_title')
@endsection

@section('content-header')
    <h1>@lang('admin/settings.general.page_title')<small>@lang('admin/settings.general.page_description')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/settings.common.admin')</a></li>
        <li class="active">@lang('admin/settings.general.page_title')</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/settings.general.box_title')</h3>
                </div>
                <form action="{{ route('admin.settings') }}" method="POST">
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.general.company_name')</label>
                                <div>
                                    <input type="text" class="form-control" name="app:name" value="{{ old('app:name', config('app.name')) }}" />
                                    <p class="text-muted"><small>@lang('admin/settings.general.company_name_description')</small></p>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.general.require_2fa')</label>
                                <div>
                                    <div class="btn-group" data-toggle="buttons">
                                        @php
                                            $level = old('pterodactyl:auth:2fa_required', config('pterodactyl.auth.2fa_required'));
                                        @endphp
                                        <label class="btn btn-primary @if ($level == 0) active @endif">
                                            <input type="radio" name="pterodactyl:auth:2fa_required" autocomplete="off" value="0" @if ($level == 0) checked @endif> @lang('admin/settings.general.not_required')
                                        </label>
                                        <label class="btn btn-primary @if ($level == 1) active @endif">
                                            <input type="radio" name="pterodactyl:auth:2fa_required" autocomplete="off" value="1" @if ($level == 1) checked @endif> @lang('admin/settings.general.admin_only')
                                        </label>
                                        <label class="btn btn-primary @if ($level == 2) active @endif">
                                            <input type="radio" name="pterodactyl:auth:2fa_required" autocomplete="off" value="2" @if ($level == 2) checked @endif> @lang('admin/settings.general.all_users')
                                        </label>
                                    </div>
                                    <p class="text-muted"><small>@lang('admin/settings.general.require_2fa_description')</small></p>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.general.default_language')</label>
                                <div>
                                    <select name="app:locale" class="form-control">
                                        @foreach($languages as $key => $value)
                                            <option value="{{ $key }}" @if(old('app:locale', config('app.panel_locale', config('app.locale'))) === $key) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-muted"><small>@lang('admin/settings.general.default_language_description')</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-xs-12">
                                <label class="control-label">@lang('admin/settings.general.footer_custom_text')</label>
                                <div>
                                    <textarea class="form-control" name="app:footer_custom_text" rows="4" maxlength="1000">{{ old('app:footer_custom_text', config('app.footer_custom_text')) }}</textarea>
                                    <p class="text-muted"><small>@lang('admin/settings.general.footer_custom_text_description')</small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        <button type="submit" name="_method" value="PATCH" class="btn btn-sm btn-primary pull-right">@lang('admin/settings.common.save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
