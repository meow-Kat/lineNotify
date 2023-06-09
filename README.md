# LineNotify
Use Line notify plugin for laravel

## Register Package Service Provider
```php!
// config/app.php
'providers' => [
  ...
  Gcreate\LineNotify\LineNotifyServiceProvider::class,
  ...
],
```
## Make Config
```
php artisan vendor:publish --tag=line-notify-config
```

## Make Migration to Users Table
```
php artisan vendor:publish --tag=line-notify-config

php artisan migrate
```
> add line_notify_access_token column and line_notify_state column

## Add .env Parameter
```
LineNotifyClientID=Your Line Service ClientID
LineNotifyClientSecret=Your Line Service ClientSecret
```
## Useage
### Binding User Line
```php!
use Gcreate\LineNotify\Controllers\LineNotify;

$redirectUrl = LineNotify::bindLineUrl($userModel)

return redirect($redirectUrl);    // go binding line page
```
> If binding user success, you need to save user Line notify token from callback

### Recvive User Line Callback
To your routes/web.php add this

```php!
Route::post( 'your/callback/url', [yourController::class, 'callback']);
```

Go to your Middleware/VerifyCsrfToken.php add this 
```php!
protected $except = [
    'your/callback/url',
];
```
```php!
use Gcreate\LineNotify\Controllers\LineNotify;

$callbackStatus = LineNotify::callback($request, $user);
```

### Revoke User Line
```php!
use Gcreate\LineNotify\Controllers\LineNotify;

$result = LineNotify::revoke($user);    // true | error
if($result){
    // ...do something
}
```

### Send Message
```php!
use Gcreate\LineNotify\Controllers\LineNotify;

LineNotify::send($user,$text);
```
