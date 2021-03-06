﻿<?php
/*
Twando.com Free PHP Twitter Application
http://www.twando.com/
*/

include('inc/include_top.php');
include('inc/class/class.cron.php');
require "vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;
include('vendor/abraham/twitteroauth/src/TwitterOAuth.php');

$cron = new cronFuncs();

//Defines
set_time_limit(0);
$run_cron = true;

//Check crpn key and if running
if ( ($argv[1] != CRON_KEY) and ($_GET['cron_key'] != CRON_KEY) ) {
 echo mainFuncs::push_response(23);
 $run_cron = false;
} else {
 if ($cron->get_cron_state('tweet') == 1) {
  echo mainFuncs::push_response(24);
  $run_cron = false;
 }
}

if ($run_cron == true) {

 /*
 New to 0.3 - Some people on super cheap hosting seem to get
 SQL errors - output them if they occur
 */
 $db->output_error = 1;

 //Set cron status
 $cron->set_cron_state('tweet',1);

 //Get credentials
 $ap_creds = $db->get_ap_creds();

 //Loop through all accounts
 $q1 = $db->query("SELECT * FROM " . DB_PREFIX . "authed_users ORDER BY (followers_count + friends_count) ASC");
 while ($q1a = $db->fetch_array($q1)) {

  //Defines
  $connection = new TwitterOAuth($ap_creds['consumer_key'], $ap_creds['consumer_secret'], $q1a['oauth_token'], $q1a['oauth_token_secret']);
  $cron->set_user_id($q1a['id']);
  $cron->set_log($q1a['log_data']);

  //Time can vary between PHP and server if server timezone doesn't match PHP timezone.
  //All scripts use PHP time rather than NOW() to avoid issues.
  $current_time = date("Y-m-d H:i:s");

  //Get scheduled tweets for this user older than or equal to current time
  $q2 = $db->query("SELECT * FROM " . DB_PREFIX . "scheduled_tweets WHERE owner_id = '" . $db->prep($q1a['id']) . "' AND time_to_post != '0000-00-00 00:00:00' AND time_to_post <= '" . $db->prep($current_time) . "' ORDER BY time_to_post ASC");
  while ($q2a = $db->fetch_array($q2)) {

	if ($q2a['tweet_image'] == NULL)
	{
   //Post the tweet
  
   $connection->post('statuses/update', array('status' => $q2a['tweet_content'] ));
   } else  {
   //Post the Tweet with image
   $media1 = $connection->upload('media/upload', array('media' => $q2a['tweet_image'],));
	$parameters = array(
    'status' => $q2a['tweet_content'],
    'media_ids' => implode(',', array($media1->media_id_string)),
);
	$connection->post('statuses/update', $parameters);
   }
   

   //Log result - reasons for a non 200 include duplicate tweets, too many tweets
   //posted in a period of time, etc etc.
   if ($connection->getLastHttpCode() == 200) {
    $cron->store_cron_log(2,$cron_txts[18] . $q2a['tweet_content'] . $cron_txts[19],'');
   } else {
    $cron->store_cron_log(2,$cron_txts[18] . $q2a['tweet_content'] . $cron_txts[20],'');
   }

	if ($q2a['everyday'] == "1")
	{
		//Add 24 hours on to time when it should tweet
		$db->query("UPDATE " . DB_PREFIX . "scheduled_tweets SET time_to_post = (time_to_post + INTERVAL 24 HOUR) WHERE owner_id = '" . $db->prep($q1a['id']) . "' AND id = '" . $q2a['id'] . "'");
	} 
	if ($q2a['everyday'] == "2")
	{
		//Add 48 hours on to time when it should tweet
		$db->query("UPDATE " . DB_PREFIX . "scheduled_tweets SET time_to_post = (time_to_post + INTERVAL 48 HOUR) WHERE owner_id = '" . $db->prep($q1a['id']) . "' AND id = '" . $q2a['id'] . "'");
	} 
	if ($q2a['everyday'] == "3")
	{
		//Add 72 hours on to time when it should tweet
		$db->query("UPDATE " . DB_PREFIX . "scheduled_tweets SET time_to_post = (time_to_post + INTERVAL 72 HOUR) WHERE owner_id = '" . $db->prep($q1a['id']) . "' AND id = '" . $q2a['id'] . "'");
	} 
	if ($q2a['everyday'] == "4")
	{
		//Add 1 Week on to time when it should tweet
		$db->query("UPDATE " . DB_PREFIX . "scheduled_tweets SET time_to_post = (time_to_post + INTERVAL 1 WEEK) WHERE owner_id = '" . $db->prep($q1a['id']) . "' AND id = '" . $q2a['id'] . "'");
	} 
	if ($q2a['everyday'] == "5")
	{
		//Add 2 Weeks on to time when it should tweet
		$db->query("UPDATE " . DB_PREFIX . "scheduled_tweets SET time_to_post = (time_to_post + INTERVAL 2 WEEK) WHERE owner_id = '" . $db->prep($q1a['id']) . "' AND id = '" . $q2a['id'] . "'");
	} 
	if ($q2a['everyday'] == "6")
	{
		//Add 3 Weeks on to time when it should tweet
		$db->query("UPDATE " . DB_PREFIX . "scheduled_tweets SET time_to_post = (time_to_post + INTERVAL 3 WEEK) WHERE owner_id = '" . $db->prep($q1a['id']) . "' AND id = '" . $q2a['id'] . "'");
	}
	if ($q2a['everyday'] == "7")
	{
		//Add 1 Month on to time when it should tweet
		$db->query("UPDATE " . DB_PREFIX . "scheduled_tweets SET time_to_post = (time_to_post + INTERVAL 1 MONTH) WHERE owner_id = '" . $db->prep($q1a['id']) . "' AND id = '" . $q2a['id'] . "'");
	}
	
	
	
	
	
	
	
	
	
	if ($q2a['everyday'] == "0")
   //Delete the tweet
   $db->query("DELETE FROM " . DB_PREFIX . "scheduled_tweets WHERE owner_id = '" . $db->prep($q1a['id']) . "' AND id = '" . $q2a['id'] . "'");
	}
  }

 //End of db loop
 }

 //Optimize tweet table
 $db->query("OPTIMIZE TABLE " . DB_PREFIX . "scheduled_tweets");

 //Set cron status
 $cron->set_cron_state('tweet',0);

 echo mainFuncs::push_response(32);

//End of run cron


include('inc/include_bottom.php');
?>
