<?php

namespace jfd\craftcspreport;

use Craft;
use craft\helpers\UrlHelper;
use craft\base\Plugin as BasePlugin;

/**
 * CSP Report plugin
 *
 * @method static Plugin getInstance()
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->attachEventHandlers();

        if (Craft::$app->getRequest()->getIsConsoleRequest() === false) {
            Craft::$app->getResponse()->getHeaders()->set(
                'Content-Security-Policy-Report-Only',
                "default-src 'self'; report-uri " . UrlHelper::actionUrl('_csp-report/report/log'));
        }

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function() {
            // ...
        });
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/5.x/extend/events.html to get started)
    }
}
