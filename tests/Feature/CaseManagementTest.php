<?php

use App\Enums\CaseStatus;
use App\Enums\UserRole;
use App\Models\CaseRecord;
use App\Models\User;

test('an investigator can create a case and is automatically assigned to it', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();

    $response = $this->actingAs($investigator)->post('/cases', [
        'case_number' => 'CASE-0001',
        'title' => 'Online banking fraud',
        'description' => 'Suspicious transfer reported by the victim.',
        'status' => CaseStatus::Open->value,
    ]);

    $case = CaseRecord::where('case_number', 'CASE-0001')->first();

    $response->assertRedirect(route('cases.show', $case));
    expect($case)->not->toBeNull();
    expect($case->created_by)->toBe($investigator->id);
    expect($case->assignedUsers->pluck('id'))->toContain($investigator->id);
});

test('a forensic officer can not create a case', function () {
    $officer = User::factory()->role(UserRole::ForensicOfficer)->create();

    $response = $this->actingAs($officer)->post('/cases', [
        'case_number' => 'CASE-0002',
        'title' => 'Attempted case creation',
        'status' => CaseStatus::Open->value,
    ]);

    $response->assertForbidden();
    expect(CaseRecord::where('case_number', 'CASE-0002')->exists())->toBeFalse();
});

test('a user only sees cases they created or were assigned to', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $otherInvestigator = User::factory()->role(UserRole::Investigator)->create();

    $ownCase = CaseRecord::factory()->create(['created_by' => $investigator->id]);
    $ownCase->assignedUsers()->attach($investigator->id, ['assigned_by' => $investigator->id]);

    $otherCase = CaseRecord::factory()->create(['created_by' => $otherInvestigator->id]);
    $otherCase->assignedUsers()->attach($otherInvestigator->id, ['assigned_by' => $otherInvestigator->id]);

    $response = $this->actingAs($investigator)->get('/cases');

    $response->assertOk();
    $response->assertSee($ownCase->case_number);
    $response->assertDontSee($otherCase->case_number);
});

test('an admin sees every case regardless of assignment', function () {
    $admin = User::factory()->role(UserRole::Admin)->create();
    $investigator = User::factory()->role(UserRole::Investigator)->create();

    $case = CaseRecord::factory()->create(['created_by' => $investigator->id]);
    $case->assignedUsers()->attach($investigator->id, ['assigned_by' => $investigator->id]);

    $response = $this->actingAs($admin)->get('/cases');

    $response->assertOk();
    $response->assertSee($case->case_number);
});

test('a user who is not assigned to a case can not view it', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $outsider = User::factory()->role(UserRole::Investigator)->create();

    $case = CaseRecord::factory()->create(['created_by' => $investigator->id]);
    $case->assignedUsers()->attach($investigator->id, ['assigned_by' => $investigator->id]);

    $response = $this->actingAs($outsider)->get(route('cases.show', $case));

    $response->assertForbidden();
});

test('a reviewer assigned to a case can view it but can not edit it', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $reviewer = User::factory()->role(UserRole::Reviewer)->create();

    $case = CaseRecord::factory()->create(['created_by' => $investigator->id]);
    $case->assignedUsers()->attach([$investigator->id, $reviewer->id], ['assigned_by' => $investigator->id]);

    $this->actingAs($reviewer)->get(route('cases.show', $case))->assertOk();
    $this->actingAs($reviewer)->get(route('cases.edit', $case))->assertForbidden();
});

test('an admin can assign a user to a case and remove them again', function () {
    $admin = User::factory()->role(UserRole::Admin)->create();
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $forensicOfficer = User::factory()->role(UserRole::ForensicOfficer)->create();

    $case = CaseRecord::factory()->create(['created_by' => $investigator->id]);
    $case->assignedUsers()->attach($investigator->id, ['assigned_by' => $investigator->id]);

    $this->actingAs($admin)
        ->post(route('cases.assignments.store', $case), ['user_id' => $forensicOfficer->id])
        ->assertRedirect(route('cases.show', $case));

    expect($case->assignedUsers()->pluck('users.id'))->toContain($forensicOfficer->id);

    $this->actingAs($admin)
        ->delete(route('cases.assignments.destroy', [$case, $forensicOfficer]))
        ->assertRedirect(route('cases.show', $case));

    expect($case->assignedUsers()->pluck('users.id'))->not->toContain($forensicOfficer->id);
});

test('the case creator can not be removed from the case', function () {
    $admin = User::factory()->role(UserRole::Admin)->create();
    $investigator = User::factory()->role(UserRole::Investigator)->create();

    $case = CaseRecord::factory()->create(['created_by' => $investigator->id]);
    $case->assignedUsers()->attach($investigator->id, ['assigned_by' => $investigator->id]);

    $response = $this->actingAs($admin)
        ->delete(route('cases.assignments.destroy', [$case, $investigator]));

    $response->assertSessionHasErrors('assignment');
    expect($case->assignedUsers()->pluck('users.id'))->toContain($investigator->id);
});
