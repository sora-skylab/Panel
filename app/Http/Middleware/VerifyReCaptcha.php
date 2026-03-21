<?php

namespace Pterodactyl\Http\Middleware;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use GuzzleHttp\Exception\GuzzleException;
use Pterodactyl\Events\Auth\FailedCaptcha;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VerifyReCaptcha
{
    private const PROVIDERS = ['none', 'recaptcha', 'turnstile'];

    /**
     * VerifyReCaptcha constructor.
     */
    public function __construct(private Dispatcher $dispatcher, private Repository $config)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $provider = $this->getProvider();
        if ($provider === 'none') {
            return $next($request);
        }

        $responseField = $this->getResponseField($provider);
        $result = null;

        if ($request->filled($responseField)) {
            try {
                $client = new Client();
                $res = $client->post($this->getVerificationDomain($provider), [
                    'form_params' => [
                        'secret' => $this->getSecretKey($provider),
                        'response' => $request->input($responseField),
                        'remoteip' => $request->ip(),
                    ],
                ]);

                if ($res->getStatusCode() === 200) {
                    $result = json_decode($res->getBody());

                    if ($result->success && (!$this->config->get('recaptcha.verify_domain') || $this->isResponseVerified($result, $request))) {
                        return $next($request);
                    }
                }
            } catch (GuzzleException) {
            }
        }

        $this->dispatcher->dispatch(
            new FailedCaptcha(
                $request->ip(),
                !empty($result) ? ($result->hostname ?? '') : ''
            )
        );

        throw new HttpException(Response::HTTP_BAD_REQUEST, 'Failed to validate captcha data.');
    }

    private function getProvider(): string
    {
        $provider = $this->config->get('recaptcha.provider', 'none');

        return in_array($provider, self::PROVIDERS, true) ? $provider : 'none';
    }

    private function getResponseField(string $provider): string
    {
        return $provider === 'turnstile' ? 'cf-turnstile-response' : 'g-recaptcha-response';
    }

    private function getVerificationDomain(string $provider): string
    {
        return $provider === 'turnstile'
            ? $this->config->get('recaptcha.turnstile_domain')
            : $this->config->get('recaptcha.domain');
    }

    private function getSecretKey(string $provider): string
    {
        return $provider === 'turnstile'
            ? $this->config->get('recaptcha.turnstile_secret_key', '')
            : $this->config->get('recaptcha.secret_key');
    }

    /**
     * Determine if the response from the recaptcha servers was valid.
     */
    private function isResponseVerified(\stdClass $result, Request $request): bool
    {
        if (!$this->config->get('recaptcha.verify_domain')) {
            return false;
        }

        $url = parse_url($request->url());

        return $result->hostname === array_get($url, 'host');
    }
}
