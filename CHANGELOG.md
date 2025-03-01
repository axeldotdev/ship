# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2025-03-01

### Added

- WorkOS option to avoid deleting the services.php config file

### Fixed

- Issue on routes publishing when using WorkOS
- Issue with CSP middleware active by default

## [1.0.0] - 2025-02-28

### Added

- React views for api management and sessions management

### Fixed

- Some fix for Vue views

## [0.0.3] - 2025-02-25

### Added

- Livewire views for sessions management
- API management with Sanctum (php artisan install:api)
- Livewire views for API tokens management
- Vue views for sessions management
- Vue views for API tokens management

## [0.0.2] - 2025-02-24

### Changed

- Upgrade dependencies for Laravel 12

## [0.0.1] - 2025-02-24

### Added

- Installation command to select the features you want in your application
- AppServiceProvider configuration
- Env files configuration
- Delete useless confi files
- CSP installation and configuration with the Spatie package
- Larastan installation and configuration
- Rector installation and configuration
- Session Management installation and configuration
- Socialite installation and configuration
- Tenant model installation and configuration
