# CSP Report

Sets a Content-Security-Policy-Report-Only header, creates an action to receive CSP violation reports, and logs them to a file.

## Requirements

This plugin requires Craft CMS 5.9.0 or later, and PHP 8.3 or later.

## How to install

1. Install the composer package:

```
composer require jfd/craft-csp-report
```

2. Install the plugin:

```bash
php craft plugin/install _csp-report
```

## How to use

Once installed, the plugin will add a `Content-Security-Policy-Report-Only` header to all pages.

Violations will be reported to `/actions/_csp-report/report/log`, which will log the violation to `storage/csp-report.json`.

The contents of this file can be viewed at `/actions/_csp-report/report/get`.

## How to update this package

After making your changes, tag the release:

```bash
git tag 1.0.1 # increment the release version as required
git push --tags
```