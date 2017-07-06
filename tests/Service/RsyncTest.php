<?php

namespace BOI_CI\Service;

use phpDocumentor\Reflection\File;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

define('RSYNC_TEST_TEMP_DIR_BASE', '/tmp/boi-ci-service-rsync-test');

class RsyncTest extends TestCase
{
  private $rsync_destination;

  /**
   * Tests running rsync service.
   */
  public function testSync()
  {
    $fs = new Filesystem();
    // Set up the temp destination directory.
    $this->rsync_destination = RSYNC_TEST_TEMP_DIR_BASE . '/' . uniqid();
    $fs->mkdir($this->rsync_destination);

    // Create a file that should be deleted.
    $fs->touch($this->rsync_destination . '/delete.txt');


    // Instantiate and add parameters to rsync.
    $rsync = new Rsync();
    $rsync->setSource(getcwd());
    $rsync->setDestination($this->rsync_destination);
    $rsync->setFlags('vr');

    // Add file exclusions.
    $rsync->addExclude('/composer.lock');
    $rsync->addExclude('/vendor');
    $rsync->addExclude('/.git');

    // Delete existing files from source.
    $rsync->addOption('--delete');

    // Execute rsync and verify output.
    $output = $rsync->sync();

    $this->assertNotContains('failed', $output);
    return $this->rsync_destination;
  }


  /**
   * Confirm composer.json file has been synced to the destination.
   * @param $destination
   * @depends testSync
   */
  public function testFileExists($destination)
  {
    $this->assertFileExists($destination . '/composer.json');
  }

  /**
   * Confirm composer.lock file has not been synced to the destination.
   * @param $destination
   * @depends testSync
   */
  public function testFileExclude($destination)
  {
    $this->assertFileNotExists($destination . '/composer.lock');
  }

  /**
   * Verify that delete.txt was removed.
   * @param $destination
   * @depends testSync
   */
  public function testFileDelete($destination)
  {
    $this->assertFileNotExists($destination . '/delete.txt');
  }

  /**
   * Get the junk out the trunk.
   * @depends testSync
   */
  public static function tearDownAfterClass()
  {
    parent::tearDownAfterClass();
    // Clean up the temp directory.
    (new Filesystem)->remove(RSYNC_TEST_TEMP_DIR_BASE);
  }
}
