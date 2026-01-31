<?php
namespace LaravelDBCache;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Cache;

class QueryCache
{
    protected $key = null;

    public function __construct(
        protected Builder | EloquentBuilder $builder,
        protected ?int $minutes = null,
        protected array $tags = []
    ) {}

    protected function get(): string
    {
        if (!empty($this->tags)) {
            return Cache::tags($this->tags)->get($this->key);
        } else {
            return Cache::get($this->key);
        }
    }

    protected function save($result): void
    {
        if (!empty($this->tags)) {
            Cache::tags($this->tags)->put($this->key, (string) $result, $this->minutes);
        } else {
            Cache::put($this->key, (string) $result, $this->minutes);
        }
    }

    protected function delete(): void
    {
        if ($this->key !== null) {
            Cache::forget($this->key);
        } 

        if (!empty($this->tags)) {
            Cache::tags($this->tags)->flush();
        }
    }

    public function makeKey(): string
    {
        $query = $this->builder->getQuery();
        $sql = str_replace('?', '%s', $query->toSql());
        $bindings = array_map(function ($binding) {
            return is_numeric($binding) ? $binding : "'" . $binding . "'";
        }, $query->getBindings());
        return sha1(vsprintf($sql, $bindings));
    }

    public function one(callable $callback): mixed
    {
        if ($this->key === null) {
            $this->key = $this->makeKey();
        }

        $result = $this->get();
        if ($result) {
            if ($this->builder instanceof EloquentBuilder) {
                $result = $this->builder->getModel()->newFromBuilder(json_decode($result));
            } else {
                $result = json_decode($result);
            }
        } else {
            $result = $callback($this->builder);
            if ($result) {
                $this->save($result);
            }
        }

        return $result;
    }

    public function many(callable $callback): mixed
    {
        if ($this->key === null) {
            $this->key = $this->makeKey();
        }

        $result = $this->get();
        if ($result) {
            if ($this->builder instanceof EloquentBuilder) {
                $result = $this->builder->getModel()->hydrate((array) json_decode($result));
            } else {
                $result = (array) json_decode($result);
            }
        } else {
            $result = $callback($this->builder);
            if ($result && $result->isNotEmpty()) {
                $this->save($result);
            }
        }

        return $result;
    }
}