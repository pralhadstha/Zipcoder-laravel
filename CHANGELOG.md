# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-04-05

### Added

- Laravel service provider with auto-discovery
- Zipcoder facade for convenient postal code lookups
- Publishable configuration file
- Support for 5 built-in providers: GeoNames, Zippopotamus, Zipcodestack, Zipcodebase, JpPostalCode
- Chain of Responsibility pattern with automatic provider fallback
- PSR-16 cache integration with configurable TTL and store
- Custom provider registration via config
- Support for Laravel 10, 11, and 12
- Feature tests for service provider and facade