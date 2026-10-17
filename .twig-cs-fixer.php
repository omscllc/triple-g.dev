<?php

declare(strict_types=1);

use TwigCsFixer\Config\Config;
use TwigCsFixer\File\Finder;
use TwigCsFixer\Ruleset\Ruleset;

$finder = new Finder();
$finder->in([__DIR__ . '/web/themes/custom/triple_g']);

$config = new Config();
$config->setFinder($finder);

return $config;
