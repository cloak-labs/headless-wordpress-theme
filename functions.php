<?php

/*
  Expand ACF field data returned in REST API; eg. image fields return full image data rather than just an ID. More info: https://www.advancedcustomfields.com/resources/wp-rest-api-integration/
*/
add_filter('acf/settings/rest_api_format', function () {
  return 'standard';
});

// Change the JWT token issuer:
add_filter('jwt_auth_iss', function () {
  // Default value is get_bloginfo( 'url' );
  return site_url();
});

// Adds a custom REST API endpoint "/menu" which returns WP menu data
add_action('rest_api_init', function () {
  register_rest_route('wp/v2', 'menu', array(
    'methods' => 'GET',
    'callback' => 'custom_wp_menu',
  ));
});

// create custom function to return nav menu
function custom_wp_menu() {
   // Replace your menu name, slug or ID carefully
   return wp_get_nav_menu_items('Navbar');
}


/*
  Add ability for "editor" user role to edit WP Menus, but hide all other submenus under Appearance (for editors only) -- eg. we don't want clients to be able to switch/deactivate theme and break site 
*/
function enable_menu_for_editors() {

  $role_object = get_role( 'editor' );
  if(!$role_object->has_cap('edit_theme_options')){
    $role_object->add_cap( 'edit_theme_options' );
  }

  if (current_user_can('editor')) { // remove certain Appearance > Sub-pages
      remove_submenu_page( 'themes.php', 'themes.php' ); // hide the theme selection submenu
      remove_submenu_page( 'themes.php', 'widgets.php' ); // hide the widgets submenu

      // special handling for removing "Customize" submenu (above method doesn't work due to its URL structure) --> snippet taken from https://stackoverflow.com/a/50912719/8297151
      global $submenu;
      if ( isset( $submenu[ 'themes.php' ] ) ) {
          foreach ( $submenu[ 'themes.php' ] as $index => $menu_item ) {
              foreach ($menu_item as $value) {
                  if (strpos($value,'customize') !== false) {
                      unset( $submenu[ 'themes.php' ][ $index ] );
                  }
              }
          }
      }
  }
}
add_action('admin_head', 'enable_menu_for_editors');