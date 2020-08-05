<?php
namespace Dapatchi\LaravelCore\Requests;

use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;

class FormRequest extends LaravelFormRequest
{
    /**
     * @return array
     */
    public function getValidatedData()
    {
        return $this->validator->validated();
    }
}
