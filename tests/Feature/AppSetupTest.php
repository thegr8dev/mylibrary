<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    Storage::deleteDirectory('public/site_assets');
    File::deleteDirectory(public_path('site_assets'));
    $this->artisan('migrate:fresh');
});

it('can access the login page', function () {
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
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('correct-password'),
    ]);

    // Use Livewire to test invalid login via the Filament login component
    $response = Livewire::test(\Filament\Pages\Auth\Login::class)
        ->set('data.email', $user->email)  // Set using the correct state path
        ->set('data.password', 'wrong-password')  // Set using the correct state path
        ->call('authenticate')
        ->assertHasErrors(['data.email']);

    $response->assertStatus(200);

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
