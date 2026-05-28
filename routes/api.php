<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\AICoachController;
use App\Http\Controllers\Api\V1\AICoachSettingsController;
use App\Http\Controllers\Api\V1\CalendarController;
use App\Http\Controllers\Api\V1\ChallengeController;
use App\Http\Controllers\Api\V1\CheckInController;
use App\Http\Controllers\Api\V1\ClassTypeController;
use App\Http\Controllers\Api\V1\ColorController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EmailLogController;
use App\Http\Controllers\Api\V1\EmailTemplateController;
use App\Http\Controllers\Api\V1\ExerciseController;
use App\Http\Controllers\Api\V1\GymClassController;
use App\Http\Controllers\Api\V1\GymClassTrialController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\ProcessedStripeEventController;
use App\Http\Controllers\Api\V1\RecoveryController;
use App\Http\Controllers\Api\V1\RetentionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\StripeWebhookLogController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\SubscriptionOptionController;
use App\Http\Controllers\Api\V1\SuperAdminController;
use App\Http\Controllers\Api\V1\SupportController;
use App\Http\Controllers\Api\V1\SystemSettingController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserDashboardWidgetController;
use App\Http\Controllers\Api\V1\WorkoutLogController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::group([
        'middleware' => 'api',
        'prefix' => 'auth',
    ], function ($router) {
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
        Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
        Route::post('me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
    });

    Route::group([
        'prefix' => 'v1',
        'middleware' => 'auth:api',
    ], function () {
        Route::get('dashboard/hero', [DashboardApiController::class, 'hero'])->name('dashboard.hero');
        Route::get('dashboard/activity-feed', [DashboardApiController::class, 'activityFeed'])->name('dashboard.activity-feed');
        Route::post('dashboard/activity-feed/{id}/react', [DashboardApiController::class, 'react'])->name('dashboard.activity-feed.react');
        Route::get('tenants/{tenant}/occupancy', [TenantController::class, 'occupancy'])->name('tenants.occupancy');

        Route::get('user/recovery', [RecoveryController::class, 'show'])->name('user.recovery');
        Route::get('ai/suggestions', [AICoachController::class, 'suggestions'])->name('ai.suggestions');
        Route::get('challenges', [ChallengeController::class, 'index'])->name('challenges.index');

        Route::get('users/me/stats', [UserController::class, 'stats'])->name('users.stats');
        Route::get('users/me/attendance', [UserController::class, 'attendance'])->name('users.attendance');
        Route::get('users/me/achievements', [UserController::class, 'achievements'])->name('users.achievements');
        Route::get('membership/wallet-pass', [UserController::class, 'walletPass'])->name('membership.wallet-pass');
        Route::post('user/devices/sync', [UserController::class, 'syncDeviceData'])->name('user.devices.sync');

        Route::post('activity/{type}/{id}/bump', [DashboardApiController::class, 'bump'])->name('activity.bump');

        Route::apiResource('users', UserController::class);
        Route::apiResource('tenants', TenantController::class);
        Route::apiResource('roles', RoleController::class);
        Route::post('gym-classes/{gymClass}/book', [GymClassController::class, 'book'])->name('gym-classes.book');
        Route::delete('gym-classes/{gymClass}/book', [GymClassController::class, 'cancelBooking'])->name('gym-classes.cancel-booking');
        Route::post('gym-classes/{gymClass}/waitlist', [GymClassController::class, 'joinWaitlist'])->name('gym-classes.waitlist');
        Route::get('gym-classes/{gymClass}/wod', [GymClassController::class, 'wod'])->name('gym-classes.wod');
        Route::get('support/faqs', [SupportController::class, 'faqs'])->name('support.faqs');
        Route::post('support/tickets', [SupportController::class, 'storeTicket'])->name('support.tickets');
        Route::apiResource('gym-classes', GymClassController::class);
        Route::apiResource('class-types', ClassTypeController::class);
        Route::apiResource('exercises', ExerciseController::class);
        Route::apiResource('workout-logs', WorkoutLogController::class);
        Route::apiResource('check-ins', CheckInController::class);
        Route::apiResource('payments', PaymentController::class);
        Route::post('stripe/portal', [PaymentController::class, 'getPortalUrl'])->name('stripe.portal');
        Route::post('stripe/checkout', [PaymentController::class, 'getCheckoutUrl'])->name('stripe.checkout');
        Route::apiResource('plans', PlanController::class);
        Route::apiResource('subscriptions', SubscriptionController::class);
        Route::apiResource('email-templates', EmailTemplateController::class);
        Route::apiResource('system-settings', SystemSettingController::class);
        Route::apiResource('ai-coach-settings', AICoachSettingsController::class);
        Route::apiResource('calendars', CalendarController::class);
        Route::apiResource('colors', ColorController::class);
        Route::apiResource('dashboards', DashboardController::class);
        Route::apiResource('email-logs', EmailLogController::class);
        Route::apiResource('gym-class-trials', GymClassTrialController::class);
        Route::apiResource('permissions', PermissionController::class);
        Route::apiResource('processed-stripe-events', ProcessedStripeEventController::class);
        Route::apiResource('retentions', RetentionController::class);
        Route::apiResource('stripe-webhook-logs', StripeWebhookLogController::class);
        Route::apiResource('subscription-options', SubscriptionOptionController::class);
        Route::apiResource('super-admins', SuperAdminController::class);
        Route::apiResource('user-dashboard-widgets', UserDashboardWidgetController::class);
    });
});
