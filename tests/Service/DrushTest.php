<?php

namespace BOI_CI\Service;

use PHPUnit\Framework\TestCase;

define('DRUSH_TEST_TEMP_DIR_BASE', '/tmp/boi-ci-service-drush-test');

class DrushTest extends TestCase
{
  public function testDrush()
  {
    $drush = new Drush(DRUSH_TEST_TEMP_DIR_BASE);
    $output = $drush->drush('status');
    $this->assertStringContainsStringIgnoringCase('Drush version', $output);
    return $drush;
  }
}
