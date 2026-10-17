<?php

declare(strict_types=1);

namespace Drupal\triple_g_pager\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\triple_g_pager\PagerTranslator;

/**
 * Rewrites pager links to one-based URLs.
 */
final class PagerHooks {

  public function __construct(
    private readonly PagerTranslator $translator,
    private readonly RouteMatchInterface $routeMatch,
  ) {}

  /**
   * Implements hook_preprocess_pager().
   *
   * @param array<string, mixed> $variables
   *   The pager template variables.
   */
  #[Hook('preprocess_pager')]
  public function preprocessPager(array &$variables): void {
    if (empty($variables['items']) || !$this->translator->appliesTo($this->routeMatch->getRouteName(), $this->routeMatch->getRouteObject())) {
      return;
    }
    // Generated from the route so path aliases (pathauto) are applied.
    $path = Url::fromRouteMatch($this->routeMatch)->toString();
    foreach (['first', 'previous', 'next', 'last'] as $key) {
      if (isset($variables['items'][$key]['href'])) {
        $variables['items'][$key]['href'] = $this->translator->rewriteHref($variables['items'][$key]['href'], $path);
      }
    }
    foreach ($variables['items']['pages'] ?? [] as $page => $item) {
      $variables['items']['pages'][$page]['href'] = $this->translator->rewriteHref($item['href'], $path);
    }
    $variables['#cache']['contexts'][] = 'url.query_args:page';
    $variables['#cache']['contexts'][] = 'route';
  }

}
