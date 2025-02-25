# Ship

Taylor Otwell said at the 2025 Laracon EU "We must ship".

He also made a demo of the new starter kits for Livewire, React and Vue. They are really nice but like Breeze they lack some features and it's normal, they are starter kits after all. So I built **Ship**.

**Ship** is a quick way to bootstrap our applications with configuration and features that we all need like Larastan, Rector, Content Security Policy, oAuth connection, Two way authentication, Sessions management, Tenant management for SaaS and basic configurations.

**Ship** doesn't contains things like Horizon, Telescope, Solo, Pulse and others because those package are really simple to install, we just need to require them with Composer and use their installation command.

> [!NOTE]
> **Ship** is a set of files and lines of code that I add to each of my projects manually... boring. It comes from other developers, other packages and me, it's a bunch of good stuff.

## What Ship can do for us?

- Configure our `AppServiceProvider` (immutable dates, vite prefetching, password rules, etc.)
- Change the `COOKIE_SESSION` env variable to an uuid
- Remove Laravel config files (optionable)
- Configure **Content Security Policy** with the [Spatie package](https://github.com/spatie/laravel-csp)
- Configure a **api management** feature with Sanctum for your users (like we have on Jetstream)
- Configure a **sessions management** feature for your users (like we have on Jetstream)
- Configure a **tenant management** feature for your users (like we have on Jetstream but you can choose the name of the model)
- Install [Larastan](https://github.com/larastan/larastan) already configured
- Install [Rector](https://github.com/rectorphp/rector) already configured

Every files are dropped in our applications, **Ship** is just an installation command with some stubs.

> [!WARNING]
> **Ship** is not complete yet. It contains only backend stuff but I intend to propose frontend for each stacks of the new starter kits when they'll be launched.

## Coming soon

- Tests with Pest and PHPUnit
- oAuth connection with Socialite
- Two Way Authentication

If you have more ideas don't hesitate to write an issue or even write a pull request, I would be glad to discuss about it.

## Installation

You can install the package via composer:

```
composer require --dev axeldotdev/ship
```

Then you just need to run the installation command:

```
php artisan ship:install
```

> [!TIP]
> You can remove the package when you are done. It's just an installation command.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Axel Charpentier](https://github.com/axeldotdev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
