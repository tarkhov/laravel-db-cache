# Laravel DB Cache

Laravel database cache.

### Contents

1. [Compatibility](#compatibility)
2. [Installation](#installation)
   1. [Composer](#composer)
3. [Usage](#usage)
   1. [Using DB facade query](#using-db-facade-query)
   1. [Using Eloquent ORM query](#using-eloquent-orm-query)
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
        $builder = DB::table('users')->select(['id', 'name'])->where('id', $id);
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