<?php
namespace Dapatchi\LaravelCore\Http\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait DeletesEntity
{
    /**
     * @return Model
     */
    abstract protected function getModel();

    /**
     * @param $id
     *
     * @return array
     */
    public function delete($id)
    {
        $model = $this->getModel();

        $object = $model::findOrFail($id);

        $loggedInUser = Auth::user();
        if (!$loggedInUser || !$loggedInUser->can('delete', $object)) {
            throw new AuthorizationException();
        }

        $object->delete();

        return [
            'success' => true,
        ];
    }
}
