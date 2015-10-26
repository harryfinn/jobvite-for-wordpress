<?php get_header(); ?>

<?php
$jobvite = new JobviteFeed(get_query_var('jobvite_id'));
$job_data = $jobvite->feed();
?>

<?php get_footer(); ?>
