<?php

namespace App\Http\Requests\Cases;

use App\Enums\CaseStatus;
use App\Models\CaseRecord;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreCaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', CaseRecord::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'case_number' => ['required', 'string', 'max:50', 'unique:case_records,case_number'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', new Enum(CaseStatus::class)],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => [Rule::exists('users', 'id')],
        ];
    }
}
