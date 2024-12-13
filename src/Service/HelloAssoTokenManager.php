<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HelloAssoTokenManager
{
    private const TOKEN_CACHE_KEY = 'helloAssoApiToken';
    private const REFRESH_TOKEN_CACHE_KEY = 'helloAssoApiRefreshToken';

    private string $refreshTokenUrl;

    public function __construct(
        private HttpClientInterface                     $httpClient,
        private CacheItemPoolInterface                  $cache,
        private DonneeClubManager                       $clubManager,
        #[Autowire('%helloAssoApiUrl%')] private string $apiBaseUrl,
    )
    {
        $this->refreshTokenUrl = $this->apiBaseUrl . '/oauth2/token';
    }

    private function getTokenCacheKey(): string
    {
        return self::TOKEN_CACHE_KEY . '_' . $this->clubManager->getDonneeClub()->getHelloAssoClientId();
    }

    private function getRefreshTokenCacheKey(): string
    {
        return self::REFRESH_TOKEN_CACHE_KEY . '_' . $this->clubManager->getDonneeClub()->getHelloAssoClientId();
    }


    public function getToken(): string
    {
        $token = $this->cache->getItem($this->getTokenCacheKey());

        if (!$token->isHit()) {
            $this->refreshToken();
            $token = $this->cache->getItem($this->getTokenCacheKey());
        }

        return $token->get();
    }

    private function initializeToken(): void
    {
        $response = $this->httpClient->request('POST', $this->refreshTokenUrl, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'client_id' => $this->clubManager->getDonneeClub()->getHelloAssoClientId(),
                'client_secret' => $this->clubManager->getDonneeClub()->getHelloAssoClientSecret(),
                'grant_type' => 'client_credentials',
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de l\'initialisation du token d\'authentification');
        }

        $data = $response->toArray();
        $this->storeToken($data['access_token'], $data['refresh_token'], $data['expires_in']);
    }


    private function refreshToken(): void
    {
        $refreshToken = $this->cache->getItem($this->getRefreshTokenCacheKey());
        if (!$refreshToken->isHit()) {
            // Si aucun refresh token n'est présent, réinitialiser le token
            $this->initializeToken();
            return;
        }

        $response = $this->httpClient->request('POST', $this->refreshTokenUrl, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken->get(),
                'client_id' => $this->clubManager->getDonneeClub()->getHelloAssoClientId(),
                'client_secret' => $this->clubManager->getDonneeClub()->getHelloAssoClientSecret(),
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            $data = $response->toArray();
            $this->storeToken($data['access_token'], $data['refresh_token'], $data['expires_in']);
        } else {
            throw new \Exception('Unable to refresh token');
        }
    }

    private function storeToken(string $accessToken, string $refreshToken, int $expiresIn): void
    {
        $tokenItem = $this->cache->getItem($this->getTokenCacheKey());
        $tokenItem->set($accessToken);
        $tokenItem->expiresAfter($expiresIn - 60); // Buffer of 1 minute
        $this->cache->save($tokenItem);

        $refreshTokenItem = $this->cache->getItem($this->getRefreshTokenCacheKey());
        $refreshTokenItem->set($refreshToken);
        $this->cache->save($refreshTokenItem);
    }
}
