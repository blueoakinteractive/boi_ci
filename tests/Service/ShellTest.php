<?php

namespace BOI_CI\Service;

use PHPUnit\Framework\TestCase;

class ShellTest extends TestCase
{
  /**
   * Tests that the environment variables are set when instantiated.
   */
  public function testEnvPathSet()
  {
    $shell = new Shell();
    $this->assertNotEmpty($shell->getEnv());
  }

  /**
   * Tests that the global composer vendor bin is added to PATH.
   */
  public function testEnvPathGlobalComposerBin()
  {
    $shell = new Shell();
    $env = $shell->getEnv();
    $this->assertContains(getenv('HOME') . '/.composer/vendor/bin', $env['PATH']);
  }

  /**
   * Tests that output of execute() returns composer.json string.
   */
  public function testExecute()
  {
    $shell = new Shell();
    $output = $shell->execute('ls');
    $this->assertContains('composer.json', $output);
  }
}
