<?php
namespace Dapatchi\LaravelCore\Http\Traits;

use Dapatchi\LaravelCore\Entities\ValidateOnCreate;
use Dapatchi\LaravelCore\Requests\FormRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

trait StoresEntity
{
    /**
     * @return Model
     */
    abstract protected function getModel();

    /**
     * @return Model
     * @throws \Exception
     */
    public function store()
    {
        $lockKey = $this->getStoreLockKey();

        if (!$lockKey) {
            return $this->doStore();
        }

        $lock = Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            throw new \Exception('Failed to acquire lock ' . $lockKey);
        }

        try {
            $createdEntity = $this->doStore();
        } finally {
            $lock->release();
        }

        return $createdEntity;
    }

    /**
     * @return Model
     */
    protected function doStore()
    {
        $input = request()->all();
        $model = $this->getModel();

        /** @var Model $object */
        $object = new $model();

        if ($object instanceof ValidateOnCreate) {
            $validatorClass = $object->getCreateRequestClass();

            // Form requests execute when they are invoked
            /** @var FormRequest $formRequest */
            $formRequest = app($validatorClass);
            $input = $formRequest->getValidatedData();
        }

        $object->fill($input);
        $object->save();

        return $object;
    }

    /**
     * @return string
     */
    protected function getStoreLockKey()
    {
        return null;
    }
}
