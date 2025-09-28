<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ExchangeRateService;
use DateTime;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/rates', name: 'api_rates_')]
class ExchangeRateController extends AbstractController
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Get exchange rates for the last 24 hours
     */
    #[Route('/last-24h', name: 'last_24h', methods: ['GET'])]
    public function getLast24Hours(Request $request): JsonResponse
    {
        try {
            $pair = $request->query->get('pair');

            if (!$pair) {
                return $this->createErrorResponse(
                    'Missing required parameter: pair',
                    Response::HTTP_BAD_REQUEST
                );
            }

            $pair = $this->sanitizePair($pair);

            $rates = $this->exchangeRateService->getLast24HoursRates($pair);

            return $this->json([
                'success' => true,
                'data'    => [
                    'pair'   => $pair,
                    'period' => 'last-24h',
                    'rates'  => $rates,
                    'count'  => count($rates),
                ],
                'meta'    => [
                    'generated_at' => (new DateTime())->format('Y-m-d H:i:s'),
                    'timezone'     => date_default_timezone_get(),
                ]
            ]);
        } catch (InvalidArgumentException $e) {
            $this->logger->warning('Invalid pair requested', [
                'pair'  => $request->query->get('pair'),
                'error' => $e->getMessage()
            ]);

            return $this->createErrorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $this->logger->error('Error fetching last 24h rates', [
                'pair'  => $request->query->get('pair'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->createErrorResponse(
                'Internal server error',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get exchange rates for a specific day
     */
    #[Route('/day', name: 'day', methods: ['GET'])]
    public function getDay(Request $request): JsonResponse
    {
        try {
            $pair = $request->query->get('pair');
            $dateString = $request->query->get('date');

            if (!$pair) {
                return $this->createErrorResponse(
                    'Missing required parameter: pair',
                    Response::HTTP_BAD_REQUEST
                );
            }

            if (!$dateString) {
                return $this->createErrorResponse(
                    'Missing required parameter: date (format: YYYY-MM-DD)',
                    Response::HTTP_BAD_REQUEST
                );
            }

            $pair = $this->sanitizePair($pair);

            // Validate and parse date
            $date = $this->parseDate($dateString);
            if (!$date) {
                return $this->createErrorResponse(
                    'Invalid date format. Expected: YYYY-MM-DD',
                    Response::HTTP_BAD_REQUEST
                );
            }

            $rates = $this->exchangeRateService->getDayRates($pair, $date);

            return $this->json([
                'success' => true,
                'data'    => [
                    'pair'  => $pair,
                    'date'  => $date->format('Y-m-d'),
                    'rates' => $rates,
                    'count' => count($rates),
                ],
                'meta'    => [
                    'generated_at' => (new DateTime())->format('Y-m-d H:i:s'),
                    'timezone'     => date_default_timezone_get(),
                ]
            ]);
        } catch (InvalidArgumentException $e) {
            $this->logger->warning('Invalid parameters for day rates', [
                'pair'  => $request->query->get('pair'),
                'date'  => $request->query->get('date'),
                'error' => $e->getMessage()
            ]);

            return $this->createErrorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $this->logger->error('Error fetching day rates', [
                'pair'  => $request->query->get('pair'),
                'date'  => $request->query->get('date'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->createErrorResponse(
                'Internal server error',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Parse date string to DateTime object
     */
    private function parseDate(string $dateString): ?DateTime
    {
        try {
            $date = DateTime::createFromFormat('Y-m-d', $dateString);

            if ($date === false) {
                return null;
            }

            // Validate that the parsed date matches the input string
            if ($date->format('Y-m-d') !== $dateString) {
                return null;
            }

            return $date;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Create standardized error response=
     */
    private function createErrorResponse(string $message, int $statusCode): JsonResponse
    {
        return $this->json([
            'success' => false,
            'error'   => [
                'message' => $message,
                'code'    => $statusCode,
            ],
            'meta'    => [
                'generated_at' => (new DateTime())->format('Y-m-d H:i:s'),
            ]
        ], $statusCode);
    }

    /**
     * Sanitize and validate pair string (e.g., "EUR/BTC")
     */
    private function sanitizePair(string $pair): string
    {
        return strtoupper(trim($pair));
    }
}