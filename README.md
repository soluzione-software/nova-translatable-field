# Nova Translatable Field

## Installation and usage

``` php
composer require soluzione-software/nova-translatable-field
```

``` php
use SoluzioneSoftware\Nova\Fields\Translatable;
```

...

``` php
Translatable::make(Text::make(__('Name'), 'name')),
```

## Credits
Thanks to:
- [@yeswedev](https://framagit.org/yeswedev) for [YWD Nova Translatable](https://framagit.org/yeswedev/ywd_nova-translatable)
