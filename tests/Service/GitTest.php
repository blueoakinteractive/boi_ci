<?php

namespace BOI_CI\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

define('GIT_TEST_TEMP_DIR_BASE', '/tmp/boi-ci-service-git-test');

class GitTest extends TestCase
{

  /**
   * Setup process for tests.
   */

  /**
   * Test git clone method.
   * @return string
   */
  public function testGitClone()
  {
    $repo = GIT_TEST_TEMP_DIR_BASE . '/' . uniqid();
    $git = new Git($repo);
    $git->gitClone('git@gitlab.com:blueoakinteractive/boi_ci_tests.git', 'master', $repo);
    $this->assertFileExists($repo . '/README.md');
    return $repo;
  }

  /**
   * Test adding a file to the repo to be committed.
   * @param $repo string
   * @depends testGitClone
   * @return string.
   */
  public function testGitAdd($repo)
  {
    $git = new Git($repo);
    $fs = new Filesystem();
    $filename = uniqid() . '.txt';
    $fs->touch($repo . '/' . $filename);
    $git->gitAdd($filename);
    $status = $git->gitStatus();
    $this->assertContains('new file:   ' . $filename, $status);
    return $repo;
  }

  /**
   * Test committing the new file to the repo.
   * @param $repo string
   * @depends testGitAdd
   * @return string
   */
  public function testGitCommit($repo)
  {
    $git = new Git($repo);

    // Set config required for committing.
    $git->gitConfig('user.email', 'boi_ci_test@internal.blueoi.com');
    $git->gitConfig('user.name', 'BOI CI Test Bot');

    $commit_message = 'Testing commit of ' . $repo;
    $git->gitCommit($commit_message);
    $status = $git->gitStatus();
    $this->assertContains('nothing to commit', $status);
    $last_commit = $git->lastCommitMessage();
    $this->assertContains($commit_message, $last_commit);
    return $repo;
  }

  /**
   * Test pushing the commit to the repo.
   * @param $repo string
   * @depends testGitCommit
   * @return string
   */
  public function testGitPush($repo)
  {
    $git = new Git($repo);
    $git->gitPush('origin', 'master');
    $git->gitFetch('origin', 'master');
    $status = $git->gitStatus();
    $this->assertContains("Your branch is up-to-date with 'origin/master'", $status);
    return $repo;
  }
}
