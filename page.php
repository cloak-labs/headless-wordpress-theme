<?php
	global $post;
	$front_end_url = get_frontend_url();
	$slug = $post->post_name;
	$type = $post->post_type;
	$url = "{$front_end_url}/api/revalidate/{$slug}?type={$type}&secret=Z87ZfKnwgE9Jf3q6zaFjalw2";
	$response = wp_remote_get($url);
	echo $url;
function pretty_print($arr)
{
  echo '<pre>';
  print_r($arr);
  echo '</pre>';
}
