<?php

use App\Http\Controllers\Api\TwilioWebhookController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::prefix('products')->name('products.')->middleware('can:view products')->group(function () {
        Route::get('/', \App\Livewire\Products\ProductList::class)->name('index');
        Route::get('create', \App\Livewire\Products\ProductForm::class)->name('create');
        Route::get('{id}/edit', \App\Livewire\Products\ProductForm::class)->name('edit');
    });

    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', \App\Livewire\Campaigns\CampaignList::class)->name('index');
        Route::get('create', \App\Livewire\Campaigns\CampaignForm::class)->name('create');
        Route::get('{id}/edit', \App\Livewire\Campaigns\CampaignForm::class)->name('edit');
        Route::get('{id}/contacts/import', \App\Livewire\Campaigns\ContactImport::class)->name('contacts.import');
        Route::get('{id}', \App\Livewire\Campaigns\CampaignDetail::class)->name('show');
    });

    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', \App\Livewire\Contacts\ContactList::class)->name('index');
        Route::get('{id}/edit', \App\Livewire\Contacts\ContactForm::class)->name('edit');
    });

    Route::prefix('calls')->name('calls.')->group(function () {
        Route::get('/', \App\Livewire\Calls\CallHistory::class)->name('index');
        Route::get('{id}', \App\Livewire\Calls\CallDetail::class)->name('show');
    });

    Route::prefix('api/twilio')->group(function () {
        Route::get('token', [TwilioWebhookController::class, 'token'])->name('api.twilio.token');
    });
});
