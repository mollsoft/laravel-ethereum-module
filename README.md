# laravel-ethereum-module

Add this lines to `routes/console.php`
```php
use Illuminate\Support\Facades\Schedule;

...

Schedule::command('ethereum:sync')
    ->everyMinute()
    ->runInBackground();
```