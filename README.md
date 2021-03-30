# Nova Translatable Field

Adds the ability to show and edit translated fields created
with [astrotomic/laravel-translatable](https://github.com/Astrotomic/laravel-translatable) package.

## Installation and usage

```shell script
composer require soluzione-software/nova-translatable-field
```

```php
<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use SoluzioneSoftware\Nova\Fields\Translatable;

class User extends Resource
{
    // ...

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [            
            Translatable::make(
                Text::make('Name')
            ),
        ];
    }

    // ...
}
```

## Credits

Thanks to:

- [@yeswedev](https://framagit.org/yeswedev)
  for [YWD Nova Translatable](https://framagit.org/yeswedev/ywd_nova-translatable)
