<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

class ExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if ($request->getContentTypeFormat() === 'json' || strpos($request->getRequestUri(), '/api/') !== false) {
            $response = new JsonResponse(
                [
                    'error' => $exception->getMessage(),
                    'status' => $this->getStatusCode($exception),
                ],
                $this->getStatusCode($exception)
            );

            $event->setResponse($response);
        }
    }

    private function getStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
    }
}
