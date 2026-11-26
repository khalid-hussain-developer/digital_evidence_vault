@php
    /** @var ?\App\Models\CaseRecord $case */
    $case ??= null;
    $selectedStatus = old('status', $case?->status?->value ?? \App\Enums\CaseStatus::Open->value);
@endphp

<div class="mb-3">
    <x-input-label for="title" value="Case Title" />
    <x-text-input
        id="title"
        name="title"
        type="text"
        :value="old('title', $case?->title)"
        :class="$errors->has('title') ? 'is-invalid' : ''"
        required
        autofocus
    />
    <x-input-error :messages="$errors->get('title')" />
</div>

<div class="mb-3">
    <x-input-label for="description" value="Description" />
    <textarea
        id="description"
        name="description"
        rows="4"
        class="form-control @error('description') is-invalid @enderror"
    >{{ old('description', $case?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" />
</div>

<div class="mb-3">
    <x-input-label for="status" value="Status" />
    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
        @foreach ($statuses as $status)
            <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>
                {{ $status->label() }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" />
</div>
