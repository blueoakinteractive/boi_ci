<?php

namespace BOI_CI\Service;

class Git extends Shell {
  protected $git;
  protected $work_tree;
  protected $git_dir;

  public function __construct($repo_dir) {
    parent::__construct();
    $this->git = trim($this->execute("which git"));

    // Make sure git is installed and available.
    if (empty($this->git)) {
      throw new \Exception('Git not found');
    }

    $this->work_tree = $repo_dir;
    $this->git_dir = $repo_dir . '/.git';
  }

  public function lastCommitMessage() {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir log --format=%B --no-merges -n 1");
  }

  public function gitStatus() {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir status");
  }

  public function gitDiff() {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir diff");
  }

  public function gitAdd($items) {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir add $items");
  }

  public function gitCommit($message) {
    $message = str_replace(["\n", "\t"], "", $message);
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir commit -m \"$message\"");
  }

  public function gitPush($remote, $branch) {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir push $remote $branch");
  }

  public function gitClone($repo, $branch, $dir) {
    return $this->execute("$this->git clone $repo --branch=$branch $this->work_tree");
  }

  public function gitConfig($option, $value) {
    return $this->execute("$this->git --work-tree=$this->work_tree --git-dir=$this->git_dir $option=$value");
  }
}