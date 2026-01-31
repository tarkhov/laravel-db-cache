<?php
namespace LaravelDBCache;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class CacheKey
{
    public static function make(Builder | EloquentBuilder $builder): string
    {
        $query = $builder->getQuery();
        $sql = str_replace('?', '%s', $query->toSql());
        $bindings = array_map(function ($binding) {
            return is_numeric($binding) ? $binding : "'" . $binding . "'";
        }, $query->getBindings());
        return sha1(vsprintf($sql, $bindings));
    }
}