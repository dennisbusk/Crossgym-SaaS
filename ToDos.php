<?php
// ============================================================
// TODO (Junie): FEATURE - Local AI Workout Generator
// ============================================================
//
// GOAL:
// Implement a fully local AI that generates CrossFit and personal training programs.
// The AI should run on the server via a small and efficient model (phi3),
// generate high-quality workouts, and adapt to user directives and injuries.
//
// ============================================================
// 1️⃣ LOCAL AI SETUP
// ============================================================
//
// TODO (Junie): Install Ollama on the Ploi server
//   - curl -fsSL https://ollama.ai/install.sh | sh
//   - Verify installation: ollama --version
//   - Pull small performant model: ollama pull phi3
//   - Test: ollama run phi3 "Write a simple 3-day full body CrossFit plan."
//
// TODO (Junie): Ensure server user (e.g., ploi or www-data) can execute ollama
//   - sudo usermod -aG ollama ploi
//   - Restart PHP-FPM service
//
// ============================================================
// 2️⃣ LARAVEL AI SERVICE
// ============================================================
//
// TODO (Junie): Create WorkoutAIService at app/Services/WorkoutAIService.php
//   - Call Ollama locally via Symfony Process
//   - Accept directives: challenge_level, focus_areas, duration_minutes, injuries, days_per_week, program_type
//   - Build structured prompt (output JSON: day, warmup, workout, cooldown)
//   - Return AI response as string
//
// Example:
//   $ai->generate([
//       'challenge_level' => 'medium',
//       'focus_areas' => 'legs and core',
//       'duration_minutes' => 60,
//       'injuries' => 'shoulder',
//       'days_per_week' => 3,
//       'program_type' => 'crossfit',
//   ]);
//
// ============================================================
// 3️⃣ LARAVEL QUEUE & HORIZON SETUP
// ============================================================
//
// TODO (Junie): Install Laravel Horizon
//   - composer require laravel/horizon
//   - php artisan horizon:install
//   - php artisan migrate
//
// TODO (Junie): Configure Horizon supervisor
//   - Create /etc/supervisor/conf.d/horizon.conf
//   - Autostart, autorestart, log to storage/logs/horizon.log
//   - Reload supervisor and start Horizon
//
// TODO (Junie): Use Horizon to handle AI job queue
//   - Prevent web requests from blocking during AI generation
//   - Monitor job progress at /horizon
//
// ============================================================
// 4️⃣ AI GENERATION JOB
// ============================================================
//
// php artisan make:job GenerateWorkoutProgram
//
// TODO (Junie): Implement job to:
//   - Receive directives from user
//   - Call WorkoutAIService->generate()
//   - Store result in workout_programs table or broadcast to Livewire
//
// Example table columns:
//   - user_id
//   - type (crossfit/personal)
//   - directives (json)
//   - result (longtext)
//
// ============================================================
// 5️⃣ CONTROLLER & ROUTES
// ============================================================
//
// TODO (Junie): Create AIWorkoutController
//   - POST /ai/workouts
//   - Validate inputs: challenge_level, focus_areas, duration_minutes, injuries, days_per_week, program_type
//   - Dispatch GenerateWorkoutProgram job
//
// ============================================================
// 6️⃣ LIVEWIRE FRONTEND COMPONENT
// ============================================================
//
// php artisan make:livewire AiWorkoutGenerator
//
// TODO (Junie): Component fields:
//   - challenge_level, focus_areas, duration_minutes, injuries, days_per_week, program_type
//   - result (output)
//
// TODO (Junie): Form:
//   - Wire:model to fields
//   - Submit triggers GenerateWorkoutProgram dispatch
//   - Show loading spinner while job runs
//   - Display AI-generated program in structured format (JSON / preformatted)
//
// ============================================================
// 7️⃣ USER ADAPTATION & PROFILE
// ============================================================
//
// TODO (Junie): Create user_training_profiles table
//   - Columns: user_id, age, goals, injuries, training_frequency, preferences (json)
//
// TODO (Junie): Automatically include user profile data in AI directives
//   - Ensures workouts adapt to injuries, goals, and frequency
//
// ============================================================
// 8️⃣ EXPORT & IMPROVEMENT
// ============================================================
//
// TODO (Junie): Add export options for generated workouts
//   - PDF via laravel-dompdf
//   - Copy JSON
//
// TODO (Junie): Add "Improve this program" feature
//   - Resubmit previous prompt with minor adjustments
//
// ============================================================
// 9️⃣ TESTS (Pest)
// ============================================================
//
// php artisan make:test AiWorkoutGenerationTest
//
// TODO (Junie): Add feature tests:
//   - user_can_dispatch_ai_job
//   - ai_job_generates_valid_json
//   - workouts_respect_injury_constraints
//   - queued_jobs_are_processed_by_horizon
//
// ============================================================
// 10️⃣ CODE STYLE & CONVENTIONS
// ============================================================
//
// TODO (Junie): Keep services under App\Services
// TODO (Junie): Jobs under App\Jobs
// TODO (Junie): Livewire components under App\Livewire
// TODO (Junie): Follow PSR-12 + Laravel 12 structure
// TODO (Junie): Keep UI minimal, clear, and consistent
//
// ============================================================
// END OF TASKS
// ============================================================
