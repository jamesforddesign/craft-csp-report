<?php

namespace jfd\craftcspreport\controllers;

use Craft;
use craft\web\Controller;
use jfd\craftcspreport\helpers\CspSource;
use jfd\craftcspreport\services\ReportService;
use yii\web\Response;

/**
 * Report controller
 *
 * POST /actions/csp/report/log — receive a CSP violation report and update the summary file
 * GET  /actions/csp/report/get — return the current summary
 */
class ReportController extends Controller
{
    public $defaultAction = 'log';
    protected array|int|bool $allowAnonymous = ['log', 'get'];
    public $enableCsrfValidation = false;

    /**
     * GET /actions/csp/report/get — return the current summary.
     */
    public function actionGet(): Response
    {
        // Get the report service
        $service = new ReportService();

        // Return the summary as JSON
        return $this->asJson($service->read());
    }

    /**
     * POST /actions/csp/report/log — record a CSP violation report.
     */
    public function actionLog(): Response
    {
        // Ensure this is a POST request
        $this->requirePostRequest();

        // Parse the JSON payload
        $payload = json_decode(Craft::$app->getRequest()->getRawBody(), true);

        // If the payload is missing or invalid, just return a 204 No Content (don't throw an error)
        if (!is_array($payload)) {
            return $this->response->setStatusCode(204);
        }

        // The actual report may be the entire payload or under a "csp-report" key, depending on the browser
        $report = $payload['csp-report'] ?? $payload;

        // Extract the relevant fields, with fallbacks
        $directive = $report['effective-directive']
            ?? $report['violated-directive']
            ?? 'unknown';

        $blockedUri = $report['blocked-uri'] ?? 'unknown';

        // Normalize the blocked URI into a source value (e.g. "https://example.com" → "example.com", "data:" → "data:", etc.)
        $source = CspSource::normalize($blockedUri);

        // Store an example URI if available
        $documentUri = $report['document-uri'] ?? '';

        // Atomically update the summary file under an exclusive lock
        $service = new ReportService();
        $service->record($directive, $source, $blockedUri, $documentUri);

        // Return a 204 No Content response to acknowledge receipt (don't return any JSON)
        return $this->response->setStatusCode(204);
    }
}
