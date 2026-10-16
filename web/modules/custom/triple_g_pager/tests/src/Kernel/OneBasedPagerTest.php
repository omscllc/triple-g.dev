<?php

declare(strict_types=1);

namespace Drupal\Tests\triple_g_pager\Kernel;

use Behat\Mink\Driver\BrowserKitDriver;
use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\HttpKernelUiHelperTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the one-based ?page=N pager on front-end listings.
 */
#[Group('triple_g_pager')]
#[RunTestsInSeparateProcesses]
final class OneBasedPagerTest extends KernelTestBase {

  use HttpKernelUiHelperTrait;
  use NodeCreationTrait;
  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field', 'filter', 'node', 'system', 'text', 'user', 'views',
    'triple_g_pager',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['system', 'node', 'filter', 'views']);
    $this->container->get('config.installer')->installDefaultConfig('module', 'node');
    NodeType::create(['type' => 'blog_post', 'name' => 'Blog post'])->save();
    // 60 promoted posts: the front page shows 10 per page, newest first, and
    // admin/content shows 50.
    for ($i = 1; $i <= 60; $i++) {
      $this->createNode([
        'type' => 'blog_post',
        'title' => "Post $i",
        'promote' => 1,
        'created' => 1000 + $i,
      ]);
    }
    $this->setCurrentUser($this->createUser(['access content', 'administer nodes', 'access content overview']));
  }

  /**
   * Returns the front page listing path.
   */
  private function frontPath(): string {
    return Url::fromRoute('view.frontpage.page_1')->toString();
  }

  /**
   * Returns the pager hrefs on the current page.
   *
   * @return string[]
   *   The hrefs.
   */
  private function pagerHrefs(): array {
    $hrefs = [];
    foreach ($this->getSession()->getPage()->findAll('css', '.pager__item a') as $link) {
      $hrefs[] = $link->getAttribute('href');
    }
    return $hrefs;
  }

  /**
   * Page 3 in the URL renders the third page, with one-based links.
   */
  public function testThirdPage(): void {
    $this->drupalGet($this->frontPath(), ['query' => ['page' => 3]]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Post 35');
    $this->assertSession()->pageTextNotContains('Post 45');
    $this->assertSession()->pageTextNotContains('Post 25');
    $hrefs = $this->pagerHrefs();
    $this->assertContains($this->frontPath() . '?page=2', $hrefs);
    $this->assertContains($this->frontPath() . '?page=4', $hrefs);
  }

  /**
   * The first page links have no page parameter.
   */
  public function testFirstPageLinkIsClean(): void {
    $this->drupalGet($this->frontPath(), ['query' => ['page' => 2]]);
    $this->assertSession()->pageTextContains('Post 45');
    $hrefs = $this->pagerHrefs();
    $this->assertContains($this->frontPath(), $hrefs);
    foreach ($hrefs as $href) {
      $this->assertStringNotContainsString('page=0', $href);
      $this->assertStringNotContainsString('page=1', $href);
    }
  }

  /**
   * Old first-page URLs redirect permanently, keeping other parameters.
   */
  public function testLegacyFirstPageRedirect(): void {
    foreach (['0', '1', 'abc', '-4'] as $value) {
      $driver = $this->getSession()->getDriver();
      $this->assertInstanceOf(BrowserKitDriver::class, $driver);
      // @phpstan-ignore missingType.generics
      $driver->getClient()->followRedirects(FALSE);
      $this->drupalGet($this->frontPath(), ['query' => ['page' => $value, 'foo' => 'bar']]);
      $this->assertSession()->statusCodeEquals(301);
      $this->assertSession()->responseHeaderEquals('Location', $this->frontPath() . '?foo=bar');
    }
  }

  /**
   * Out-of-range values render an empty listing, as in core, without errors.
   */
  public function testOutOfRange(): void {
    $this->drupalGet($this->frontPath(), ['query' => ['page' => 999]]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('Post 5');
    $this->assertSession()->pageTextNotContains('Post 60');
  }

  /**
   * Admin listings keep the zero-based pager.
   */
  public function testAdminContentUnchanged(): void {
    $this->drupalGet('admin/content', ['query' => ['page' => 1]]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Post 5');
    $this->assertSession()->pageTextNotContains('Post 60');
  }

}
