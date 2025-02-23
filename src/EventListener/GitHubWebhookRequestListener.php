<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Authenticator\SignatureAuthenticator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final readonly class GitHubWebhookRequestListener
{
    public function __construct(private SignatureAuthenticator $authenticator)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        if ($route !== 'github_webhook') {
            return;
        }

        if (
            !$this->authenticator->isAuthenticated(
                $request->getContent(),
                (string)$request->headers->get('X-Hub-Signature-256')
            )
        ) {
            $event->setResponse(new Response('Unauthenticated', 401));
        }
    }
}
