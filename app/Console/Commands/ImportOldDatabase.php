<?php

namespace App\Console\Commands;

use App\Jobs\FetchStripePaymentIntent;
use App\Jobs\SyncStripeSubscriptionStatus;
use App\Models\CheckIn;
use App\Models\EmailLog;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportOldDatabase extends Command
{
    protected $signature = 'import:old-db {tenant : Tenant ID or domain} {file=db_dump.sql} {--force : Skip confirmation} {--update : Update existing data instead of clearing}';

    protected $description = 'Import data from old database dump';

    protected $tenant;

    protected $oldUsers = [];

    protected $oldMembers = [];

    protected $userMapping = []; // old_user_id => new_user_id

    protected $memberMapping = []; // old_member_id => new_user_id

    protected $roleMapping = [];

    protected $oldEventTypes = [];

    protected $oldEvents = [];

    protected $classTypeMapping = []; // old_event_type_id => new_class_type_id

    protected $classMapping = []; // old_event_id => new_class_id

    protected $colorMapping = []; // hex_code => color_id

    public function handle()
    {
        $tenantIdentifier = $this->argument('tenant');
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File not found: $file");

            return;
        }

        // Find tenant
        $this->tenant = Tenant::where('id', $tenantIdentifier)
            ->orWhere('domain', $tenantIdentifier)
            ->orWhere('name', 'like', "%$tenantIdentifier%")
            ->first();

        if (! $this->tenant) {
            $this->error("Tenant not found with ID or domain: $tenantIdentifier");

            return;
        }

        if (! $this->option('update') && ! $this->option('force')) {
            if (! $this->confirm("WARNING: This will DELETE ALL USERS, PAYMENTS, EMAILS, and CHECK-INS for tenant '{$this->tenant->name}' and replace them with data from '{$file}'. Continue?")) {
                $this->info('Aborted.');

                return;
            }
        }

        // Sæt tidszone til UTC for at undgå problemer med sommertid/ugyldige datoer
        if (DB::getDriverName() === 'mysql') {
            DB::statement("SET time_zone = '+00:00'");
        }

        User::unsetEventDispatcher();

        if ($this->option('update')) {
            $this->info('Update mode enabled. Existing data will NOT be cleared.');
        } else {
            $this->clearExistingData();
        }

        $this->setupRoles();

        $this->info('Loading file content...');
        $content = file_get_contents($file);

        $this->info('Parsing users and members...');
        $this->extractAllData($content, 'users', 'users');
        $this->extractAllData($content, 'members', 'members');
        $this->extractAllData($content, 'event_type', 'event_types');
        $this->extractAllData($content, 'events', 'events');

        $this->info('Found '.count($this->oldUsers).' users and '.count($this->oldMembers).' members.');

        $this->processUsersAndMembers();
        $this->processEventTypes();
        $this->processEvents();

        $this->info('Parsing payments, emails, bookings and subscriptions...');
        $this->extractAllData($content, 'payments', 'payments');
        $this->extractAllData($content, 'emails', 'emails');
        $this->extractAllData($content, 'bookings_deltagere', 'bookings');
        $this->extractAllData($content, 'subscriptions', 'subscriptions');

        $this->info('Import completed!');

        $this->call('app:deduplicate-colors');
    }

    protected function clearExistingData()
    {
        $this->info("Clearing existing data for tenant '{$this->tenant->name}'...");

        // Sletning sker i rækkefølge pga foreign keys
        // CheckIn, EmailLog, Payment afhænger af User.
        // Hvis der er cascade on delete, vil de blive slettet når User slettes.
        // Men vi gør det eksplicit for en sikkerheds skyld (hvis nogle ikke har cascade).

        DB::table('check_ins')->where('tenant_id', $this->tenant->id)->delete();
        DB::table('gym_class_user')->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('users')
                ->whereColumn('users.id', 'gym_class_user.user_id')
                ->where('users.tenant_id', $this->tenant->id);
        })->delete();
        DB::table('gym_class_trials')->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('classes')
                ->whereColumn('classes.id', 'gym_class_trials.gym_class_id')
                ->where('classes.tenant_id', $this->tenant->id);
        })->delete();
        DB::table('classes')->where('tenant_id', $this->tenant->id)->delete();
        DB::table('colors')->where('tenant_id', $this->tenant->id)->delete();
        DB::table('class_types')->where('tenant_id', $this->tenant->id)->delete();
        DB::table('email_logs')->where('tenant_id', $this->tenant->id)->delete();
        DB::table('payments')->where('tenant_id', $this->tenant->id)->delete();
        DB::table('subscriptions')->where('tenant_id', $this->tenant->id)->delete();
        DB::table('users')->where('tenant_id', $this->tenant->id)->delete();

        // Roller beholder vi normalt, men vi sikrer os de findes i setupRoles.
        // Vi behøver ikke slette roller medmindre de også skal erstattes helt.
    }

    protected function setupRoles()
    {
        $this->roleMapping['admin'] = Role::firstOrCreate(['slug' => 'admin', 'tenant_id' => $this->tenant->id], ['name' => ['da' => 'Administrator', 'en' => 'Administrator']])->id;
        $this->roleMapping['trainer'] = Role::firstOrCreate(['slug' => 'trainer', 'tenant_id' => $this->tenant->id], ['name' => ['da' => 'Træner', 'en' => 'Trainer']])->id;
        $this->roleMapping['creator'] = Role::firstOrCreate(['slug' => 'creator', 'tenant_id' => $this->tenant->id], ['name' => ['da' => 'Holdopretter', 'en' => 'Creator']])->id;
        $this->roleMapping['member'] = Role::firstOrCreate(['slug' => 'member', 'tenant_id' => $this->tenant->id], ['name' => ['da' => 'Medlem', 'en' => 'Member']])->id;
    }

    protected function extractAllData($content, $tableName, $type)
    {
        // Find alle INSERT INTO `tableName` blocks
        // Vi finder kolonne rækkefølgen hvis den findes
        $pattern = "/INSERT INTO `$tableName` \((.*)\) VALUES\s*(.*);/isU";
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[2] as $index => $valuesPart) {
                $columns = str_getcsv($matches[1][$index], ',', '`');
                $columns = array_map(fn ($c) => trim($c), $columns);
                $this->parseValuesPart($valuesPart, $type, $columns);
            }
        } else {
            // Prøv uden kolonner
            $pattern = "/INSERT INTO `$tableName` VALUES\s*(.*);/isU";
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $valuesPart) {
                    $this->parseValuesPart($valuesPart, $type);
                }
            }
        }
    }

    protected function parseValuesPart($valuesPart, $type, $columns = [])
    {
        // En mere robust måde at splitte rækker: kig efter ),( eller rækker der slutter med )
        // Men pas på strings der indeholder ),(

        // Vi splitter ved ),\s*( for at få de enkelte rækker
        $valuesPart = trim($valuesPart);
        // Fjern de yderste paranteser hvis nødvendigt, men normalt er de der for hver række

        // Da SQL formatet er (v1, v2), (v3, v4)
        // Vi bruger en regex til at finde alt mellem (...)
        preg_match_all("/\((.*)\)(?:,|$)/sU", $valuesPart, $matches);

        foreach ($matches[1] as $row) {
            $data = str_getcsv($row, ',', "'");
            $data = array_map(function ($v) {
                if ($v === null) {
                    return null;
                }
                $v = trim($v, " '\"");
                if ($v === 'NULL' || $v === '0000-00-00' || $v === '0000-00-00 00:00:00') {
                    return null;
                }

                return $v;
            }, $data);

            if ($columns) {
                $namedData = [];
                foreach ($columns as $idx => $col) {
                    $namedData[$col] = $data[$idx] ?? null;
                }
                $data = $namedData;
            }

            if ($type === 'users') {
                $id = $data['id'] ?? $data[0] ?? null;
                if (! $id) {
                    continue;
                }
                $this->oldUsers[$id] = [
                    'id' => $id,
                    'name' => $data['name'] ?? $data[1] ?? '',
                    'email' => $data['email'] ?? $data[3] ?? '',
                    'password' => $data['password'] ?? $data[4] ?? '',
                    'is_admin' => $data['admin'] ?? $data[5] ?? 0,
                    'is_instructor' => $data['instructor'] ?? $data[7] ?? 0,
                    'maa_oprette_hold' => $data['maa_oprette_hold'] ?? $data[17] ?? 0,
                    'card_brand' => $data['card_brand'] ?? null,
                    'card_last_four' => $data['card_last_four'] ?? null,
                ];
            } elseif ($type === 'members') {
                $id = $data['id'] ?? $data[0] ?? null;
                if (! $id) {
                    continue;
                }
                $this->oldMembers[$id] = [
                    'id' => $id,
                    'medlemsnummer' => $data['medlemsnummer'] ?? $data[1] ?? null,
                    'user_id' => $data['user_id'] ?? $data[2] ?? null,
                    'name' => $data['name'] ?? $data[4] ?? '',
                    'address' => $data['adress'] ?? $data[5] ?? '',
                    'postal' => $data['postal'] ?? $data[6] ?? '',
                    'city' => $data['city'] ?? $data[7] ?? '',
                    'birthday' => $data['birthday'] ?? $data[8] ?? null,
                    'sex' => $data['sex'] ?? $data[9] ?? null,
                    'joined_at' => $data['indmeldt'] ?? $data[10] ?? null,
                    'left_at' => $data['udmeldt'] ?? $data[11] ?? null,
                    'phone' => $data['phone'] ?? $data[14] ?? null,
                    'mobile' => $data['mobile'] ?? $data[15] ?? null,
                    'email' => $data['email'] ?? $data[16] ?? '',
                    'image' => $data['image'] ?? $data[23] ?? null,
                    'terms_accepted' => $data['accept_af_vedtaegter'] ?? $data[24] ?? 0,
                    'approved_closed' => $data['godkendt_til_lukkede_hold'] ?? $data[25] ?? 0,
                ];
            } elseif ($type === 'event_types') {
                $id = $data['id'] ?? $data[0] ?? null;
                if (! $id) {
                    continue;
                }
                $this->oldEventTypes[$id] = [
                    'id' => $id,
                    'name' => $data['display_name'] ?? $data[2] ?? '',
                    'slug' => $data['name'] ?? $data[1] ?? '',
                    'price' => $data['price'] ?? $data[3] ?? 0,
                ];
            } elseif ($type === 'events') {
                $id = $data['id'] ?? $data[0] ?? null;
                if (! $id) {
                    continue;
                }
                $this->oldEvents[$id] = [
                    'id' => $id,
                    'user_id' => $data['user_id'] ?? $data[1] ?? null,
                    'name' => $data['name'] ?? $data[2] ?? '',
                    'startdate' => $data['startdate'] ?? $data[3] ?? null,
                    'enddate' => $data['enddate'] ?? $data[4] ?? null,
                    'color' => $data['color'] ?? $data[5] ?? null,
                    'description' => $data['description'] ?? $data[6] ?? null,
                    'size' => $data['size'] ?? $data[8] ?? $data[10] ?? 0,
                    'Allday' => $data['allDay'] ?? $data['Allday'] ?? $data[10] ?? $data[11] ?? 0,
                    'group' => $data['group'] ?? $data[11] ?? $data[12] ?? null,
                    'featured' => $data['featured'] ?? $data[12] ?? $data[13] ?? 0,
                    'event_type_id' => $data['event_type_id'] ?? $data[9] ?? $data[14] ?? null,
                ];
            } elseif ($type === 'payments') {
                $this->importPayment($data);
            } elseif ($type === 'emails') {
                $this->importEmail($data);
            } elseif ($type === 'bookings') {
                $this->importBookingAsCheckIn($data);
            } elseif ($type === 'subscriptions') {
                $this->importSubscription($data);
            }
        }
    }

    protected function importSubscription($data)
    {
        $oldUserId = $data['user_id'] ?? $data[1] ?? null;
        $newUserId = $this->userMapping[$oldUserId] ?? null;
        if (! $newUserId) {
            return;
        }

        $stripeId = $data['stripe_id'] ?? $data[3] ?? null;
        $stripePlan = $data['stripe_plan'] ?? $data[4] ?? null;
        $createdAt = $data['created_at'] ?? $data[8] ?? now();
        $updatedAt = $data['updated_at'] ?? $data[9] ?? now();
        $endsAt = $data['ends_at'] ?? $data[7] ?? null;

        // Bestem status baseret på om den er udløbet
        $status = 'active';
        if ($endsAt && strtotime($endsAt) < time()) {
            $status = 'expired';
        }

        $subData = [
            'tenant_id' => $this->tenant->id,
            'user_id' => $newUserId,
            'stripe_subscription_id' => $stripeId,
            'stripe_price_id' => $stripePlan,
            'status' => $status,
            'current_period_end' => $endsAt,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];

        $existing = DB::table('subscriptions')
            ->where('tenant_id', $this->tenant->id)
            ->where('stripe_subscription_id', $stripeId)
            ->first();

        if ($existing) {
            DB::table('subscriptions')->where('id', $existing->id)->update($subData);
            $newSubId = $existing->id;
        } else {
            $newSubId = DB::table('subscriptions')->insertGetId($subData);
        }

        if ($stripeId) {
            SyncStripeSubscriptionStatus::dispatch((int) $newSubId, $stripeId);
        }
    }

    protected function processUsersAndMembers()
    {
        $this->info('Processing users and merging with members...');

        $membersByUserId = [];
        foreach ($this->oldMembers as $member) {
            $membersByUserId[$member['user_id']] = $member;
        }

        $usedEmails = [];
        foreach ($this->oldUsers as $oldUserId => $oldUser) {
            $member = $membersByUserId[$oldUserId] ?? null;

            $roleId = $this->roleMapping['member'];
            if (($oldUser['is_admin'] ?? 0) == 1) {
                $roleId = $this->roleMapping['admin'];
            } elseif (($oldUser['is_instructor'] ?? 0) == 1) {
                $roleId = $this->roleMapping['trainer'];
            } elseif (($oldUser['maa_oprette_hold'] ?? 0) == 1) {
                $roleId = $this->roleMapping['creator'];
            }

            $email = $oldUser['email'];
            if (in_array($email, $usedEmails) || DB::table('users')->where('email', $email)->where('id', '!=', $oldUserId)->exists()) {
                $email = "id{$oldUserId}_".$email;
            }
            $usedEmails[] = $email;

            $userData = [
                'name' => $oldUser['name'],
                'email' => $email,
                'password' => $oldUser['password'],
                'tenant_id' => $this->tenant->id,
                'role_id' => $roleId,
                'medlemsnummer' => $member['medlemsnummer'] ?? null,
                'address' => $member['address'] ?? null,
                'postal_code' => $member['postal'] ?? null,
                'city' => $member['city'] ?? null,
                'birthday' => $member['birthday'] ?? null,
                'sex' => $member['sex'] ?? null,
                'phone' => $member['phone'] ?? null,
                'mobile' => $member['mobile'] ?? null,
                'joined_at' => $member['joined_at'] ?? null,
                'left_at' => $member['left_at'] ?? null,
                'is_approved_for_closed_classes' => ($member['approved_closed'] ?? 0) == 1,
                'image' => $member['image'] ?? null,
                'terms_accepted_at' => ($member['terms_accepted'] ?? 0) == 1 ? now() : null,
                'old_user_id' => $oldUserId,
                'old_member_id' => $member['id'] ?? null,
                'stripe_customer_id' => $oldUser['stripe_id'] ?? null,
                'card_brand' => $oldUser['card_brand'] ?? null,
                'card_last_four' => $oldUser['card_last_four'] ?? null,
                'updated_at' => now(),
            ];

            if ($this->option('update')) {
                DB::table('users')->updateOrInsert(['id' => $oldUserId], $userData);
            } else {
                $userData['id'] = $oldUserId;
                $userData['created_at'] = now();
                DB::table('users')->insert($userData);
            }
            $newUserId = $oldUserId;

            $this->userMapping[$oldUserId] = $newUserId;
            if ($member) {
                $this->memberMapping[$member['id']] = $newUserId;
            }
        }
    }

    protected function processEventTypes()
    {
        $this->info('Processing event types...');
        foreach ($this->oldEventTypes as $oldId => $oldType) {
            $data = [
                'tenant_id' => $this->tenant->id,
                'slug' => $oldType['slug'],
                'name' => json_encode(['da' => $oldType['name']]),
                'price' => $oldType['price'],
                'updated_at' => now(),
            ];

            if ($this->option('update')) {
                DB::table('class_types')->updateOrInsert(['id' => $oldId], $data);
            } else {
                $data['id'] = $oldId;
                $data['created_at'] = now();
                DB::table('class_types')->insert($data);
            }
            $this->classTypeMapping[$oldId] = $oldId;
        }
    }

    protected function processEvents()
    {
        $this->info('Processing events...');
        foreach ($this->oldEvents as $oldId => $oldEvent) {
            if (! $oldEvent['startdate']) {
                continue;
            }

            $trainerId = $this->userMapping[$oldEvent['user_id']] ?? null;
            $classTypeId = $this->classTypeMapping[$oldEvent['event_type_id']] ?? null;

            $colorId = null;
            $hex = $oldEvent['color'];
            if ($hex) {
                if (isset($this->colorMapping[$hex])) {
                    $colorId = $this->colorMapping[$hex];
                } else {
                    $existingColor = DB::table('colors')->where('tenant_id', $this->tenant->id)->where('color', $hex)->first();
                    if ($existingColor) {
                        $colorId = $existingColor->id;
                    } else {
                        $colorName = 'WOD'; // Standard
                        if ($hex === '#488aff') {
                            $colorName = 'WOD';
                        } elseif ($hex === '#0098ff') {
                            $colorName = 'Mobility';
                        } elseif ($hex === '#285ff4') {
                            $colorName = 'Kettlebell Club';
                        } elseif ($hex === '#ff8080') {
                            $colorName = 'Hybrid';
                        } else {
                            $colorName = $oldEvent['name'];
                        } // Brug event navn for ukendte hex koder

                        $colorId = DB::table('colors')->insertGetId([
                            'tenant_id' => $this->tenant->id,
                            'color' => $hex,
                            'name' => $colorName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    $this->colorMapping[$hex] = $colorId;
                }
            }

            $data = [
                'tenant_id' => $this->tenant->id,
                'name' => json_encode(['da' => $oldEvent['name']]),
                'description' => json_encode(['da' => $oldEvent['description']]),
                'trainer_id' => $trainerId,
                'class_type_id' => $classTypeId,
                'max_participants' => $oldEvent['size'],
                'class_start' => $oldEvent['startdate'],
                'class_end' => $oldEvent['enddate'],
                'recurring_id' => $oldEvent['group'],
                'all_day_event' => ($oldEvent['Allday'] ?? 0) == 1,
                'featured' => ($oldEvent['featured'] ?? 0) == 1,
                'color_id' => $colorId,
                'updated_at' => now(),
            ];

            if ($this->option('update')) {
                DB::table('classes')->updateOrInsert(['id' => $oldId], $data);
            } else {
                $data['id'] = $oldId;
                $data['created_at'] = now();
                DB::table('classes')->insert($data);
            }
            $this->classMapping[$oldId] = $oldId;
        }
    }

    protected function importPayment($data)
    {
        $oldMemberId = $data['member_id'] ?? $data[1] ?? null;
        $newUserId = $this->memberMapping[$oldMemberId] ?? null;

        if (! $newUserId && $oldMemberId && isset($this->oldMembers[$oldMemberId])) {
            $oldUserId = $this->oldMembers[$oldMemberId]['user_id'];
            $newUserId = $this->userMapping[$oldUserId] ?? $oldUserId;
        }

        if (! $newUserId) {
            return;
        }

        $amount = $data['amount'] ?? $data[4] ?? 0;
        $type = $data['type'] ?? $data[5] ?? '';
        $month = $data['month'] ?? $data[2] ?? '';
        $year = $data['year'] ?? $data[3] ?? '';
        $refunded = $data['refunded'] ?? $data[9] ?? 0;
        $createdAt = $data['created_at'] ?? $data[6] ?? now();
        $updatedAt = $data['updated_at'] ?? $data[11] ?? now();
        $id = $data['id'] ?? $data[0] ?? null;
        $chargeId = $data['charge_id'] ?? $data[8] ?? null;

        $paymentData = [
            'tenant_id' => $this->tenant->id,
            'user_id' => $newUserId,
            'amount' => (int) ($amount * 100),
            'currency' => 'DKK',
            'status' => 'completed',
            'type' => $type === 'Stripe' ? 'stripe' : 'manual',
            'notes' => "Importeret fra gammel DB. Måned: {$month}/{$year}",
            'refunded_amount' => $refunded == 1 ? (int) ($amount * 100) : 0,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];

        $newPaymentId = null;

        if ($id && is_numeric($id)) {
            DB::table('payments')->updateOrInsert(['id' => $id], $paymentData);
            $newPaymentId = (int) $id;
        } else {
            $newPaymentId = DB::table('payments')->insertGetId($paymentData);
        }

        if ($type === 'Stripe' && $chargeId) {
            FetchStripePaymentIntent::dispatch($newPaymentId, $chargeId);
        }
    }

    protected function importEmail($data)
    {
        $oldMemberId = $data['member_id'] ?? $data[6] ?? null;
        $newUserId = $this->memberMapping[$oldMemberId] ?? null;

        if (! $newUserId && $oldMemberId && isset($this->oldMembers[$oldMemberId])) {
            $oldUserId = $this->oldMembers[$oldMemberId]['user_id'];
            $newUserId = $this->userMapping[$oldUserId] ?? $oldUserId;
        }

        if (! $newUserId) {
            return;
        }

        $subject = $data['subject'] ?? $data['type'] ?? $data[2] ?? 'Importeret besked';
        $sentAt = $data['sent_at'] ?? $data[5] ?? $data['created_at'] ?? $data[7] ?? now();
        $delivered = $data['delivered'] ?? $data[3] ?? 0;
        $opened = $data['opened'] ?? $data[4] ?? 0;
        $createdAt = $data['created_at'] ?? $data[7] ?? now();
        $updatedAt = $data['updated_at'] ?? $data[8] ?? now();
        $id = $data['id'] ?? $data[0] ?? null;

        $emailData = [
            'tenant_id' => $this->tenant->id,
            'user_id' => $newUserId,
            'subject' => $subject,
            'to' => 'unknown@example.com',
            'tracking_id' => Str::random(32),
            'sent_at' => $sentAt,
            'delivered_at' => $delivered == 1 ? $sentAt : null,
            'opened_at' => $opened == 1 ? $sentAt : null,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];

        if ($id && is_numeric($id)) {
            $existing = DB::table('email_logs')->where('id', $id)->first();
            if ($existing) {
                unset($emailData['tracking_id']);
                DB::table('email_logs')->where('id', $id)->update($emailData);
            } else {
                $emailData['id'] = $id;
                DB::table('email_logs')->insert($emailData);
            }
        } else {
            DB::table('email_logs')->insert($emailData);
        }
    }

    protected function importBookingAsCheckIn($data)
    {
        // $data can be indexed array or named array
        $oldMemberId = $data['member_id'] ?? $data[0] ?? null;
        $oldEventId = $data['event_id'] ?? $data[1] ?? null;
        $createdAt = $data['created_at'] ?? $data[2] ?? now();
        $updatedAt = $data['updated_at'] ?? $data[3] ?? now();
        $checkedIn = $data['checked_in'] ?? $data[4] ?? 0;
        $checkedInAt = $data['checked_in_at'] ?? $data[5] ?? null;
        $chargeId = $data['charge_id'] ?? $data[6] ?? null;
        $paid = $data['paid'] ?? $data[7] ?? 0;

        $newUserId = $this->memberMapping[$oldMemberId] ?? null;

        if (! $newUserId && $oldMemberId && isset($this->oldMembers[$oldMemberId])) {
            $oldUserId = $this->oldMembers[$oldMemberId]['user_id'];
            $newUserId = $this->userMapping[$oldUserId] ?? $oldUserId;
        }

        if (! $newUserId) {
            return;
        }

        $newClassId = $this->classMapping[$oldEventId] ?? null;
        if (! $newClassId) {
            return;
        }

        // Find den gamle user id for at se om det er en trial
        $oldUserIdForMember = $this->oldMembers[$oldMemberId]['user_id'] ?? null;

        $checkInId = null;
        // Opret CheckIn hvis brugeren er tjekket ind ELLER hvis der er betalt (charge_id eksisterer eller paid flag er sat)
        if ($checkedIn == 1 || $chargeId !== null || $paid == 1) {
            $checkInData = [
                'tenant_id' => $this->tenant->id,
                'user_id' => $newUserId,
                'gym_class_id' => $newClassId,
                'is_paid' => $paid == 1 || $chargeId !== null,
                'charge_id' => $chargeId,
                'checked_at' => $checkedInAt ?? ($checkedIn == 1 ? $createdAt : null),
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];

            if ($this->option('update')) {
                $existingCheckIn = DB::table('check_ins')
                    ->where('tenant_id', $this->tenant->id)
                    ->where('user_id', $newUserId)
                    ->where('gym_class_id', $newClassId)
                    ->where('created_at', $createdAt)
                    ->first();

                if ($existingCheckIn) {
                    $checkInId = $existingCheckIn->id;
                    DB::table('check_ins')->where('id', $checkInId)->update($checkInData);
                } else {
                    $checkInId = DB::table('check_ins')->insertGetId($checkInData);
                }
            } else {
                $checkInId = DB::table('check_ins')->insertGetId($checkInData);
            }
        }

        if ($oldUserIdForMember == 85) {
            // GymClassTrial
            $trialData = [
                'tenant_id' => $this->tenant->id,
                'gym_class_id' => $newClassId,
                'name' => "Trial User (Old ID: $oldMemberId)",
                'check_in_id' => $checkInId,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];

            if ($this->option('update')) {
                DB::table('gym_class_trials')->updateOrInsert([
                    'tenant_id' => $this->tenant->id,
                    'gym_class_id' => $newClassId,
                    'check_in_id' => $checkInId,
                ], $trialData);
            } else {
                DB::table('gym_class_trials')->insert($trialData);
            }

            return;
        }

        $bookingData = [
            'gym_class_id' => $newClassId,
            'user_id' => $newUserId,
            'check_in_id' => $checkInId,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];

        if ($this->option('update')) {
            DB::table('gym_class_user')->updateOrInsert([
                'gym_class_id' => $newClassId,
                'user_id' => $newUserId,
            ], $bookingData);
        } else {
            DB::table('gym_class_user')->insert($bookingData);
        }
    }
}
