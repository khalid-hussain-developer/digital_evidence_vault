<?php

namespace App\Http\Requests\Evidence;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEvidenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * The route-model-bound CaseRecord is passed to the policy by name
     * ('case') via the {case} route parameter — see routes/web.php.
     */
    public function authorize(): bool
    {
        return $this->user()->can('uploadEvidence', $this->route('case'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The "mimes" rule checks the file's actual content-sniffed type, not
     * just its filename extension, so this is a real content check.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:'.implode(',', config('evidence.allowed_extensions')),
                'max:'.config('evidence.max_upload_size_kb'),
            ],
        ];
    }
}
