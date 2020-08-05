<?php
namespace Dapatchi\LaravelCore\Http\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait FetchesCollection
{
    /**
     * @return Model
     */
    abstract protected function getModel();

    /**
     * @return LengthAwarePaginator|Collection
     */
    public function fetchAll()
    {
        $queryBuilder = $this->getFetchAllQueryBuilder();

        if ($page = request()->query('page')) {
            $pageSize = request()->query('page_size', config('database.pagination.items_per_page'));
            return $queryBuilder->paginate($pageSize);
        }

        return $queryBuilder->get();
    }

    /**
     * @return string[]
     */
    protected function getRestrictedQueryParameters()
    {
        return [];
    }

    /**
     * @param Builder $queryBuilder
     * @param string $parameter
     * @param string|int $value
     * @return bool
     */
    protected function registerCustomParameterHandling(Builder $queryBuilder, string $parameter, $value)
    {
        return false;
    }

    /**
     * @return Builder
     */
    protected function getFetchAllQueryBuilder()
    {
        $model = $this->getModel();
        $queryBuilder = $model::query();
        $queryBuilder->select((new $model)->getTable() . '.*');
        $queryParams = Arr::except(
            request()->query(),
            ['includes', 'page', 'page_size', 'sort', 'sort_dir'] + $this->getRestrictedQueryParameters()
        );

        foreach ($queryParams as $queryParam => $value) {
            $result = $this->registerCustomParameterHandling($queryBuilder, $queryParam, $value);
            if ($result !== false) {
                unset($queryParams[$queryParam]);
            }
        }

        $queryBuilder = $queryBuilder->where($queryParams);

        $orderDirection = 'asc';
        if (method_exists($this, 'orderDirection')) {
            $orderDirection = $this->orderDirection();
        }

        $orderColumn = 'created_at';
        if (method_exists($this, 'orderColumn')) {
            $orderColumn = $this->orderColumn();
        }

        if (Str::contains($orderColumn, '.')) {
            list($table, $column) = explode('.', $orderColumn);

            $relation = (new $model)->$table();

            if ($relation instanceof BelongsTo) {
                $queryBuilder->join($relation->getRelated()->getTable(), $relation->getQualifiedForeignKeyName(), '=', $relation->getQualifiedOwnerKeyName());
                $queryBuilder->orderBy($relation->getRelated()->qualifyColumn($column), $orderDirection);
            }
        } else {
            $queryBuilder->orderBy($orderColumn, $orderDirection);
        }

        return $queryBuilder;
    }

    /**
     * @return string
     */
    protected function orderColumn()
    {
        return request()->query('sort', 'created_at');
    }

    /**
     * @return string
     */
    protected function orderDirection()
    {
        return request()->query('sort_dir', 'asc');
    }
}
