<?php
namespace Dapatchi\LaravelCore\Http\Traits;

use Dapatchi\LaravelCore\Entities\ValidateOnUpdate;
use Dapatchi\LaravelCore\Requests\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

trait UpdatesEntity
{
    /**
     * @return Model
     */
    abstract protected function getModel();

    /**
     * @param string $id
     *
     * @return Model
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function update($id)
    {
        $input = request()->all();
        $model = $this->getModel();

        /** @var Model $object */
        $object = $model::findOrFail($id);

        $loggedInUser = Auth::user();
        if (!$loggedInUser || !$loggedInUser->can('update', $object)) {
            throw new AuthorizationException();
        }

        if ($object instanceof ValidateOnUpdate) {
            $validatorClass = $object->getUpdateRequestClass();

            // Form requests execute when they are invoked
            /** @var FormRequest $formRequest */
            $formRequest = app($validatorClass);
            $input = $formRequest->getValidatedData();
        }

        $object->fill($input);
        $object->save();

        return $object;
    }
}
