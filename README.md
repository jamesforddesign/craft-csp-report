# CSP Report

Sets a Content-Security-Policy-Report-Only header, creates an action to receive CSP violation reports, and logs them to a file.

## Requirements

This plugin requires Craft CMS 5.9.0 or later, and PHP 8.3 or later.

## How to install

1. Add the following path to the composer.json repositories:

```
"repositories": [
    {
        "type": "path",
        "url": "plugins/csp-report"
    }
]
```

2. Install the composer package:

```
composer require jfd/craft-csp-report
```

3. Install the plugin:

```
php craft plugin/install _csp-report
```

## How to update this package

After making your changes, tag the release:

```
git tag 1.0.1
git push --tags
```