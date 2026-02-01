# Laravel DB Cache

Laravel database query caching.

### Contents

1. [Compatibility](#compatibility)
2. [Installation](#installation)
   1. [Composer](#composer)
3. [Usage](#usage)
   1. [How it work](#how-it-work)
   2. [Using DB facade query](#using-db-facade-query)
   3. [Using Eloquent ORM query](#using-eloquent-orm-query)
4. [Author](#author)
5. [License](#license)

## Compatibility

Library | Version
------- | -------
Laravel | >= 12.0

## Installation

### Composer

```bash
composer require tarkhov/laravel-db-cache
```

## Usage

### How it work

1. Import **QueryCache** class: `use LaravelDBCache\QueryCache`.
2. Creating a query builder without retrieving data: `$builder = DB::table('users')->select(['id', 'name'])->where('id', $id);`.
3. Get new **QueryCache** instance by passing query builder as argument in constructor: `$queryCache = new QueryCache($builder);`. Since each sql query is unique, the constructor will generate a caching key as a hash from the sql query, which guarantees the absence of collisions and duplicates.  Optionally, you can pass the amount of cache time in minutes as the 2nd argument and cache tags as the 3rd argument if you plan to use them instead of the cache key: `$queryCache = new QueryCache($builder, 60, ['my_tag']);`.
4. Get cached data from storage or automatically saving the result in the cache if the given query has not yet been added to the cache storage using **one** method for single row query or **many** for multiple rows query with callback as argument, this callback has one argument - it's your query builder without any modifications: `$result = $queryCache->one(fn($builder) => $builder->first());` or `$result = $queryCache->many(fn($builder) => $builder->get());`. You can use any method for data retrieving like `first()`, `firstOrFail()`, `get()` and others, because it's a native non modified Laravel query builder.

### Using DB facade query

Retrieve one row.

```php
<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use LaravelDBCache\QueryCache;

class UserController extends Controller
{
    public function oneWithDB(string $id): object
    {
        // create builder query 
        $builder = DB::table('users')->select(['id', 'name'])->where('id', $id);
        // retrieve cached result
        $user = (new QueryCache($builder))->one(fn($builder) => $builder->first());
        return $user;
    }
}
```

Retrieve many rows.

```php
<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use LaravelDBCache\QueryCache;

class UserController extends Controller
{
    public function manyWithDB(): array
    {
        $builder = DB::table('users')->select(['id', 'name'])->limit(15);
        $user = (new QueryCache($builder))->many(fn($builder) => $builder->get());
        return $user;
    }
}
```

### Using Eloquent ORM query

Retrieve one row.

```php
<?php
namespace App\Http\Controllers;

use LaravelDBCache\QueryCache;
use App\Models\User;

class UserController extends Controller
{
    public function oneWithORM(string $id): User
    {
        $builder = User::select(['id', 'name'])->where('id', $id);
        $user = (new QueryCache($builder))->one(fn($builder) => $builder->first());
        return $user;
    }
}
```

Retrieve many rows.

```php
<?php
namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use LaravelDBCache\QueryCache;
use App\Models\User;

class UserController extends Controller
{
    public function manyWithORM(): Collection
    {
        $builder = User::select(['id', 'name'])->limit(5);
        $user = (new QueryCache($builder))->many(fn($builder) => $builder->get());
        return $user;
    }
}
```

## Author

* [Twitter](https://x.com/tarkhovich)
* [Medium](https://medium.com/@tarkhov)

## License

This project is licensed under the **MIT License** - see the `LICENSE` file for details.