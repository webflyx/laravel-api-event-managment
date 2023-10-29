<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;

trait CanLoadRelationships
{
    protected function hasIncludeRelation(string $relation): bool
    {
        $include = request()->query('include');
        $relations = array_map('trim', explode(',', $include));

        return in_array($relation, $relations);
    }

    public function loadRelationships(
        Model|QueryBuilder|EloquentBuilder $for,
        ?array $relations = null
    ): Model|QueryBuilder|EloquentBuilder
    {
        $relations = $relations ? $relations : [];

        foreach( $relations as $relation ) {
            $for->when(
                $this->hasIncludeRelation($relation),
                fn($q) => $for instanceof Model ? $for->load($relation) : $q->with($relation)
            );
        }

        return $for;
    }
}