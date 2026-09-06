# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/jeffersongoncalves/laravel-demio/commits/master/compare/1.0.0...master)

### Added

- Initial release.
- `DemioClient` REST wrapper for `ping()`, `events()`, `event()`, `eventDate()`, `register()`, and `participants()`.
- `DemioAuthenticationException` thrown on a 401 response.
- Configurable `api_key`, `api_secret`, `base_url`, and `timeout` via `config/demio.php`.

## [1.0.0](https://github.com/jeffersongoncalves/laravel-demio/commits/master/compare/master...1.0.0) - 2026-09-06

### Added

- Initial release.
- `DemioClient` REST wrapper for `ping()`, `events()`, `event()`, `eventDate()`, `register()`, and `participants()`.
- `DemioAuthenticationException` thrown on a 401 response.
- Configurable `api_key`, `api_secret`, `base_url`, and `timeout` via `config/demio.php`.
