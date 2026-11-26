<?php

use App\Enums\UserRole;
use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake(config('evidence.storage_disk'));
});

function assignedCase(User $owner): CaseRecord
{
    $case = CaseRecord::factory()->create(['created_by' => $owner->id]);
    $case->assignedUsers()->attach($owner->id, ['assigned_by' => $owner->id]);

    return $case;
}

test('an assigned investigator can upload evidence and its hash is computed on the server', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $case = assignedCase($investigator);

    $file = UploadedFile::fake()->image('crime-scene.jpg', 20, 20);
    $expectedHash = hash_file('sha256', $file->getRealPath());

    $response = $this->actingAs($investigator)->post(route('cases.evidence.store', $case), [
        'file' => $file,
    ]);

    $evidence = Evidence::where('case_id', $case->id)->first();

    $response->assertRedirect(route('evidence.show', $evidence));
    expect($evidence)->not->toBeNull();
    expect($evidence->sha256_hash)->toBe($expectedHash);
    expect($evidence->original_filename)->toBe('crime-scene.jpg');
    expect($evidence->uploaded_by)->toBe($investigator->id);
    expect($evidence->current_custodian_id)->toBe($investigator->id);

    Storage::disk(config('evidence.storage_disk'))->assertExists($evidence->file_path);
});

test('a user who is not assigned to the case can not upload evidence to it', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $outsider = User::factory()->role(UserRole::Investigator)->create();
    $case = assignedCase($investigator);

    $response = $this->actingAs($outsider)->post(route('cases.evidence.store', $case), [
        'file' => UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertForbidden();
    expect(Evidence::where('case_id', $case->id)->exists())->toBeFalse();
});

test('a forensic officer can not upload evidence', function () {
    $officer = User::factory()->role(UserRole::ForensicOfficer)->create();
    $case = assignedCase($officer);

    $response = $this->actingAs($officer)->post(route('cases.evidence.store', $case), [
        'file' => UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertForbidden();
});

test('an unsupported file type is rejected', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $case = assignedCase($investigator);

    $response = $this->actingAs($investigator)->post(route('cases.evidence.store', $case), [
        'file' => UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload'),
    ]);

    $response->assertSessionHasErrors('file');
    expect(Evidence::where('case_id', $case->id)->exists())->toBeFalse();
});

test('a file larger than the configured limit is rejected', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $case = assignedCase($investigator);

    $oversizeKb = config('evidence.max_upload_size_kb') + 1024;

    $response = $this->actingAs($investigator)->post(route('cases.evidence.store', $case), [
        'file' => UploadedFile::fake()->create('large.pdf', $oversizeKb, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors('file');
    expect(Evidence::where('case_id', $case->id)->exists())->toBeFalse();
});

test('an authorized user can download evidence and receives the original file content', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $case = assignedCase($investigator);

    $content = 'exact bytes of the fake evidence file';
    $file = UploadedFile::fake()->createWithContent('statement.pdf', $content);

    $this->actingAs($investigator)->post(route('cases.evidence.store', $case), ['file' => $file]);
    $evidence = Evidence::where('case_id', $case->id)->first();

    $response = $this->actingAs($investigator)->get(route('evidence.download', $evidence));

    $response->assertOk();
    expect($response->streamedContent())->toBe($content);
});

test('a user who can not view the case can not download its evidence', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $outsider = User::factory()->role(UserRole::Investigator)->create();
    $case = assignedCase($investigator);

    $this->actingAs($investigator)->post(route('cases.evidence.store', $case), [
        'file' => UploadedFile::fake()->image('photo.jpg'),
    ]);
    $evidence = Evidence::where('case_id', $case->id)->first();

    $this->actingAs($outsider)->get(route('evidence.download', $evidence))->assertForbidden();
});

test('the evidence list only shows evidence from cases the user can see', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $outsider = User::factory()->role(UserRole::Investigator)->create();

    $ownCase = assignedCase($investigator);
    $this->actingAs($investigator)->post(route('cases.evidence.store', $ownCase), [
        'file' => UploadedFile::fake()->image('mine.jpg'),
    ]);
    $ownEvidence = Evidence::where('case_id', $ownCase->id)->first();

    $otherCase = assignedCase($outsider);
    $this->actingAs($outsider)->post(route('cases.evidence.store', $otherCase), [
        'file' => UploadedFile::fake()->image('theirs.jpg'),
    ]);
    $otherEvidence = Evidence::where('case_id', $otherCase->id)->first();

    $response = $this->actingAs($investigator)->get(route('evidence.index'));

    $response->assertOk();
    $response->assertSee($ownEvidence->evidence_id);
    $response->assertDontSee($otherEvidence->evidence_id);
});
