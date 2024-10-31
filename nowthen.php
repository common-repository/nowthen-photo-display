<?php
/*
Plugin Name: NowThen Photo Display
Plugin URI: http://omninoggin.com/2008/03/19/nowthen-photo-display-wordpress-widget/
Description: This plugin parses your RSS feed from the image service nowthen.com and displays it on your sidebar.
Author: Thaya Kareeson
Version: 0.4
Author URI: http://omninoggin.com/
*/

/*
Copyright 2008 Thaya Kareeson (email : thaya.kareeson@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//-----------------------------//
//---------- Globals ----------//
//-----------------------------//

// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') )
  define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
  define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );

$SITE_URL         = get_option('siteurl') . "/";
$PLUGIN_DIR       = WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__));
$PLUGIN_URL       = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
$PLUGIN_IMAGE_URL = $PLUGIN_URL.'/images/';
$NOWTHEN_RSS_URL  = "http://www.nowthen.com/rssposts?maxresults=";
$NOWTHEN_NUM_COLS = 4;

add_action('wp_head', 'nowthen_css');
function nowthen_css() {
	echo '<link rel="stylesheet" href="'.get_option('siteurl').'/wp-content/plugins/nowthen-photo-display/nowthen.css" type="text/css" media="screen" />'."\n";
}




//------------------------------------//
//---------- NowThen Widget ----------//
//------------------------------------//

function nowthen_widget_init() {

  // Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

  //---------- Widget Display ----------//
  function nowthen_widget($args) {
    extract($args);

    global $PLUGIN_IMAGE_URL;
    global $NOWTHEN_RSS_URL;

    // Load options
    $options = get_option('nowthen');

    $nowthen_cache          = $options['nowthen_cache'];          // number of seconds to cache the XML
    $nowthen_user           = $options['nowthen_user'];           // nowthen username
    $nowthen_widget_show    = $options['nowthen_widget_show'];    // # of pictures we are showing
    $nowthen_widget_title   = $options['nowthen_widget_title'];   // Title in sidebar for widget
    $nowthen_widget_tagline = $options['nowthen_widget_tagline']; // enable tagline?

    // check for invalid options
    if(is_null($nowthen_cache) || $nowthen_cache=="" || $nowthen_cache<0) {
      $nowthen_cache = 900;
      $options['nowthen_cache'] = $nowthen_cache;
    }
    if(is_null($nowthen_widget_title) || $nowthen_widget_title=="") {
      $nowthen_widget_title = "Recent Mobile Pictures";
      $options['nowthen_widget_title'] = $nowthen_widget_title;
    }
    if (!is_null($nowthen_user) && $nowthen_user!="") {
      $NOWTHEN_RSS_URL = "http://www.nowthen.com/".$nowthen_user."/rss?maxresults=";
    }
    if(is_null($nowthen_widget_show) || $nowthen_widget_show=="" || $nowthen_widget_show<1) {
      $nowthen_widget_show = 3;
      $options['nowthen_widget_show'] = $nowthen_widget_show;
    }
    if (is_null($nowthen_widget_tagline)) {
      $nowthen_widget_tagline = true;
      $options['nowthen_widget_tagline'] = $nowthen_widget_tagline;
    }

    echo $before_widget;
    echo $before_title . $nowthen_widget_title . $after_title;
    echo '<div class="nowthen">';

    if (! ($xmlparser = xml_parser_create()) ) { 
      die ("Cannot create parser");
    }
    
    xml_set_element_handler($xmlparser, "start_tag", "end_tag");
    xml_set_character_data_handler($xmlparser, "tag_contents");
    
    $filename = cacheFetch($NOWTHEN_RSS_URL . $nowthen_widget_show, $nowthen_cache);
    
    if (!($fp = fopen($filename, "r"))) { die("Cannot open ".$filename); }
    
    while ($data = fread($fp, 1024000)){
      $data=eregi_replace(">"."[[:space:]]+"."<","><",$data);
      if (!xml_parse($xmlparser, $data, feof($fp))) {
        $reason = xml_error_string(xml_get_error_code($xmlparser));
        $reason .= xml_get_current_line_number($xmlparser);
        die($reason);
      }
    }
    xml_parser_free($xmlparser);

    echo '</div>';
    if ($nowthen_widget_tagline) {
      echo '
        <p align="center">
          <a href="http://nowthen.com/">
            <img src="' . $PLUGIN_IMAGE_URL . 'nowthen_logo.png" alt="NowThen"/><br/>
            Snap, Share & Store your mobile photos for free
          </a>
        </p>
      ';
    }
    echo $after_widget;
  }


  //---------- Widget Options----------//
  function nowthen_widget_options() {
    // Get options
    $options = get_option('nowthen');

    // options exist? if not set defaults
    if ( !is_array($options) )
      $options = array('nowthen_widget_title'=>'Recent Mobile Pictures', 'nowthen_widget_show'=>'3', 'nowthen_widget_tagline'=>1);

    // form posted?
    if ( $_POST['nowthen-submit'] ) {
      // Remember to sanitize and format use input appropriately.
      $options['nowthen_widget_title']   = strip_tags(stripslashes($_POST['nowthen-widget-title']));
      $options['nowthen_widget_show']    = strip_tags(stripslashes($_POST['nowthen-widget-show']));
      $options['nowthen_widget_tagline'] = isset($_POST['nowthen-widget-tagline']);
      update_option('nowthen', $options);
    }

    // Get options for form fields to show
    $title   = htmlspecialchars($options['nowthen_widget_title'], ENT_QUOTES);
    $show    = htmlspecialchars($options['nowthen_widget_show'], ENT_QUOTES);
    $tagline = $options['nowthen_widget_tagline'] ? 'checked="checked"' : '';

    // The form fields
    echo '<p style="text-align:right;">
      <label for="nowthen-widget-title">' . __('Title:') . '
      <input style="width: 200px;" id="nowthen-widget-title" name="nowthen-widget-title" type="text" value="'.$title.'" />
      </label></p>';
    echo '<p style="text-align:right;">
      <label for="nowthen-widget-show">' . __('Number of Pictures to Show:') . '
      <input style="width: 200px;" id="nowthen-widget-show" name="nowthen-widget-show" type="text" value="'.$show.'" />
      </label></p>';
    echo '<p style="text-align:right;">
      <label for="nowthen-widget-tagline">' . __('Display NowThen logo and tagline:') . '
      <input id="nowthen-widget-tagline" name="nowthen-widget-tagline" type="checkbox" '.$tagline.' />
      </label></p>';
    echo '<input type="hidden" id="nowthen-submit" name="nowthen-submit" value="1" />';
    echo '<p style="text-align:right;">
      Additional options can be found at <a href="' . $SITE_URL . 'options-general.php?page=nowthen-photo-display/nowthen.php">NowThen Options</a>.
      </p>';
  }



  // Register widget for use
  register_sidebar_widget('NowThen Photo Display', 'nowthen_widget');

  // Register settings for use, 400x200 pixel form
  register_widget_control('NowThen Photo Display', 'nowthen_widget_options', 400, 200);
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'nowthen_widget_init');




//------------------------------------// 
//--------- NowThen Gallery ----------// 
//------------------------------------// 

function gallery_nowthen($content) {
	if (strstr($content, '[[nowthen]]')) {
		$content = str_replace('[[nowthen]]', gallery_nowthen_display(), $content);
	}
	return $content;
}

function gallery_nowthen_display($args) {
  extract($args);

  global $PLUGIN_IMAGE_URL;
  global $NOWTHEN_RSS_URL;
  global $NOWTHEN_NUM_COLS;

  // Load options 
  $options = get_option('nowthen');
  
  $nowthen_cache           = $options['nowthen_cache'];           // number of seconds to cache the XML
  $nowthen_gallery_show    = $options['nowthen_gallery_show'];    // # of pictures we are showing
  $nowthen_gallery_tagline = $options['nowthen_gallery_tagline']; // enable tagline?
  $nowthen_num_columns     = $options['nowthen_num_columns'];     // # of pictures per row
  $nowthen_user            = $options['nowthen_user'];            // nowthen username

  // check for invalid options
  if(is_null($nowthen_cache) || $nowthen_cache=="" || $nowthen_cache<0) {
    $nowthen_cache = 900;
    $options['nowthen_cache'] = $nowthen_cache;
  }
  if(is_null($nowthen_gallery_show) || $nowthen_num_columns=="" || $nowthen_gallery_show<1) {
    $nowthen_gallery_show = 30;
    $options['nowthen_gallery_show'] = $nowthen_gallery_show;
  }
  if (is_null($nowthen_gallery_tagline)) {
    $nowthen_gallery_tagline = true;
    $options['nowthen_gallery_tagline'] = $nowthen_gallery_tagline;
  }
  if(is_null($nowthen_num_columns) || $nowthen_num_columns=="" || $nowthen_num_columns<1) {
    $nowthen_num_columns = 3;
    $options['nowthen_num_columns'] = $nowthen_num_columns;
  }
  if (!is_null($nowthen_user) && $nowthen_user!="") {
    $NOWTHEN_RSS_URL = "http://www.nowthen.com/".$nowthen_user."/rss?maxresults=";
  }

  if (! ($xmlparser = xml_parser_create()) ) { 
    die ("Cannot create parser");
  }
  
  echo '<div style="clear:both"></div>';
  echo '<div class="nowthen_gallery">';

  // Set this NOWTHEN_NUM_COLS global so the XML parser knows how to generate the pictures
  $NOWTHEN_NUM_COLS = $nowthen_num_columns;
  xml_set_element_handler($xmlparser, "gallery_start_tag", "gallery_end_tag");
  xml_set_character_data_handler($xmlparser, "tag_contents");
  
  $filename = cacheFetch($NOWTHEN_RSS_URL . $nowthen_gallery_show, $nowthen_cache);
  
  if (!($fp = fopen($filename, "r"))) { die("Cannot open ".$filename); }
  
  while ($data = fread($fp, 1024000)){
    $data=eregi_replace(">"."[[:space:]]+"."<","><",$data);
    if (!xml_parse($xmlparser, $data, feof($fp))) {
      $reason = xml_error_string(xml_get_error_code($xmlparser));
      $reason .= xml_get_current_line_number($xmlparser);
      die($reason);
    }
  }
  xml_parser_free($xmlparser);

  echo '<div style="clear:both"></div>';

  if ($nowthen_gallery_tagline) {
    echo '<p align="center"><a href="http://nowthen.com/' . $nowthen_user . '" style="text-decoration:none"><img src="' . $PLUGIN_IMAGE_URL . 'nowthen_logo.png" alt="NowThen" style="border:none"/><br/>See more of my mobile photos at NowThen</a></p>';
  }

  echo '</div>';
}

// See if user is diabling NowThen gallery to slightly improve performance
$options = get_option('nowthen');
$nowthen_gallery_disable = $options['nowthen_gallery_disable'];
if (is_null($nowthen_gallery_disable)) {
  $nowthen_gallery_disable = false;
  $options['nowthen_gallery_disable'] = $nowthen_gallery_disable;
}

if (!$nowthen_gallery_disable) {
  // Add gallery display code into the_content
  add_action('the_content', 'gallery_nowthen');
}




//-------------------------------------//
//---------- NowThen Options ----------//
//-------------------------------------//

function nowthen_option_menu() {
	if (function_exists('current_user_can')) {
		if (!current_user_can('manage_options')) return;
	} else {
		global $user_level;
		get_currentuserinfo();
		if ($user_level < 8) return;
	}
	if (function_exists('add_options_page')) {
		add_options_page(__('NowThen'), __('NowThen'), 1, __FILE__, 'nowthen_gallery_options');
	}
}

function nowthen_gallery_options(){
	if (isset($_POST['update_options'])) {
    $options['nowthen_user']            = strip_tags(stripslashes($_POST['nowthen-user']));
    $options['nowthen_cache']           = strip_tags(stripslashes($_POST['nowthen-cache']));
		$options['nowthen_gallery_disable'] = strip_tags(stripslashes($_POST['nowthen-gallery-disable']));
		$options['nowthen_gallery_show']    = strip_tags(stripslashes($_POST['nowthen-gallery-show']));
		$options['nowthen_num_columns']     = strip_tags(stripslashes($_POST['nowthen-num-columns']));
    $options['nowthen_gallery_tagline'] = isset($_POST['nowthen-gallery-tagline']);
    update_option('nowthen', $options);

		// Show a message to say we've done something
		echo '<div class="updated"><p>' . __('Options saved') . '</p></div>';
	} else {
		// If we are just displaying the page we first load up the options array
    $options = get_option('nowthen');
    // options exist? if not set defaults
    if ( !is_array($options) )
      $options = array('nowthen_user'=>'', 'nowthen_cache'=>'900', 'nowthen_gallery_disable'=>0, 'nowthen_gallery_show'=>'30', 'nowthen_num_columns'=>'4', 'nowthen_gallery_tagline'=>1);
	}
	//now we drop into html to display the option page form
	?>
		<div class="wrap">
		<h2><?php echo __('NowThen Options'); ?></h2>
		<form method="post" action="">
		<fieldset class="options">
    <p>After initial installation, please specify your NowThen username.  If NowThen gallery is enabled, you can place the text "[[nowthen]]" into any one of your posts/pages to display your NowThen gallery.  If you are not going to use the NowThen gallery, it is recommend that you check "Disable NowThen gallery" below for slightly better site performance.</p>
		<table class="optiontable" align="center">
			<tr valign="top">
				<th scope="row" align="right"><?php _e('NowThen username:') ?></th>
				<td align="left"><input name="nowthen-user" type="text" id="nowthen-user" value="<?php echo $options['nowthen_user']; ?>" size="15" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('XML cache expiration (seconds):') ?></th>
				<td align="left"><input name="nowthen-cache" type="text" id="nowthen-cache" value="<?php echo $options['nowthen_cache']; ?>" size="15" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Disable NowThen gallery:') ?></th>
				<td align="left"><input id="nowthen-gallery-disable" name="nowthen-gallery-disable" type="checkbox" <?php echo $options['nowthen_gallery_disable'] ? 'checked="checked"' : '' ?> /></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Number of photos to display in gallery (30 max):') ?></th>
				<td align="left"><input name="nowthen-gallery-show" type="text" id="nowthen-gallery-show" value="<?php echo $options['nowthen_gallery_show']; ?>" size="15" /></td>
			</tr>
			<tr valign="top" align="right">
				<th scope="row"><?php _e('Number of columns per row of photos:') ?></th>
				<td align="left"><input name="nowthen-num-columns" type="text" id="nowthen-num-columns" value="<?php echo $options['nowthen_num_columns']; ?>" size="15" /></td>
			</tr>
			<tr valign="top" align="right">
				<th scope="row"><?php _e('Display "See more of my mobile photos at NowThen"?:') ?></th>
				<td align="left"><input id="nowthen-gallery-tagline" name="nowthen-gallery-tagline" type="checkbox" <?php echo $options['nowthen_gallery_tagline'] ? 'checked="checked"' : '' ?> /></td>
			</tr>
		</table>
		</fieldset>
		<div class="submit"><input type="submit" name="update_options" value="<?php _e('Update') ?>"  style="font-weight:bold;" /></div>
		</form>    		
	</div>
	<?php	
}

// Install the options page
add_action('admin_menu', 'nowthen_option_menu');




//------------------------------------------//
//---------- XML parser functions ----------//
//------------------------------------------//

// XML globals used while parsing
$count = 0;
$currentTag = "";
$currentLink = "";
$delayedDescription = "";
$recordOn = 0;

function cacheFetch($url,$age) {
  // directory in which to store cached files
  global $PLUGIN_DIR;
  $cacheDir = $PLUGIN_DIR . "/cache/";
  if (!is_dir($cacheDir)) {
    echo ("Cannot find cache directory: " . $cacheDir);
  }
  // cache filename constructed from MD5 hash of URL
  $filename = $cacheDir.md5($url);
  // default to fetch the file
  $fetch = true;
  // but if the file exists, don't fetch if it is recent enough
  if (file_exists($filename)) {
    $fetch = (filemtime($filename) < (time()-$age));
  }
  // fetch the file if required
  if ($fetch)
  {
    // get xml via http
    $handle = fopen($url, "rb");
    $contents = "";
    while (!feof($handle)) {
      $contents .= fread($handle, 8192);
    }
    fclose($handle);

    // save xml as local cache
    $fh = fopen($filename, 'w') or die("can't open file");
    fwrite($fh, $contents);
    fclose($fh);
  }

  // return the cache filename
  return $filename;
}

function gallery_start_tag($parser, $name, $attribs) {
  start_tag($parser, $name, $attribs, 'gallery');
}

function start_tag($parser, $name, $attribs, $mode) {
  if (is_array($attribs)) {
    global $currentTag;
    global $currentLink;
    global $recordOn;
    $currentTag = $name;
    if($name == "ITEM") {$recordOn = 1;}
    if($name == "LINK" && $recordOn) {
      if($mode == 'gallery') {
        echo '<div class="nowthen_gallery_photo">';
      }
      else {
        echo '<div class="nowthen_photo">';
      }
    }
    while(list($key,$val) = each($attribs)) {}
  }
}

function gallery_end_tag($parser, $name) {
  end_tag($parser, $name, 'gallery');
}

function end_tag($parser, $name, $mode) {
  global $currentTag;
  global $recordOn;
  global $delayedDescription;
  global $count;
  global $NOWTHEN_NUM_COLS;
  if($currentTag == "DESCRIPTION" && $recordOn) {
    echo '</div>';
    if($mode == 'gallery') {
      if(++$count >= $NOWTHEN_NUM_COLS) {
        echo '<div style="clear:both"></div>';
        $count = 0;
      }
    }
    else {
      echo '<br />';
    }
  }
}

function tag_contents($parser, $data) {
  global $currentTag;
  global $currentLink;
  global $recordOn;
  global $delayedDescription;
  if($currentTag == "DESCRIPTION" && $recordOn) {
    if(preg_match("/<img /i", $data)) {
      $data = str_replace("<br />", "", $data);
      $data = str_replace(".jpg\"", ".jpg\" alt=\"Mobile Picture:".$delayedDescription."\"", $data);
      $data = str_replace("/uploads/h/", "/uploads/", $data);
      echo $data;
      echo "<br />";
      echo $delayedDescription;
    }
    else {
      $delayedDescription = $data;
    }
  }
}

