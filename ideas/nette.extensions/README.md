# Extensions
These are for nette framework

## [RunInicializeExtension](RunInicializeExtension.php)
Support run services in **Container::initialize()** without save to memory. 
```neon
extensions:
    runExtension: h4kuna\Extensions\RunInicializeExtension

runExtension:
    services:
        - App\Events\Connection
```
For example if you need attach events...
```php
<?php

namespace App\Events;


use Nette\Database,
    Nette\Security;

class Connection
{
    public function __construct(Database\Connection $connection, Security\User $user)
    {
        $connection->onConnect[] = static function ($connection) use ($user) {
            $connection->query('login_user(?)', $user->getId());
        };
    }
}
```

