<?php

declare(strict_types=1);

namespace Drupal\triple_g_error_pages\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/**
 * Provides local previews of the themed error pages.
 */
final class ErrorPagePreviewController implements ContainerInjectionInterface {

  private const string PREVIEW_SETTING = 'triple_g_error_pages_preview';

  public function __construct(
    private readonly Environment $twig,
  ) {}

  /**
   * Creates the controller from the service container.
   */
  public static function create(ContainerInterface $container): self {
    return new self($container->get('twig'));
  }

  /**
   * Previews the themed 403 page through Drupal's real 403 handling.
   *
   * Throwing here makes core render the production 403 page, including blocks,
   * theme assets and the HTTP 403 status.
   */
  #[Route(
    path: '/dev/error-preview/403',
    name: 'triple_g_error_pages.preview_403',
    requirements: [
      '_custom_access' => '\Drupal\triple_g_error_pages\Controller\ErrorPagePreviewController::previewAccess',
    ],
    options: ['_admin_route' => FALSE],
  )]
  public function preview403(): never {
    throw new AccessDeniedHttpException('Error page preview.');
  }

  /**
   * Previews the themed 404 page through Drupal's real 404 handling.
   *
   * Throwing here makes core render the production 404 page and HTTP status.
   */
  #[Route(
    path: '/dev/error-preview/404',
    name: 'triple_g_error_pages.preview_404',
    requirements: [
      '_custom_access' => '\Drupal\triple_g_error_pages\Controller\ErrorPagePreviewController::previewAccess',
    ],
    options: ['_admin_route' => FALSE],
  )]
  public function preview404(): never {
    throw new NotFoundHttpException('Error page preview.');
  }

  /**
   * Previews the standalone themed 500 page.
   */
  #[Route(
    path: '/dev/error-preview/500',
    name: 'triple_g_error_pages.preview_500',
    requirements: [
      '_custom_access' => '\Drupal\triple_g_error_pages\Controller\ErrorPagePreviewController::previewAccess',
    ],
    options: ['_admin_route' => FALSE],
  )]
  public function preview500(): Response {
    $content = $this->twig->render('@triple_g/templates/layout/error-500.html.twig');

    return $this->createPreviewResponse($content);
  }

  /**
   * Checks whether local error-page previews are enabled.
   */
  public static function previewAccess(): AccessResult {
    return AccessResult::allowedIf(Settings::get(self::PREVIEW_SETTING) === TRUE)
      ->setCacheMaxAge(0);
  }

  /**
   * Creates an uncached, non-indexable preview response.
   */
  private function createPreviewResponse(string $content): Response {
    return new Response($content, Response::HTTP_OK, [
      'Cache-Control' => 'no-store, private',
      'X-Robots-Tag' => 'noindex',
    ]);
  }

}
