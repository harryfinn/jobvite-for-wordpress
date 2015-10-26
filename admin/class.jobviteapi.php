<?php
class JobviteAPI {
  public $api_url,
         $api_key,
         $api_secret,
         $api_companyId;
  public function __construct($api_key, $api_secret, $api_companyId) {
    $this->api_url = 'https://api.jobvite.com/v1/jobFeed';
    $this->api_key = $api_key;
    $this->api_secret = $api_secret;
    $this->api_companyId = $api_companyId;
  }
  public function get_results($fetch_type = 'External') {
    return $this->map_feed_fields(
      json_decode($this->get_api_response($fetch_type))
    );
  }
  private function build_api_url($type) {
    return $this->api_url .
           '?api=' . $this->api_key .
           '&sc=' . $this->api_secret .
           '&companyId=' . $this->api_companyId .
           '&availableTo=' . $type;
  }
  private function get_api_response($type) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, $this->build_api_url($type));
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
  }
  private function map_feed_fields($feed) {
    $fields_to_map = [
      'title' => 'title',
      'id' => 'id',
      'posted_on' => 'date',
      'apply_url' => 'applyUrl',
      'excerpt' => 'briefDescription',
      'description' => 'description',
      'department' => 'department'
    ];
    foreach($feed->jobs as $job) {
      $job_array = (array) $job;
      $filter_field_keys = array_intersect_key(
        array_flip($fields_to_map),
        $job_array
      );
      $remap_fields = array_intersect_key($job_array, $filter_field_keys);
      ksort($filter_field_keys);
      ksort($remap_fields);
      $job_feed[$job_array['id']] = array_combine($filter_field_keys, $remap_fields);
    }
    return $job_feed;
  }
}
