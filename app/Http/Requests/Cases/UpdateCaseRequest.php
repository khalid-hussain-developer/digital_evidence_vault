<?php

namespace App\Http\Requests\Cases;

use App\Enums\CaseStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateCaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * The route-model-bound CaseRecord is passed to the policy by name
     * ('caseRecord') via the {case} route parameter — see routes/web.php.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('case'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The case number is not editable here — it is the case's permanent
     * identifier, set once at creation.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', new Enum(CaseStatus::class)],
        ];
    }
}
