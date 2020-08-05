<?php
namespace Dapatchi\LaravelCore\Http\Traits;

use Illuminate\Database\Eloquent\Model;

trait FetchesEntity
{
    /**
     * @return Model
     */
    abstract protected function getModel();

    /**
     * @param $id
     *
     * @return Model
     */
    public function fetch($id)
    {
        $model = $this->getModel();
        $object = $model::findOrFail($id);

        return $object;
    }
}
