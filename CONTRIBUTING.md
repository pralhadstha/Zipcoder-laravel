# Contributing

Contributions are welcome and appreciated. Here's how to get started.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone git@github.com:your-username/zipcoder-laravel.git`
3. Install dependencies: `composer install`
4. Create a branch: `git checkout -b feature/your-feature`

## Development

### Code Style

This project uses [Laravel Pint](https://laravel.com/docs/pint) for code formatting:

```bash
vendor/bin/pint
```

Check without modifying:

```bash
vendor/bin/pint --test
```

### Running Tests

```bash
vendor/bin/phpunit
```

### Adding a Provider

If you're adding support for a new postal code API, the provider should be implemented in the core [zipcoder-php](https://github.com/pralhadstha/zipcoder-php) package. This package only handles the Laravel integration (service provider, config, facade).

## Pull Request Process

1. Ensure all tests pass
2. Run `vendor/bin/pint` to format your code
3. Update the CHANGELOG.md with your changes
4. Submit a pull request with a clear description of what you changed and why

## Reporting Bugs

Open an issue with:
- A clear description of the problem
- Steps to reproduce
- Expected vs actual behavior
- PHP version, Laravel version, and package version

## Security

If you discover a security vulnerability, please see [SECURITY.md](.github/SECURITY.md) instead of opening a public issue.