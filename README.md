This package provides an offset based pagination already integrated with Laravel's [query builder](https://laravel.com/docs/master/queries) and [Eloquent ORM](https://laravel.com/docs/master/eloquent).
It calculates the SQL query limits automatically by checking the requests GET parameters, and automatically builds the next and previous urls for you.

## Installation

You can install this package via composer using:

```bash
composer require chelout/offset-pagination
```

The package will automatically register itself.

### Config

To publish the config file to `config/offset_pagination.php` run:

````bash
php artisan vendor:publish --provider="Chelout\OffsetPagination\CursorPaginationServiceProvider" --tag="config"
````

This will publish the following [file](config/offset_pagination.php). 
You can customize default items per page and maximum items per page. 

## Basic Usage

### Paginating Query Builder Results
    
````php
public function index()
{
    $users = DB::table('users')->offsetPaginate();
    
    return $users;
}
````

### Paginating Eloquent Results

````php
$users = User::offsetPaginate(15);
````

Of course, you may call paginate after setting other constraints on the query, such as where clauses:

````php
$users = User::where('votes', '>', 100)->offsetPaginate(15);
````

Or sorting your results:

````php
$users = User::orderBy('id', 'desc')->offsetPaginate(15);
````
## Displaying Pagination Results

### Converting to JSON

A basic return will transform the paginator to JSON and will have a result like this:

````php
Route::get('api/v1/users', function () {
    return App\User::offsetPaginate();
});
````

Calling `api/v1` will output:

````json
{
   "data": [
        {}, 
   ],
    "prev": 85,
    "next": 95,
    "limit": 5,
    "total": 101,
    "next_page_url": "https://example.com/api/v1/users?limit=5&offset=95",
    "prev_page_url": "https://example.com/api/v1/users?limit=5&offset=85"
}
````

### Using a Resource Collection

By default, Laravel's API Resources when using them as collections, they will output a paginator's metadata
into `links` and `meta`.

````json
    {
        "data":[
            {}, 
        ],
        "links": {
            "first": "https://example.com/api/v1/users",
            "last": "https://example.com/api/v1/users?offset=95",
            "prev": "https://example.com/api/v1/users?offset=55",
            "next": "https://example.com/api/v1/users?offset=65"
        },
        "meta": {
            "offset": 60
            "prev": 55,
            "next": 65,
            "limit": 5,
            "total": 100
        },
    }
````

## Testing

Run the tests with:
```bash
vendor/bin/phpunit
```

## Credits

- [Viacheslav Ostrovskiy](https://github.com/chelout)
- All Contributors

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.