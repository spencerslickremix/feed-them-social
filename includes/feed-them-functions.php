<?php
/************************************************
 	Function file for Feed Them Social plugin
************************************************/

add_action('admin_menu', 'Feed_Them_Main_Menu');

function Feed_Them_Main_Menu() {
   add_menu_page('Feed Them Social', 'Feed Them', 'edit_plugins', 'feed-them-settings-page', 'feed_them_settings_page', 'div', 99);
}

add_action('admin_menu', 'Feed_Them_Submenu_Pages');

function Feed_Them_Submenu_Pages() {   
	add_submenu_page( 
          'feed-them-settings-page'
        , 'System Info' 
        , 'System Info'
        , 'manage_options'
        , 'fts-system-info-submenu-page'
        , 'feed_them_system_info_page'
    );
}

add_action('admin_enqueue_scripts', 'feed_them_admin_css');
// THIS GIVES US SOME OPTIONS FOR STYLING THE ADMIN AREA
function feed_them_admin_css() {
    wp_register_style( 'feed_them_admin', plugins_url( 'admin/css/admin.css', dirname(__FILE__) ) );  
	wp_enqueue_style('feed_them_admin');
}

if (isset($_GET['page']) && $_GET['page'] == 'fts-system-info-submenu-page') {
  add_action('admin_enqueue_scripts', 'feed_them_system_info_css');
  // THIS GIVES US SOME OPTIONS FOR STYLING THE ADMIN AREA
  function feed_them_system_info_css() {
	  wp_register_style( 'fts-settings-admin-css', plugins_url( 'admin/css/admin-settings.css',  dirname(__FILE__) ) );
	  wp_enqueue_style('fts-settings-admin-css'); 
  }
}

if (isset($_GET['page']) && $_GET['page'] == 'feed-them-settings-page') {
  add_action('admin_enqueue_scripts', 'feed_them_settings');
  // THIS GIVES US SOME OPTIONS FOR STYLING THE ADMIN AREA
  function feed_them_settings() {
	  wp_register_style( 'feed_them_settings_css', plugins_url( 'admin/css/settings-page.css',  dirname(__FILE__) ) );
	  wp_enqueue_style('feed_them_settings_css'); 
	  wp_enqueue_script( 'feed_them_settings_js', plugins_url( 'admin/js/admin.js',  dirname(__FILE__) ) ); 
  }
}

function feed_them_clear_cache() {
	
   $plugins = array (
	 1 => 'facebook',
	 2 => 'instagram',
	 3 => 'twitter',
   );
  
   foreach($plugins as $key => $value){
	$files = glob('../wp-content/plugins/feed-them-social/feeds/'.$value.'/cache/*'); // get all file names
	  if($files){
		foreach($files as $file){ // iterate files
		  if(is_file($file))
			unlink($file); // delete file
		}//end foreach $files
	  }// end if($files)
   }//end foreach $plugins
   
  return 'Cache for all FTS Feeds cleared!';
}

//Settings option. Add Custom CSS to the header of FTS pages only
$fts_include_custom_css_checked_css =  get_option( 'fts-color-options-settings-custom-css' );
	if ($fts_include_custom_css_checked_css == '1') { 
	
	add_action('wp_enqueue_scripts', 'fts_color_options_head_css');
function  fts_color_options_head_css() {
		?>
<style type="text/css"><?php echo get_option('fts-color-options-main-wrapper-css-input');?></style>
		<?php
	}
 }
 //Settings option. Custom Powered by Feed Them Social Option
$fts_powered_text_options_settings =  get_option( 'fts-powered-text-options-settings' );
	if ($fts_powered_text_options_settings == '1') { 
	  // do not show the powered by feed them social text
	}
	else {
	add_action('wp_enqueue_scripts', 'fts_powered_by_js');
function  fts_powered_by_js() {
	
	  wp_register_style( 'fts_powered_by_css', plugins_url( 'css/powered-by.css',  dirname(__FILE__) ) );
	  wp_enqueue_style('fts_powered_by_css'); 
	  
	  wp_enqueue_script( 'fts_powered_by_js', plugins_url( 'js/powered-by.js',  dirname(__FILE__) ),
	  array( 'jquery' )
	 ); 	
	}
 }
?>