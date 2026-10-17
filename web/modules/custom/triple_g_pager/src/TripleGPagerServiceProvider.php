<?php

declare(strict_types=1);

namespace Drupal\triple_g_pager;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Enables #[AsEventListener] discovery, which Drupal core does not register.
 */
final class TripleGPagerServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    $container->registerAttributeForAutoconfiguration(
      AsEventListener::class,
      static function (ChildDefinition $definition, AsEventListener $attribute, \Reflector $reflector): void {
        $tag = \array_filter(\get_object_vars($attribute), static fn (mixed $value): bool => $value !== NULL);
        if ($reflector instanceof \ReflectionMethod) {
          $tag['method'] = $reflector->getName();
        }
        $definition->addTag('kernel.event_listener', $tag);
      },
    );
  }

}
