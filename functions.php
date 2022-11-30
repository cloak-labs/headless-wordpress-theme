<?php

add_action('enqueue_block_editor_assets', 'gutenberg_editor_assets');
function gutenberg_editor_assets() {
	// Load the theme styles within Gutenberg.
	wp_enqueue_style('my-gutenberg-editor-styles', get_theme_file_uri('/blocks/styles.css'), array(), filemtime(), 'all');
	wp_enqueue_script('gutenberg-editor-js', get_theme_file_uri('/blocks/scripts.js'), array(), filemtime(), true);
}

add_theme_support( 'post-thumbnails' ); // enable featured images
add_post_type_support( 'page', 'excerpt' ); // enable page excerpts

add_action('init', 'register_menus');
function register_menus() {
	register_nav_menus(
		array(
			'main-nav' => __('Header Menu'),
		)
	);
}

add_filter( 'acf/settings/rest_api_format', function () {
	return 'standard';
});

add_filter( 'jwt_auth_iss', function () {
	// Default value is get_bloginfo( 'url' );
	return 'http://23.29.145.150/~vanguard/';
});


add_action('init', 'register_blocks');
function register_blocks() {
	register_block_type( get_template_directory() . '/blocks/review/block.json' );
	register_block_type( get_template_directory() . '/blocks/cardsGrid/block.json' );
	register_block_type( get_template_directory() . '/blocks/faq/block.json' );
	register_block_type( get_template_directory() . '/blocks/cta/block.json' );
	register_block_type( get_template_directory() . '/blocks/banner/block.json' );
	register_block_type( get_template_directory() . '/blocks/globalBlock/block.json' );
	// register_block_type( get_template_directory() . '/blocks/calendly/block.json' );
	// register_block_type( get_template_directory() . '/blocks/global/block.json' );
}

function get_frontend_url(){
	// return 'https://lionheart.vercel.app';
	return 'http://localhost:3000';
}

// Custom 'View Page' link on post
add_filter( 'page_link', 'custom_view_page_url', 10, 2 ); 
add_filter( 'post_link', 'custom_view_page_url', 10, 2 );
add_filter( 'post_type_link', 'custom_view_page_url', 10, 2 );
function custom_view_page_url( $permalink, $post ) {
	$custom_permalink = get_frontend_url();
	if($permalink){
		$custom_permalink = str_replace( home_url(), $custom_permalink,  $permalink );
	}
	return $custom_permalink;
};

// Custom Preview Link
add_filter('preview_post_link', 'preview_url');
function preview_url() {
	global $post;
	$id = $post->ID;
	$parent = $post->post_parent;
	$secret = "Z87ZfKnwgE9Jf3q6zaFjalw2";
	$front_end_url = get_frontend_url();
	return "{$front_end_url}/api/preview?id={$id}&parent={$parent}&secret={$secret}";
}

// On-demand Incremental Static Regeneration (ISR) --> rebuild static pages/posts immediately upon saving your changes in WP
add_action('save_post_page', 'revalidate_on_save', 10, 2);
function revalidate_on_save($post_ID, $post){
	$front_end_url = get_frontend_url();

	// manually add environment URLs to this array if you wish to enable on-demand ISR for that environment (useful for testing one-off Vercel deployments or when running a production build locally) 
	$environments_to_revalidate = [$front_end_url, "http://localhost:3000"];

	$slug = $post->post_name;
	$type = $post->post_type;

	foreach ($environments_to_revalidate as $url){
		$response = wp_remote_get("{$url}/api/revalidate/{$slug}?post_type={$type}&secret=Z87ZfKnwgE9Jf3q6zaFjalw2");
	}
}

add_filter('jwt_auth_expire', 'on_jwt_expire_token',10,1);	
function on_jwt_expire_token($exp){ // add custom expiry date to our JWT (hook provided by "JWT Authentication for WP-API" plugin)
	$days = 500000; // 500,000 days == expiry in the year 3391.. i.e. we don't want the JWT to expire because the front-end data fetching will break 
	$seconds_in_a_day = 86400; //
	$exp = time() + ($seconds_in_a_day * $days);		
	return $exp;
}

// create custom function to return nav menu
function custom_wp_menu() {
	// Replace your menu name, slug or ID carefully
	return wp_get_nav_menu_items('Navbar');
}

// create new endpoint route
add_action( 'rest_api_init', function () {
	register_rest_route( 'wp/v2', 'menu', array(
			'methods' => 'GET',
			'callback' => 'custom_wp_menu',
	) );
});

add_filter( 'register_post_type_args', 'wpd_change_post_type_args', 10, 2 );
function wpd_change_post_type_args( $args, $post_type ){
	if( 'testimonials' == $post_type ){
			$args['rewrite']['with_front'] = false;
			$args['rewrite']['slug'] = 'testimonials';
	}else if( 'faqs' == $post_type ){
			$args['rewrite']['with_front'] = false;
			$args['rewrite']['slug'] = 'faqs';
	} 
	return $args;
}

