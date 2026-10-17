<?php

declare(strict_types=1);

namespace Drupal\triple_g_pager\EventSubscriber;

use Drupal\Core\Routing\RouteObjectInterface;
use Drupal\triple_g_pager\PagerTranslator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Route;

/**
 * Translates one-based ?page=N into core's zero-based value.
 *
 * Old first-page URLs (?page=0 and ?page=1) get a 301 to the clean URL. Old
 * zero-based links to later pages are not redirected: ?page=2 now simply means
 * the second page.
 */
final class PagerRequestSubscriber {

  public function __construct(
    private readonly PagerTranslator $translator,
  ) {}

  /**
   * Runs after routing (priority 32) and before Views reads the pager.
   */
  #[AsEventListener(event: KernelEvents::REQUEST, priority: 30)]
  public function onRequest(RequestEvent $event): void {
    $request = $event->getRequest();
    if (!$event->isMainRequest() || !$request->query->has('page')) {
      return;
    }
    $route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT);
    $name = $request->attributes->get(RouteObjectInterface::ROUTE_NAME);
    if (!$this->translator->appliesTo(\is_string($name) ? $name : NULL, $route instanceof Route ? $route : NULL)) {
      return;
    }

    $raw = $request->query->all()['page'];
    if ($request->isMethodCacheable() && $this->translator->isFirstPage($raw)) {
      $params = $request->query->all();
      unset($params['page']);
      $query = \http_build_query($params, '', '&', \PHP_QUERY_RFC3986);
      $event->setResponse(new RedirectResponse(
        $request->getBaseUrl() . $request->getPathInfo() . ($query !== '' ? '?' . $query : ''),
        Response::HTTP_MOVED_PERMANENTLY,
      ));
      return;
    }

    $request->query->set('page', $this->translator->toInternal($raw));
  }

}
