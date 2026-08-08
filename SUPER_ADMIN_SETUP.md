# Existing Laravel User model integration

Ensure `App\Models\User` uses `Spatie\Permission\Traits\HasRoles`.

Add:
use HasRoles;

The seed creates the `Super Admin` role and assigns it to `admin@example.com`.

If your Laravel 10 project already has a User model, merge this trait rather than replacing your model.
