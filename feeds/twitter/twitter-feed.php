<?php
add_action('wp_enqueue_scripts', 'fts_twitter_head');
function  fts_twitter_head() {
    wp_enqueue_style( 'fts_twitter_css', plugins_url( 'twitter/css/styles.css',  dirname(__FILE__) ) ); 
}
add_shortcode( 'fts twitter', 'fts_twitter_func' );
//Main Funtion
function fts_twitter_func($atts){
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if(is_plugin_active('feed-them-premium/feed-them-premium.php')) {
   include('wp-content/plugins/feed-them-premium/feeds/twitter/twitter-feed.php');
}
else 	{
	extract( shortcode_atts( array(
		'twitter_name' => '',
		'fts_rotate_feed' => 'no',
		'fts_rotate_poh' =>'true',
		'fts_rotate_speed' =>'200',
		'fts_rotate_fx' =>'fade',
		'fts_rotate_random' => 'no'
		
	), $atts ) );
	$tweets_count ='5';
}
ob_start();  

$type = 'twitter';
$numTweets      = $tweets_count;
$name           = $twitter_name;  
$excludeReplies = true;            
$transName      = 'list-of-tweets-'.$twitter_name;
$cacheTime      = 15;               

$backupName = $transName . '-backup';
 
// Do we already have saved tweet data? If not, lets get it.
if(false === ($tweets = get_transient($transName) ) ) :	
 
  // Get the tweets from Twitter.
  include 'twitteroauth/twitteroauth.php';
  
  $connection = new TwitterFTSAuth(
    'dOIIcGrhWgooKquMWWXg',
    'qzAE4t4xXbsDyGIcJxabUz3n6fgqWlg8N02B6zM',
    '1184502104-Cjef1xpCPwPobP5X8bvgOTbwblsmeGGsmkBzwdB',  
    'd789TWA8uwwfBDjkU0iJNPDz1UenRPTeJXbmZZ4xjY'
  );
  
  // If excluding replies, we need to fetch more than requested as the
  // total is fetched first, and then replies removed.
  $totalToFetch = ($excludeReplies) ? max(50, $numTweets * 3) : $numTweets;
  
  $fetchedTweets = $connection->get(
    'statuses/user_timeline',
    array(
      'screen_name'     => $name,
      'count'           => $totalToFetch,
      'exclude_replies' => $excludeReplies
    )
  );
  
  // Did the fetch fail?
  if($connection->http_code != 200) :
    $tweets = get_option($backupName); // False if there has never been data saved.
    
  else :
    // Fetch succeeded.
    // Now update the array to store just what we need.
    // (Done here instead of PHP doing this for every page load)
    $limitToDisplay = min($numTweets, count($fetchedTweets));
    
    for($i = 0; $i < $limitToDisplay; $i++) :
      $tweet = $fetchedTweets[$i];
    
      // Core info.
      $name = $tweet->user->name;
	  $screen_name = $tweet->user->screen_name;
      $permalink = 'http://twitter.com/'. $screen_name .'/status/'. $tweet->id_str;
	  $user_permalink = 'https://twitter.com/#!/'. $screen_name;
 
      /* Alternative image sizes method: http://dev.twitter.com/doc/get/users/profile_image/:screen_name */
      $image = $tweet->user->profile_image_url;
 
      // Message. Convert links to real links.
      $pattern = array('/http:(\S)+/', '/https:(\S)+/', '/([^a-zA-Z0-9-_&])@([0-9a-zA-Z_]+)/', '/([^a-zA-Z0-9-_&])#([0-9a-zA-Z_]+)/');
      $replace = array('<a href="${0}" target="_blank" rel="nofollow">${0}</a>', '<a href="${0}" target="_blank" rel="nofollow">${0}</a>', '<a href="http://twitter.com/$2" target="_blank" rel="nofollow">@$2</a>', '<a href="http://twitter.com/search?q=%23$2&src=hash" target="_blank" rel="nofollow">#$2</a>');
      $text = preg_replace($pattern, $replace, $tweet->text);
 
      // Need to get time in Unix format.
	  $times = $tweet->created_at;
      $uTime = date('F j, Y, \a\t g:i a',strtotime($times));
	  $twitter_id = $tweet->id_str;
 
      // Now make the new array.
      $tweets[] = array(
              'text' => $text,
              'name' => $name,
			  'user_permalink' => $user_permalink,
              'permalink' => $permalink,
              'image' => $image,
              'time' => $uTime,
			  'id' => $twitter_id
              );
    endfor;
 
    // Save our new transient, and update the backup.
    set_transient($transName, $tweets, 60 * $cacheTime);
    update_option($backupName, $tweets);
  endif;
endif; 
 
// Now display the tweets.
?>


    <div id="twitter-feed-<?php print $twitter_name?>" class="fts-twitter-div">
    
    
<?php 
if(is_plugin_active('fts-rotate/fts-rotate.php') && $fts_rotate_feed == 'yes') {
	// FTS Rotate Head
	include( 'wp-content/plugins/fts-rotate/includes/fts-rotate-head.php' );
	$fts_rotate_on = 'yes';
}
else	{
	$fts_rotate_on = 'no';
}
	
	//start tweet loop
	foreach($tweets as $t) : 
	  
	  if($fts_rotate_on == 'yes' && $fts_rotate_feed == 'yes'){
		echo '<div class="fts-rotate-slide">';
	  }
	  ?>
      
        <p><?php print $t['text'];?></p><div class="tweeter-info"><div class="fts-twitter-image"><img class="twitter-image" src="<?php print $t['image'];?>" /></div><div class="uppercase bold"><a href="<?php print $t['user_permalink'];?>" target="_blank" class="black">@<?php print $t['name'];?></a></div><div class="right"><a href="<?php print $t['permalink']?>"><?php print $t['time'];?></a></div></div>
      
      <?php 
	  if($fts_rotate_on == 'yes' && $fts_rotate_feed == 'yes'){
		echo '</div>';
	  }
	  
   endforeach; ?>
      
<?php 
 if($fts_rotate_on == 'yes' && $fts_rotate_feed == 'yes'){
	// FTS Rotate Foot
	include( 'wp-content/plugins/fts-rotate/includes/fts-rotate-foot.php' ); 
}?> 
    <div class="clear"></div>
    </div> 
    
    


<?php 
return ob_get_clean(); 
}
?>