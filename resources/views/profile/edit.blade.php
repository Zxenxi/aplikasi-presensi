@extends('layouts.admin')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">
                Profile Settings
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Kelola informasi profil dan keamanan akun Anda
            </p>
        </div>
    </div>

    {{-- Profile Information Card --}}
    <div class="bg-white p-5 sm:p-6 rounded-xl shadow-md border border-gray-200">
        <div class="max-w-2xl">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    {{-- Update Password Card --}}
    <div class="bg-white p-5 sm:p-6 rounded-xl shadow-md border border-gray-200">
        <div class="max-w-2xl">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    {{-- Delete Account Card --}}
    <div class="bg-white p-5 sm:p-6 rounded-xl shadow-md border border-gray-200">
        <div class="max-w-2xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
