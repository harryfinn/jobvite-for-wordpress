<?php

class JobviteFeed extends JobviteSetup {
  public $job_id,
         $job_feed;

  public function __construct($job_id = null) {
    parent::__construct();

    if(!empty($job_id)) {
      $this->job_id = $job_id;
    }

    $this->job_feed = get_option($this->prefix . 'job_feed');
  }

  public function jfw_job_post($job_id = null) {
    $job_id = !empty($job_id) ? $job_id : $this->job_id;

    return $this->job_feed[$job_id];
  }

  public function jfw_related_jobs($department) {
    $matched_department_jobs = array_column(
      $this->job_feed,
      'department',
      'id'
    );

    unset($matched_department_jobs[$this->job_id]);

    $related_jobs = array_filter(
      $matched_department_jobs, function($val) use ($department) {
        return $val === $department;
      }
    );

    if(empty($related_jobs)) return [];

    $num_of_related_jobs = count($related_jobs);
    $num_of_related_jobs_randomise = $num_of_related_jobs >= 2 ?
      2 :
      $num_of_related_jobs;

    $random_jobs = array_rand($related_jobs, $num_of_related_jobs_randomise);

    return (array)$random_jobs;
  }

  public function jfw_jobs_index_url() {
    $rewrite_options = get_option($this->prefix . 'rewrite_options');
    $current_rewrite_url = !empty($rewrite_options['url']) ?
      $rewrite_options['url'] :
      'jobs';

    return trailingslashit(get_bloginfo('url')) .
           trailingslashit($current_rewrite_url);
  }

  public function jfw_job_url($job_id = null) {
    $job_id = !empty($job_id) ? $job_id : $this->job_id;

    return $this->jfw_jobs_index_url() . $job_id;
  }

  public function jfw_job_departments() {
    return get_option($this->prefix . 'job_feed_departments');
  }
}
