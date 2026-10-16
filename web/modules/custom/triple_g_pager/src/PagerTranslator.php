<?php

declare(strict_types=1);

namespace Drupal\triple_g_pager;

use Drupal\Core\Routing\AdminContext;
use Symfony\Component\Routing\Route;

/**
 * Converts between one-based URL page values and Drupal's zero-based pager.
 *
 * A "page" value is a comma separated list with one slot per pager element.
 */
final class PagerTranslator {

  /**
   * Front-end listing routes that use the one-based pager.
   *
   * Every other route (admin or otherwise) keeps core's zero-based pager.
   */
  public const ROUTES = [
    'view.frontpage.page_1',
    'view.taxonomy_term.page_1',
    // The taxonomy_term view overrides this core route.
    'entity.taxonomy_term.canonical',
  ];

  public function __construct(
    private readonly AdminContext $adminContext,
  ) {}

  /**
   * Whether the route uses the one-based pager.
   */
  public function appliesTo(?string $route_name, ?Route $route): bool {
    return $route_name !== NULL
      && $route !== NULL
      && \in_array($route_name, self::ROUTES, TRUE)
      && !$route->getOption('_admin_route')
      && !$this->adminContext->isAdminRoute($route);
  }

  /**
   * Converts an incoming one-based value to core's zero-based value.
   *
   * Missing, non-numeric and values below 1 become the first page.
   *
   * @return string
   *   The zero-based value.
   */
  public function toInternal(mixed $raw): string {
    $slots = \is_string($raw) ? \explode(',', $raw) : [];
    return \implode(',', \array_map(static function (string $slot): int {
      $slot = \trim($slot);
      return \ctype_digit($slot) ? \max(0, (int) $slot - 1) : 0;
    }, $slots ?: ['1']));
  }

  /**
   * Whether the incoming value addresses only first pages.
   */
  public function isFirstPage(mixed $raw): bool {
    return \array_filter(\explode(',', $this->toInternal($raw))) === [];
  }

  /**
   * Rewrites a pager link into its one-based form.
   *
   * @param string $href
   *   A link such as "?page=2&foo=bar" built from core's zero-based pager.
   * @param string $current_path
   *   The current path, used when the link is query-only so that the first
   *   page link is the clean URL rather than an empty href.
   */
  public function rewriteHref(string $href, string $current_path = ''): string {
    $fragment = '';
    if (($pos = \strpos($href, '#')) !== FALSE) {
      $fragment = \substr($href, $pos);
      $href = \substr($href, 0, $pos);
    }
    [$path, $query] = \array_pad(\explode('?', $href, 2), 2, '');
    if ($path === '') {
      $path = $current_path;
    }
    \parse_str($query, $params);

    if (isset($params['page']) && \is_string($params['page'])) {
      $slots = \array_map(static fn (string $slot): int => (int) $slot, \explode(',', $params['page']));
      if (\array_filter($slots) === []) {
        unset($params['page']);
      }
      else {
        $params['page'] = \implode(',', \array_map(static fn (int $slot): int => $slot + 1, $slots));
      }
    }

    $query = \http_build_query($params, '', '&', \PHP_QUERY_RFC3986);
    return $path . ($query !== '' ? '?' . $query : '') . $fragment;
  }

}
