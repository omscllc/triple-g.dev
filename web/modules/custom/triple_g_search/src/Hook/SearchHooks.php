<?php

declare(strict_types=1);

namespace Drupal\triple_g_search\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\views\ViewExecutable;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Maps the public "q" query parameter onto the search View's "keys" filter.
 *
 * Views reserves "q" and strips it from exposed input, so the View keeps its
 * internal "keys" identifier while the public URL uses "q".
 */
class SearchHooks {

  /**
   * The search View ID.
   */
  protected const string VIEW_ID = 'search';

  public function __construct(
    protected RequestStack $requestStack,
  ) {}

  /**
   * Feeds ?q= into the exposed input of the search View.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   The view being executed.
   * @param string $display_id
   *   The display ID.
   * @param array<int, mixed> $args
   *   The view arguments.
   */
  #[Hook('views_pre_view')]
  public function preView(ViewExecutable $view, string $display_id, array &$args): void {
    if ($view->id() !== self::VIEW_ID) {
      return;
    }
    $q = $this->requestStack->getCurrentRequest()?->query->all()['q'] ?? NULL;
    if (!\is_string($q)) {
      return;
    }
    $input = $view->getExposedInput();
    $input['keys'] = $q;
    $view->setExposedInput($input);
  }

  /**
   * Renders the exposed text field with the public name "q".
   *
   * @param array<string, mixed> $form
   *   The form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  #[Hook('form_views_exposed_form_alter')]
  public function exposedFormAlter(array &$form, FormStateInterface $form_state): void {
    $view = $form_state->get('view');
    if (!$view instanceof ViewExecutable || $view->id() !== self::VIEW_ID || !isset($form['keys'])) {
      return;
    }
    $form['keys']['#name'] = 'q';
  }

}
