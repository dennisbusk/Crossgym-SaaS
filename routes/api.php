<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\GymClassController;
use App\Http\Controllers\Api\V1\ClassTypeController;
use App\Http\Controllers\Api\V1\ExerciseController;
use App\Http\Controllers\Api\V1\WorkoutLogController;
use App\Http\Controllers\Api\V1\CheckInController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\EmailTemplateController;
use App\Http\Controllers\Api\V1\SystemSettingController;
use App\Http\Controllers\Api\V1\AICoachSettingsController;
use App\Http\Controllers\Api\V1\CalendarController;
use App\Http\Controllers\Api\V1\ColorController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EmailLogController;
use App\Http\Controllers\Api\V1\GymClassTrialController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\ProcessedStripeEventController;
use App\Http\Controllers\Api\V1\RetentionController;
use App\Http\Controllers\Api\V1\StripeWebhookLogController;
use App\Http\Controllers\Api\V1\SubscriptionOptionController;
use App\Http\Controllers\Api\V1\SuperAdminController;
use App\Http\Controllers\Api\V1\UserDashboardWidgetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('me', [AuthController::class, 'me'])->middleware('auth:api');
});

Route::group([
    'prefix' => 'v1',
    'middleware' => 'auth:api'
], function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('tenants', TenantController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('gym-classes', GymClassController::class);
    Route::apiResource('class-types', ClassTypeController::class);
    Route::apiResource('exercises', ExerciseController::class);
    Route::apiResource('workout-logs', WorkoutLogController::class);
    Route::apiResource('check-ins', CheckInController::class);
    Route::apiResource('payments', PaymentController::class);
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
