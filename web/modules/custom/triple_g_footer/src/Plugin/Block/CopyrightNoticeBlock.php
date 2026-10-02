<?php

declare(strict_types=1);

namespace Drupal\triple_g_footer\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an editable copyright notice with an automatically updated year.
 */
#[Block(
  id: 'triple_g_copyright_notice',
  admin_label: new TranslatableMarkup('Copyright notice'),
  category: new TranslatableMarkup('Triple G'),
)]
final class CopyrightNoticeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a copyright notice block.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Utility\Token $token
   *   The token replacement service.
   */
  public function __construct(array $configuration, string $plugin_id, mixed $plugin_definition, protected Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('token'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return parent::defaultConfiguration() + [
      'copyright_text' => "\u{00A9} [current-date:custom:Y] Geek's Gadgets and Gizmos",
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form = parent::blockForm($form, $form_state);
    $form['copyright_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Copyright text'),
      '#description' => $this->t('Edit the whole notice. The [current-date:custom:Y] token updates automatically.'),
      '#default_value' => $this->configuration['copyright_text'],
      '#rows' => 2,
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    parent::blockSubmit($form, $form_state);
    $this->configuration['copyright_text'] = \trim((string) $form_state->getValue('copyright_text'));
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $now = new \DateTimeImmutable();
    $year_boundary = $now->modify('first day of January next year')->setTime(0, 0);
    $max_age = \max(1, $year_boundary->getTimestamp() - $now->getTimestamp());
    $metadata = new BubbleableMetadata();
    $copyright_text = $this->token->replace($this->configuration['copyright_text'], [], [], $metadata);

    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['site-shell__copyright']],
      'text' => ['#plain_text' => $copyright_text],
    ];
    $metadata->setCacheMaxAge(Cache::mergeMaxAges($metadata->getCacheMaxAge(), $max_age));
    $metadata->applyTo($build);
    return $build;
  }

}
