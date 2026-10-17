<?php

declare(strict_types=1);

namespace Drupal\triple_g_error_pages\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * Renders the theme’s standalone HTML page for server errors.
 */
final class ErrorPageExceptionSubscriber implements EventSubscriberInterface {

  private const string TEMPLATE = '@triple_g/templates/layout/error-500.html.twig';

  public function __construct(
    private readonly Environment $twig,
    private readonly LoggerChannelInterface $logger,
  ) {}

  /**
   * Replaces Drupal’s plain-text final response for HTML server errors.
   */
  public function onException(ExceptionEvent $event): void {
    $request = $event->getRequest();
    if ($request->getRequestFormat() !== 'html') {
      return;
    }

    $exception = $event->getThrowable();
    if ($exception instanceof HttpExceptionInterface) {
      $status_code = $exception->getStatusCode();
      if ($status_code < 500 || $status_code >= 600) {
        return;
      }
    }

    try {
      $content = $this->twig->render(self::TEMPLATE);
    }
    catch (\Throwable $render_exception) {
      $this->logger->error('Unable to render the themed 500 error page: @message', [
        '@message' => $render_exception->getMessage(),
        'exception' => $render_exception,
      ]);
      return;
    }

    $response = new Response($content, Response::HTTP_INTERNAL_SERVER_ERROR, [
      'Content-Type' => 'text/html; charset=UTF-8',
      'Cache-Control' => 'no-store',
    ]);
    $event->setResponse($response);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::EXCEPTION => ['onException', -255],
    ];
  }

}
