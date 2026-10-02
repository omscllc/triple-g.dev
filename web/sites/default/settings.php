<?php
declare(strict_types=1);

/** @var string $app_root */
/** @var string $site_path */

use Symfony\Component\Dotenv\Dotenv;

/** @disregard P1011 */
$env_file = DRUPAL_ROOT . '/../.env';
if (file_exists($env_file)) {
  $dotenv = new Dotenv();
  $dotenv->load($env_file);
}

ini_set('memory_limit', '512M');

$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';

$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

$settings['entity_update_batch_size'] = 50;

$settings['entity_update_backup'] = TRUE;

$settings['migrate_node_migrate_type_classic'] = FALSE;

$settings['config_sync_directory'] = '../config/sync';

// Set the public file path to 'assets/'.
$settings['file_public_path'] = 'assets';

$databases['default']['default'] = [
  'database' => $_ENV['DB_NAME'],
  'username' => $_ENV['DB_USER'],
  'password' => $_ENV['DB_PASSWORD'],
  'prefix' => '',
  'host' => $_ENV['DB_HOST'],
  'port' => $_ENV['DB_PORT'],
  'isolation_level' => 'READ COMMITTED',
  'driver' => 'mysql',
  'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
  'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
];


$settings['hash_salt'] = $_ENV['HASH_SALT'];

if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
