# Kernel middleware

Because routes use `role:Super Admin`, make sure Spatie Permission's middleware is registered in `app/Http/Kernel.php`:

'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,

Or use Spatie's standard Laravel 10 package installation/configuration.
