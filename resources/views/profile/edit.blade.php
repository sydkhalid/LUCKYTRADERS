@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="erp-form-card h-100">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="erp-form-card h-100">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="col-12">
            <div class="erp-form-card border-danger-subtle">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection
