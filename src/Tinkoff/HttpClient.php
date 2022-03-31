<?php

namespace App\Tinkoff;

use App\Tinkoff\DTO\Cluster;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClient
{
    private HttpClientInterface $client;
    private LoggerInterface     $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->client = SymfonyHttpClient::create();
        $this->logger = $logger;
    }

    /**
     * @return Cluster[]
     */
    public function getClusters(
        float $bottomLeftLatitude,
        float $bottomLeftLongitude,
        float $topRightLatitude,
        float $topRightLongitude
    ): array
    {
        for ($attempt = 1; $attempt <= 10; $attempt++) {
            try {
                $response = $this->client->request('POST', 'https://api.tinkoff.ru/geo/withdraw/clusters',
                    [
                        'body'    => json_encode([
                            "bounds"  => [
                                "bottomLeft" => ['lat' => $bottomLeftLatitude, 'lng' => $bottomLeftLongitude],
                                "topRight"   => ['lat' => $topRightLatitude, 'lng' => $topRightLongitude]
                            ],
                            "filters" => [
                                "banks"           => ["tcs"],
                                "showUnavailable" => true,
                                "currencies"      => ["RUB"]
                            ],
                            "zoom"    => 12
                        ]),
                        'headers' => [
                            'Content-Type'    => 'application/json',
                            'Pragma'          => 'no-cache',
                            'Accept'          => '*/*',
                            'Accept-Language' => 'en-us',
                            'Cache-Control'   => 'no-cache',
                            'Host'            => 'api.tinkoff.ru',
                            'Origin'          => 'https://www.tinkoff.ru',
                            'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.3 Safari/605.1.15',
                            'Connection'      => 'keep-alive',
                            'Referer'         => 'https://www.tinkoff.ru/'
                        ]
                    ]
                );

                $data = $response->toArray();

                if ($data['status'] !== 'Ok') {
                    $this->logger->debug('Invalid response status', ['response' => $data, 'attempt' => $attempt]);
                    if ($attempt === 10) {
                        throw new \RuntimeException('Invalid response status');
                    }

                    sleep($attempt);

                    continue;
                }

                if (!isset($data['payload'])) {
                    $this->logger->warning('Response has invalid format', ['response' => $data, 'attempt' => $attempt]);

                    return [];
                }

                if (!isset($data['payload']['clusters'])) {
                    $this->logger->warning('Response has invalid format', ['response' => $data, 'attempt' => $attempt]);

                    return [];
                }

                if (!is_array($data['payload']['clusters'])) {
                    $this->logger->warning('Response has invalid format', ['response' => $data, 'attempt' => $attempt]);

                    return [];
                }

                $clusters = [];
                foreach ($data['payload']['clusters'] as $clusterData) {
                    $clusters[] = new Cluster($clusterData);
                }

                return $clusters;
            } catch (\Exception $exception) {
                $this->logger->error('Request error', ['exception' => $exception, 'attempt' => $attempt]);
                if ($attempt === 10) {
                    throw $exception;
                }
                sleep($attempt);
            }
        }

        return [];
    }
}
