<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Storage::deleteDirectory('public/site_assets');
    File::deleteDirectory(public_path('site_assets'));
    // $this->artisan('migrate:fresh');
});

afterAll(function () {
    Storage::deleteDirectory('public/site_assets');
    File::deleteDirectory(public_path('site_assets'));
    expect(Storage::exists('public/site_assets/defaultLightModeLogo.png'))->toBeFalse();
    expect(Storage::exists('public/site_assets/defaultDarkModeLogo.png'))->toBeFalse();
    expect(Storage::exists('public/site_assets/favicon.png'))->toBeFalse();
});

it('can access the login page', function () {
    Auth::logout();
    $this->assertGuest();
    $this->get('/admin/login')
        ->assertStatus(200)
        ->assertSee('Login');
});

it('allows user to login with valid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->actingAs($user)->get('/admin/login');

    $response->assertRedirect('/admin/');
    $this->assertAuthenticatedAs($user);
});

it('prevents user login with invalid credentials', function () {
    // Create a user with a known password
    Auth::logout();

    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('correct-password'),
    ]);

    livewire(\Filament\Pages\Auth\Login::class)
        ->set('data.email', $user->email)  // Ensure these fields are correctly named
        ->set('data.password', 'wrong-password')  // Ensure these fields are correctly named
        ->call('authenticate')
        ->assertHasErrors(['data.email']); // Ensure this is the correct key for errors

    $this->assertGuest(); // ensure no users are authenticated
});

it('runs the setup command successfully', function () {

    // Execute the (mocked) command
    Artisan::call('app:install');

    // Ensure no actual assets are copied
    expect(Storage::exists('public/site_assets/defaultLightModeLogo.png'))->toBeTrue();
    expect(Storage::exists('public/site_assets/defaultDarkModeLogo.png'))->toBeTrue();
    expect(Storage::exists('public/site_assets/favicon.png'))->toBeTrue();

    // Check if the user exists
    assertDatabaseHas('users', [
        'email' => 'admin@admin.com',
    ]);

    // Get the admin user
    $adminUser = User::where('email', 'admin@admin.com')->firstOrFail();

    $adminUser->refresh();

    // Check if the notification was sent to the admin user
    $notifications = $adminUser->notifications;

    // Assert that a notification was sent to the admin user
    expect($notifications->count())->toBe(1);

    // Assert the notification's contents
    $notification = $notifications->first();
    expect($notification->data['title'])->toBe('Setup completed !');
    expect($notification->data['body'])->toBe('Congrats ! app installed successfully !');
});
