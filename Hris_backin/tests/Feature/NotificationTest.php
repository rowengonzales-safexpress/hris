<?php

use App\Models\Notification;
use App\Models\Core\User;
use App\Models\Core\CoreApp;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('notification can be created with app_id and user_to', function () {
    $user = User::factory()->create();
    $app = CoreApp::factory()->create();
    $siteheadUser = User::factory()->create();

    $notification = Notification::create([
        'user_id' => $user->id,
        'app_id' => $app->id,
        'user_to' => $siteheadUser->id,
        'title' => 'Test Notification',
        'message' => 'This is a test notification',
        'type' => 'info',
        'is_read' => false,
    ]);

    expect($notification)->toBeInstanceOf(Notification::class);
    expect($notification->user_id)->toBe($user->id);
    expect($notification->app_id)->toBe($app->id);
    expect($notification->user_to)->toBe($siteheadUser->id);
    expect($notification->title)->toBe('Test Notification');
    expect($notification->message)->toBe('This is a test notification');
    expect($notification->type)->toBe('info');
    expect($notification->is_read)->toBeFalse();
});

test('notification service creates notification with sitehead_user_id logic', function () {
    $siteheadUser = User::factory()->create();
    $user = User::factory()->create(['sitehead_user_id' => $siteheadUser->id]);
    $app = CoreApp::factory()->create();

    $notification = NotificationService::create(
        userId: $user->id,
        title: 'Test Notification',
        message: 'This is a test notification',
        type: 'info',
        appId: $app->id
    );

    expect($notification->user_id)->toBe($user->id);
    expect($notification->app_id)->toBe($app->id);
    expect($notification->user_to)->toBe($siteheadUser->id);
});

test('notification service respects explicit user_to parameter', function () {
    $siteheadUser = User::factory()->create();
    $user = User::factory()->create(['sitehead_user_id' => $siteheadUser->id]);
    $explicitUser = User::factory()->create();
    $app = CoreApp::factory()->create();

    $notification = NotificationService::create(
        userId: $user->id,
        title: 'Test Notification',
        message: 'This is a test notification',
        type: 'info',
        appId: $app->id,
        userTo: $explicitUser->id
    );

    expect($notification->user_id)->toBe($user->id);
    expect($notification->app_id)->toBe($app->id);
    expect($notification->user_to)->toBe($explicitUser->id);
});

test('notification controller filters by app_id and user_to', function () {
    $user = User::factory()->create();
    $siteheadUser = User::factory()->create();
    $user->update(['sitehead_user_id' => $siteheadUser->id]);

    $app1 = CoreApp::factory()->create();
    $app2 = CoreApp::factory()->create();

    // Create notifications for different scenarios
    $notification1 = Notification::factory()->create([
        'user_id' => $user->id,
        'app_id' => $app1->id,
        'title' => 'User Notification App1'
    ]);

    $notification2 = Notification::factory()->create([
        'user_id' => $user->id,
        'app_id' => $app2->id,
        'title' => 'User Notification App2'
    ]);

    $notification3 = Notification::factory()->create([
        'user_to' => $siteheadUser->id,
        'app_id' => $app1->id,
        'title' => 'Sitehead Notification App1'
    ]);

    $this->actingAs($user);

    // Test filtering by app_id
    $response = $this->get('/notifications?app_id=' . $app1->id);
    $response->assertStatus(200);

    // Test that user can access notifications for their sitehead user
    $response = $this->get('/notifications');
    $response->assertStatus(200);
});

test('notification controller denies access to unauthorized notifications', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $notification = Notification::factory()->create([
        'user_id' => $user2->id,
        'title' => 'Private Notification'
    ]);

    $this->actingAs($user1);

    $response = $this->get("/notifications/{$notification->id}");
    $response->assertStatus(403);
});

test('notification relationships work correctly', function () {
    $user = User::factory()->create();
    $siteheadUser = User::factory()->create();
    $app = CoreApp::factory()->create();

    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'app_id' => $app->id,
        'user_to' => $siteheadUser->id,
    ]);

    expect($notification->user)->toBeInstanceOf(User::class);
    expect($notification->user->id)->toBe($user->id);

    expect($notification->app)->toBeInstanceOf(CoreApp::class);
    expect($notification->app->id)->toBe($app->id);

    expect($notification->userTo)->toBeInstanceOf(User::class);
    expect($notification->userTo->id)->toBe($siteheadUser->id);
});
