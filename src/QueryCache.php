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
    ) {
        $this->key = CacheKey::make($builder);
    }

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

    public function delete(): void
    {
        if (!empty($this->tags)) {
            Cache::tags($this->tags)->flush();
        } elseif ($this->key !== null) {
            Cache::forget($this->key);
        }
    }

    public function one(callable $callback): mixed
    {
        $result = $this->get();
        if ($result) {
            $result = json_decode($result);
            if ($this->builder instanceof EloquentBuilder) {
                $result = $this->builder->getModel()->newFromBuilder($result);
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
        $result = $this->get();
        if ($result) {
            $result = (array) json_decode($result);
            if ($this->builder instanceof EloquentBuilder) {
                $result = $this->builder->getModel()->hydrate($result);
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