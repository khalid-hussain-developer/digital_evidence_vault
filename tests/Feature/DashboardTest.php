<?php

use App\Enums\EvidenceStatus;
use App\Enums\UserRole;
use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\User;

test('an investigator only sees counts for cases and evidence they can access, and no user total', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $outsider = User::factory()->role(UserRole::Investigator)->create();

    $ownCase = CaseRecord::factory()->create(['created_by' => $investigator->id]);
    $ownCase->assignedUsers()->attach($investigator->id, ['assigned_by' => $investigator->id]);
    Evidence::factory()->create(['case_id' => $ownCase->id, 'uploaded_by' => $investigator->id]);

    $otherCase = CaseRecord::factory()->create(['created_by' => $outsider->id]);
    $otherCase->assignedUsers()->attach($outsider->id, ['assigned_by' => $outsider->id]);
    Evidence::factory()->create(['case_id' => $otherCase->id, 'uploaded_by' => $outsider->id]);

    $response = $this->actingAs($investigator)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Total Cases');
    $response->assertDontSee('Total Users');
});

test('an admin sees the total user count alongside every case and evidence record', function () {
    $admin = User::factory()->role(UserRole::Admin)->create();
    $investigator = User::factory()->role(UserRole::Investigator)->create();

    $case = CaseRecord::factory()->create(['created_by' => $investigator->id]);
    Evidence::factory()->create([
        'case_id' => $case->id,
        'uploaded_by' => $investigator->id,
        'registration_status' => EvidenceStatus::Registered,
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Total Users');
    $response->assertSeeInOrder(['Total Users', (string) User::count()]);
    $response->assertSeeInOrder(['Registered on Blockchain', '1']);
});
