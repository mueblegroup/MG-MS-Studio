<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\ClassSession;
use App\Models\ClassSessionAssignment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\PlanSession;
use App\Models\Studio;
use App\Models\User;
use App\Models\UserPlan;
use App\Support\TenantManager;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class QrAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private Studio $studio;

    private User $teacher;

    private User $student;

    private User $owner;

    private ClassSession $classSession;

    private PlanSession $planSession;

    private string $origin = 'https://qr.classm8.test';

    protected function migrateFreshUsing(): array
    {
        if (config('database.default') !== 'sqlite') {
            return [];
        }

        // The legacy ENUM-alter migration is MySQL-only. Run every other real
        // migration (including the QR migration); widen the test column below.
        return ['--realpath' => true, '--path' => array_values(array_filter(
            glob(database_path('migrations/*.php')),
            fn ($path) => ! str_ends_with($path, '2026_06_02_000002_allow_subscription_class_type.php')
        ))];
    }

    protected function setUp(): void
    {
        parent::setUp();
        if (config('database.default') === 'sqlite') {
            Schema::table('classes', fn (Blueprint $table) => $table->string('type')->default('single')->change());
        }
        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('q', 32)),
            'app.url' => $this->origin,
            'app.timezone' => 'Asia/Kuala_Lumpur',
            'saas.root_domain' => 'classm8.test',
            'saas.central_domains' => ['classm8.test'],
        ]);
        date_default_timezone_set('Asia/Kuala_Lumpur');
        Carbon::setTestNow(Carbon::parse('2026-09-18 19:00:00', 'Asia/Kuala_Lumpur'));
        URL::forceRootUrl($this->origin);
        URL::forceScheme('https');
        $this->withServerVariables(['HTTP_HOST' => 'qr.classm8.test', 'SERVER_NAME' => 'qr.classm8.test', 'HTTPS' => 'on', 'SERVER_PORT' => 443]);
        $this->owner = User::factory()->create(['role' => 'admin', 'studio_id' => null]);
        $this->studio = Studio::create([
            'name' => 'QR Studio', 'slug' => 'qr', 'subdomain' => 'qr',
            'owner_user_id' => $this->owner->id, 'status' => 'active',
            'settings' => ['timezone' => 'Asia/Kuala_Lumpur'],
        ]);
        app(TenantManager::class)->set($this->studio);
        $this->teacher = User::factory()->create(['role' => 'teacher', 'studio_id' => $this->studio->id]);
        $this->student = User::factory()->create(['role' => 'student', 'studio_id' => $this->studio->id]);
        $class = ClassModel::create([
            'studio_id' => $this->studio->id, 'name' => 'Dance class', 'teacher_id' => $this->teacher->id,
            'type' => 'single', 'price' => 50,
        ]);
        $this->classSession = ClassSession::create([
            'studio_id' => $this->studio->id, 'class_id' => $class->id,
            'start_time' => '2026-09-18 19:00:00', 'end_time' => '2026-09-18 20:00:00',
        ]);
        ClassSessionAssignment::create([
            'studio_id' => $this->studio->id, 'user_id' => $this->student->id,
            'class_session_id' => $this->classSession->id, 'assigned_by' => $this->teacher->id, 'status' => 'assigned',
        ]);
        $plan = Plan::create([
            'studio_id' => $this->studio->id, 'name' => 'Dance plan', 'teacher_id' => $this->teacher->id,
            'price' => 100, 'is_active' => true,
        ]);
        $this->planSession = PlanSession::create([
            'studio_id' => $this->studio->id, 'plan_id' => $plan->id,
            'start_time' => '2026-09-18 19:00:00', 'end_time' => '2026-09-18 20:00:00',
        ]);
        UserPlan::create([
            'studio_id' => $this->studio->id, 'user_id' => $this->student->id, 'plan_id' => $plan->id,
            'starts_on' => '2026-09-01', 'ends_on' => '2026-09-30', 'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        date_default_timezone_set('UTC');
        parent::tearDown();
    }

    public static function kinds(): array
    {
        return [['class'], ['plan']];
    }

    private function sessionFor(string $kind): ClassSession|PlanSession
    {
        return $kind === 'class' ? $this->classSession : $this->planSession;
    }

    private function qr(string $kind = 'class'): string
    {
        $path = '/attendance/qr/'.$kind.'/'.$this->sessionFor($kind)->id;
        $this->actingAs($this->teacher)->post($path)->assertRedirect();
        $url = $this->getJson($path)->assertOk()->json('url');
        $this->assertIsString($url);

        return \Illuminate\Http\Request::create($url)->fullUrl();
    }

    #[DataProvider('kinds')]
    public function test_scan_is_read_only_and_confirmation_is_idempotent(string $kind): void
    {
        $url = $this->qr($kind);
        $this->actingAs($this->student)->get($url)->assertOk()->assertSee('Confirm attendance');
        $this->assertDatabaseCount('attendances', 0);
        $this->post($url, ['user_id' => $this->teacher->id, 'status' => 'no_show'])->assertRedirect($url);
        $firstTime = Attendance::first()->attended_at->toDateTimeString();
        $this->travel(1)->minutes();
        $this->post($url)->assertRedirect($url);
        $this->get($url)->assertOk()->assertSee('No changes were made.');
        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->student->id, 'studio_id' => $this->studio->id,
            'status' => 'attended', 'attended_at' => $firstTime,
        ]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'attendance.qr_checked_in', 'user_id' => $this->student->id]);
        $this->assertDatabaseCount('class_card_usages', 0);
        $path = '/attendance/qr/'.$kind.'/'.$this->sessionFor($kind)->id;
        $this->actingAs($this->teacher)->getJson($path)->assertJsonPath('attendees.0.name', $this->student->name);
    }

    public function test_student_and_unassigned_teacher_cannot_display_or_generate_qr(): void
    {
        $path = '/attendance/qr/class/'.$this->classSession->id;
        $this->actingAs($this->student)->get($path)->assertForbidden();
        $this->post($path)->assertForbidden();
        $other = User::factory()->create(['role' => 'teacher', 'studio_id' => $this->studio->id]);
        $this->actingAs($other)->getJson($path)->assertForbidden();
        $this->post($path)->assertForbidden();
        $this->actingAs($this->owner)->post($path)->assertRedirect();
        $this->get($path)->assertOk()->assertSee('Scan to mark your attendance');
    }

    #[DataProvider('kinds')]
    public function test_guest_returns_to_scan_after_login(string $kind): void
    {
        $url = $this->qr($kind);
        Auth::logout();
        $this->get($url)->assertRedirect($this->origin.'/login');
        $this->post('/login', ['email' => $this->student->email, 'password' => 'password'])->assertRedirect($url);
        $this->get($url)->assertOk();
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_two_factor_login_preserves_scan_destination(): void
    {
        $url = $this->qr();
        $this->student->forceFill([
            'two_factor_secret' => 'TESTSECRET', 'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => ['RECOVERY-CODE'],
        ])->save();
        Auth::logout();
        $this->get($url)->assertRedirect($this->origin.'/login');
        $this->post('/login', ['email' => $this->student->email, 'password' => 'password'])
            ->assertRedirect($this->origin.'/two-factor-challenge');
        $this->assertGuest();
        $this->get($url)->assertRedirect($this->origin.'/login');
        $this->post('/two-factor-challenge', ['recovery_code' => 'RECOVERY-CODE'])->assertRedirect($url);
        $this->post($url)->assertRedirect($url);
        $this->assertDatabaseCount('attendances', 1);
    }

    #[DataProvider('kinds')]
    public function test_server_enforces_exact_end_time_even_after_page_opened(string $kind): void
    {
        $url = $this->qr($kind);
        $this->actingAs($this->student)->get($url)->assertOk();
        // Same instant as 20:00 in Kuala Lumpur, regardless of device/server display timezone.
        Carbon::setTestNow(Carbon::parse('2026-09-18 12:00:00', 'UTC'));
        $this->post($url)->assertForbidden()->assertSee('class has ended');
        $this->assertDatabaseCount('attendances', 0);
        $this->actingAs($this->teacher)->getJson('/attendance/qr/'.$kind.'/'.$this->sessionFor($kind)->id)
            ->assertOk()->assertJsonPath('url', null);
    }

    public function test_check_in_opens_exactly_fifteen_minutes_before_class(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-18 18:44:59'));
        $path = '/attendance/qr/class/'.$this->classSession->id;
        $this->actingAs($this->teacher)->post($path)->assertForbidden();
        Carbon::setTestNow(Carbon::parse('2026-09-18 18:45:00'));
        $url = $this->qr();
        $this->actingAs($this->student)->post($url)->assertRedirect($url);
    }

    #[DataProvider('kinds')]
    public function test_replaced_or_rescheduled_qr_cannot_be_reused(string $kind): void
    {
        $url = $this->qr($kind);
        $path = '/attendance/qr/'.$kind.'/'.$this->sessionFor($kind)->id;
        $this->post($path, ['replace' => true])->assertRedirect();
        $this->actingAs($this->student)->post($url)->assertForbidden();
        $replacement = $this->qr($kind);
        $session = $this->sessionFor($kind)->fresh();
        $session->update(['end_time' => '2026-09-18 21:00:00']);
        $session->update(['end_time' => '2026-09-18 20:00:00']);
        $this->actingAs($this->student)->post($replacement)->assertForbidden();
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_signature_tampering_and_copied_links_on_another_host_fail(): void
    {
        $url = $this->qr();
        $this->actingAs($this->student)->post($url.'&user_id=123')->assertForbidden();
        $this->post(str_replace('schedule=', 'schedule=bad', $url))->assertForbidden();
        $this->post(str_replace('/class/', '/plan/', $url))->assertForbidden();
        $this->post(str_replace('qr.classm8.test', 'classm8.test', $url))->assertForbidden();
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_student_from_another_studio_cannot_check_in(): void
    {
        $url = $this->qr();
        app(TenantManager::class)->clear();
        $otherStudio = Studio::create([
            'name' => 'Other', 'slug' => 'other', 'subdomain' => 'other',
            'owner_user_id' => $this->owner->id, 'status' => 'active',
        ]);
        $otherStudent = User::factory()->create(['role' => 'student', 'studio_id' => $otherStudio->id]);
        $this->actingAs($otherStudent)->post($url)->assertForbidden();
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_cancelled_session_and_removed_assignment_are_rejected(): void
    {
        $url = $this->qr();
        $this->classSession->update(['status' => 'cancelled']);
        $this->actingAs($this->student)->post($url)->assertForbidden();
        $this->classSession->update(['status' => 'scheduled']);
        $url = $this->qr();
        ClassSessionAssignment::query()->first()->delete();
        $this->actingAs($this->student)->post($url)->assertForbidden();
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_inactive_and_out_of_date_plan_enrolment_is_rejected(): void
    {
        $url = $this->qr('plan');
        $enrolment = UserPlan::first();
        $enrolment->update(['is_active' => false]);
        $this->actingAs($this->student)->post($url)->assertForbidden();
        $enrolment->update(['is_active' => true, 'ends_on' => '2026-09-17']);
        $this->post($url)->assertForbidden();
        $enrolment->update(['ends_on' => '2026-09-30', 'starts_on' => '2026-09-19']);
        $this->post($url)->assertForbidden();
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_subscription_requires_paid_fulfilled_order_for_exact_session(): void
    {
        $this->classSession->classModel->update(['type' => 'subscription']);
        $url = $this->qr();
        $this->actingAs($this->student)->post($url)->assertForbidden()->assertSee('Payment');
        $order = Order::create([
            'studio_id' => $this->studio->id, 'user_id' => $this->student->id,
            'status' => 'pending', 'currency' => 'MYR', 'subtotal' => 50, 'total' => 50,
        ]);
        $item = $order->items()->create([
            'studio_id' => $this->studio->id, 'purchasable_type' => ClassSession::class,
            'purchasable_id' => $this->classSession->id, 'quantity' => 1, 'unit_price' => 50, 'currency' => 'MYR',
        ]);
        $this->post($url)->assertForbidden();
        $order->update(['status' => 'paid', 'paid_at' => now()]);
        $this->post($url)->assertForbidden();
        $order->update(['fulfilled_at' => now()]);
        $item->update(['purchasable_id' => $this->classSession->id + 100]);
        $this->post($url)->assertForbidden();
        $item->update(['purchasable_id' => $this->classSession->id]);
        $this->post($url)->assertRedirect($url);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_students_cannot_override_staff_no_show(): void
    {
        $url = $this->qr();
        Attendance::create([
            'studio_id' => $this->studio->id, 'user_id' => $this->student->id,
            'class_session_assignment_id' => ClassSessionAssignment::first()->id, 'status' => 'no_show',
        ]);
        $this->actingAs($this->student)->post($url)->assertStatus(409);
        $this->assertDatabaseHas('attendances', ['status' => 'no_show']);
    }

    public function test_one_qr_accepts_multiple_enrolled_students_without_sharing_identity(): void
    {
        $url = $this->qr();
        $other = User::factory()->create(['role' => 'student', 'studio_id' => $this->studio->id]);
        ClassSessionAssignment::create([
            'studio_id' => $this->studio->id, 'user_id' => $other->id,
            'class_session_id' => $this->classSession->id, 'assigned_by' => $this->teacher->id, 'status' => 'assigned',
        ]);
        $this->actingAs($this->student)->post($url)->assertRedirect($url);
        $this->actingAs($other)->post($url)->assertRedirect($url);
        $this->assertDatabaseCount('attendances', 2);
        $this->assertDatabaseHas('attendances', ['user_id' => $this->student->id]);
        $this->assertDatabaseHas('attendances', ['user_id' => $other->id]);
    }

    public function test_reassignment_does_not_duplicate_an_existing_session_check_in(): void
    {
        $url = $this->qr();
        $this->actingAs($this->student)->post($url)->assertRedirect($url);
        $assignment = ClassSessionAssignment::first();
        $assignment->delete();
        $this->post($url)->assertForbidden();
        $assignment->restore();
        $this->post($url)->assertRedirect($url);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_confirmation_requires_csrf(): void
    {
        $url = $this->qr();
        $this->app->instance('env', 'production'); // Enable Laravel's real CSRF check during this request.
        $this->actingAs($this->student)->post($url)->assertStatus(419);
        $this->assertDatabaseCount('attendances', 0);
    }
}
