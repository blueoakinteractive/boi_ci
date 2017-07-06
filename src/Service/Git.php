<?php

namespace BOI_CI\Service;

class Git extends Shell
{
  protected $git;
  protected $work_tree;
  protected $git_dir;

  public function __construct($repo_dir)
  {
    parent::__construct();
    $this->git = trim($this->execute("which git"));

    // Make sure git is installed and available.
    if (empty($this->git)) {
      throw new \Exception('Git not found');
    }

    $this->work_tree = $repo_dir;
    $this->git_dir = $repo_dir . '/.git';
  }

  /**
   * Returns the last git commit message.
   * @return string
   */
  public function lastCommitMessage()
  {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir log --format=%B --no-merges -n 1");
  }

  /**
   * Returns the git repository status.
   * @return string
   */
  public function gitStatus()
  {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir status");
  }

  /**
   * Returns a git dif.
   * @return string
   */
  public function gitDiff()
  {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir diff");
  }

  /**
   * Adds items to the git repo.
   * @param $items
   * @return string
   */
  public function gitAdd($items)
  {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir add $items");
  }

  /**
   * Commits the staged changes.
   * @param $message
   * @return string
   */
  public function gitCommit($message)
  {
    $message = str_replace(["\n", "\t"], "", $message);
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir commit -m \"$message\"");
  }

  /**
   * Pushes to a git repository.
   * @param $remote
   * @param $branch
   * @return string
   */
  public function gitPush($remote, $branch)
  {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir push $remote $branch");
  }

  /**
   * Clones a git repository.
   * @param $repo
   * @param $branch
   * @param $dir
   * @return string
   */
  public function gitClone($repo, $branch, $dir)
  {
    return $this->execute("$this->git clone $repo --branch=$branch $this->work_tree");
  }

  /**
   * Fetches from a git repository.
   * @param $remote
   * @param $branch
   * @return string
   */
  public function gitFetch($remote, $branch)
  {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir fetch $remote $branch");
  }

  /**
   * Sets a config parameter.
   * @param $option
   * @param $value
   * @return string
   */
  public function gitConfig($option, $value)
  {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir config $option \"$value\"");
  }
}