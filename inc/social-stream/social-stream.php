<?php

/**
 * Script Name: PHP Social Stream
 * Script URI: http://axentmedia.com/php-social-stream/
 * Description: Combine all your social media network & feed updates (Facebook, Twitter, Google+, Flickr, YouTube, RSS, ...) into one feed and display on your website.
 * Tags: social media, social networks, social feed, social tabs, social wall, social timeline, social stream, php social stream, feed reader, facebook, twitter, google+, tumblr, delicious, pinterest, flickr, instagram, youtube, vimeo, stumbleupon, deviantart, rss, soundcloud, vk, linkedin, vine
 * Version: 2.5
 * Author: Axent Media
 * Author URI: http://axentmedia.com/
 * License: http://codecanyon.net/licenses/standard
 * 
 * Copyright 2015 Axent Media (axentmedia@gmail.com)
 */

// Load configuration
define( 'SB_DIR', realpath(dirname(__FILE__)) );
require( SB_DIR . '/config.php' );

if ( ! defined( 'SB_PATH' ) )
    exit('Path to PHP Social Stream script directory is not defined!');

// For load more
if ( ! session_id() ) {
    //session_start();
}

// Make sure feeds are getting local timestamps
if ( ! ini_get('date.timezone') )
	date_default_timezone_set('UTC');

// DateTime localization
if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
    setlocale(LC_ALL, ss_win_locale(SB_LOCALE));
} else {
    setlocale(LC_ALL, SB_LOCALE);
}

// Define constants
define( 'SB_DT_FORMAT', ss_format_locale(SB_DATE_FORMAT) );
define( 'SB_TT_FORMAT', ss_format_locale(SB_TIME_FORMAT) );
define( 'SB_LOGFILE', SB_DIR . '/log.txt' );

// load cache system
include( SB_DIR . '/library/SimpleCache.php' );

// Language localization
include( SB_DIR . '/language/social-stream-'.SB_LOCALE.'.php' );
$GLOBALS['_'] = $_;
$GLOBALS['enqueue'] = array('general' => false, 'timeline' => false, 'carousel' => false, 'rotating' => false, 'wall' => false);
$GLOBALS['sb_scripts'] = array();

// Social Stream main class
class SocialStream {
    public $attr, $final, $finalslide;
    public $echo = true;
    public $args = null;
    
    public function __construct() {}
    
    // Initialize by property
    function run(){
        $this->init( $this->attr, $this->echo, $this->args );
    }

    // Initialize by function
    function init( $attr, $echo = true, $args = null, $ajax_feed = array(), $loadmore = array() ) {
        
        $id = (@$attr['id']) ? $attr['id'] : substr( sha1( implode('', $attr) ), 0, 5 );
        $type = (@$attr['type']) ? $attr['type'] : 'wall';
        $this->sboption = $attr['network'];
        $attr_ajax = json_encode($attr);
        
        // get default setting & post options
        $setoption = array(
            'setting' => array(
                'theme' => 'sb-modern-light',
                'results' => '30',
                'words' => '40',
                'slicepoint' => '300',
                'commentwords' => '20',
                'titles' => '15',
                'readmore' => '1',
                'order' => 'date',
                'filters' => '1',
                'loadmore' => '1',
                'iframe' => 'media',
                'layout_image' => 'imgexpand',
                'layout_user' => 'userpic',
                'links' => '1',
                'nofollow' => '1',
                'https' => false,
                'filters_order' => 'facebook,twitter,google,tumblr,delicious,pinterest,flickr,instagram,youtube,vimeo,stumbleupon,deviantart,rss,soundcloud,vk,linkedin,vine',
                'cache' => '360',
                'crawl' => '10',
                'debuglog' => '0'
            ),
            'wallsetting' => array(
                'transition' => '400',
                'stagger' => '',
                'originLeft' => 'true',
                'fixWidth' => 'false',
                'breakpoints' => array('5', '4', '4', '3', '2', '2', '1'),
                'itemwidth' => '230'
            ),
            'feedsetting' => array(
                'rotate_speed' => '100',
                'duration' => '4000',
                'direction' => 'up',
                'controls' => '1',
                'autostart' => '1',
                'pauseonhover' => '1',
                'width' => '250'
            ),
            'carouselsetting' => array(
                'cs_speed' => '400',
                'cs_pause' => '2000',
                'autoWidth'=> 'false',
                'cs_item' => array('4', '3', '2', '2', '1'),
                'cs_width' => '230',
                'cs_rtl' => 'false',
                'cs_controls' => 'true',
                'cs_auto' => 'false',
                'cs_loop' => 'true',
                'cs_pager' => 'true',
                'slideMargin' => '10',
                'slideMove' => '1'
            ),
            'timelinesetting' => array(
                'onecolumn' => 'false'
            )
        );
        
        // define theme
        if ( ! $theme = @$attr['theme'] )
            $theme = $setoption['setting']['theme'];
        $themeoption = $GLOBALS['themes'][$attr['theme']];

        // set some settings
        $label = $type.$id;
        if ( $type == 'feed' ) {
            $is_feed = true;
            $settingsection = (@$attr['carousel']) ? 'carouselsetting' : 'feedsetting';
            $filterlabel = '';
        } else {
            $is_feed = false;
            $settingsection = $type.'setting';
            $filterlabel = ' filter-label';
        }
        $is_timeline = ( $type == 'timeline' ) ? true : false;
        $is_wall = ( $type == 'wall' ) ? true : false;

        $typeoption = $type;
        if ( $is_feed ) {
            if ( @$attr['position'] ){
                if ( @$attr['position'] != 'normal' )
                    $typeoption = 'feed_sticky';
            }
            if (@$attr['carousel'])
                $typeoption = 'feed_carousel';
        }

        // main container id
        if ( @$label )
            $attr_id = ' id="timeline_'.$label.'"';
        $class = array('sboard');

        // merge shortcode and widget attributes with related default settings
        if ( ! @$setoption[$settingsection] )
            $setoption[$settingsection] = array();
        $this->attr = $attr = array_merge($setoption['setting'], $setoption[$settingsection], $attr);
        
    	// the array of feeds to get
    	$filters_order = explode(',', str_replace(' ', '', $attr['filters_order']) );
        $this->feed_keys = ( ! empty($ajax_feed) && $ajax_feed != 'all' ) ? $ajax_feed : $filters_order;
        
    	// set results
        if ( ! $results = @$attr['results'] )
    		$results = 10;
    	if (@$args['liveresults'])
        if ( $results < $args['liveresults'] )
            $results = $args['liveresults'];
        if ($results > 100)
            $results = 100;
        $attr['results'] = $results;
            
        $attr['cache'] = (int)$attr['cache'];
        // set crawl time limit (some servers can not read a lot of feeds at the same time)
        $GLOBALS['crawled'] = 0;
        $crawl_limit = ($attr['cache'] == 0) ? 0 : (int)@$attr['crawl'];
        
        // Init cache
        $cache = new SimpleCache;
        $cache->debug_log = @$attr['debuglog'];
        
        if ( $is_feed ) {
            if (@$attr['carousel']){
                $class[] = 'sb-carousel';
                if (@$args['widget_id'])
                    $class[] = 'sb-widget';
            } else {
                $class[] = 'sb-widget';
            }
        } elseif ($is_wall) {
            $class[] = 'sb-wall';
        }

        if (@$attr['iframe'] == 'media' || @$attr['iframe'] == 'slide') {
            $iframe = true;
        } else
            $iframe = false;
        
        // if slideshow is active
        if (@$attr['iframe'] == 'slide') {
            $slideshow = true;
            $class[] = 'sb-slideshow';
        }
        
        // set the block height
        $block_height = (@$attr['height']) ? $attr['height'] : 400;

        // load layout
        include_once( SB_DIR . '/layout/'.$themeoption['layout'].'.php' );
        if ( $attr['theme'] != $themeoption['layout'] )
            $class[] = 'sb-'.$themeoption['layout'];

        $layoutclass = 'ss_'.$themeoption['layout'].'_layout';
        $layoutobj = new $layoutclass;
        
        // load slide layout
        if ( isset($slideshow) ) {
            include_once( SB_DIR . '/layout/slide/default.php' );
            $slidelayoutobj = new ss_default_slidelayout;
        }
        
        if ( ! $ajax_feed) {
        // do some styling stuffs
        $dotboard = "#timeline_$label.sboard";
        if ( @$themeoption['social_colors'] ) {
            $style = $layoutobj->create_colors( $themeoption['social_colors'], $filters_order, $type, $dotboard, $attr, @$themeoption[$typeoption] );
        }
        
        if ($is_wall) {
            $dotitem2 = '.sb-item';
            $sbitem2 = "$dotboard $dotitem2";
            $sbgutter2 = "$dotboard .sb-gsizer";
            $gutterX = (@$attr['gutterX']) ? $attr['gutterX'] : 10;
            $gutterY = (@$attr['gutterY']) ? $attr['gutterY'] : 10;
            if (@$attr['itemwidth']) {
                $itemwidth = (@$attr['itemwidth'] ? $attr['itemwidth'] : $defoption['wallsetting']['itemwidth']);
            }
            if (@$attr['fixWidth'] == 'false') {
                if ( ! @is_array($attr['breakpoints']) )
                    $attr['breakpoints'] = $defoption['wallsetting']['breakpoints'];
                    
                // calculate breakpoints
                $bpsizes = array(1200, 960, 768, 600, 480, 320, 180);
                foreach ($attr['breakpoints'] as $bpkey => $breakpoint) {
                    $gut = round(($gutterX * 100) / $bpsizes[$bpkey], 3);
                    $yut = round(($gutterY * 100) / $bpsizes[$bpkey], 3);
                    $bpyut = round($bpsizes[$bpkey] / (100/$yut), 3);
                    $tw = round(100 - (($breakpoint - 1) * $gut), 3);
                    if ($tw < 100) {
                        $bpgrid = round($tw / $breakpoint, 3);
                        $bpgut = $gut;
                    } else {
                        $bpgrid = 100;
                        $bpgut = 0;
                    }
                    $bpcol[$bpkey] = "$sbitem2 { width: $bpgrid%; margin-bottom: {$bpyut}px; }
                    $sbgutter2 { width: $bpgut%; }";
                }
                
                $mediaqueries = "$bpcol[0]
@media (min-width: 960px) and (max-width: 1200px) { $bpcol[1] }
@media (min-width: 768px) and (max-width: 959px) { $bpcol[2] }
@media (min-width: 600px) and (max-width: 767px) { $bpcol[3] }
@media (min-width: 480px) and (max-width: 599px) { $bpcol[4] }
@media (min-width: 320px) and (max-width: 479px) { $bpcol[5] }
@media (max-width: 319px) { $bpcol[6] }";
            } else {
                $style[$sbitem2][] = 'width: '.$itemwidth.'px';
                $style[$sbitem2][] = 'margin-bottom: '.$gutterY.'px';
            }
            //$style['#sb_'.$label][] = 'overflow: hidden';
        } else {
            if (@$attr['carousel']) {
                $dotitem2 = '.lslide';
                if (@$attr['cs_width']) {
                    $itemwidth = $attr['cs_width'];
                    $style["$dotboard $dotitem2"][] = 'width: '.$itemwidth.'px';
                }
            }
        }
        
        if ( $font_size = @$themeoption['font_size'] ) {
            $style["$dotboard, $dotboard a"][] = 'font-size: '.$font_size.'px';
            $style["$dotboard .sb-heading"][] = 'font-size: '.($font_size+1).'px !important';
        }
        
        if ( $is_feed && @$themeoption[$typeoption]['title_background_color'] )
        if ( $themeoption[$typeoption]['title_background_color'] != 'transparent') {
            $style["$dotboard .sb-heading, $dotboard .sb-opener"][] = 'background-color: '.$themeoption[$typeoption]['title_background_color'].' !important';
        }
        if ( $is_feed && @$themeoption[$typeoption]['title_color'] )
        if ( $themeoption[$typeoption]['title_color'] != 'transparent')
            $style["$dotboard .sb-heading"][] = 'color: '.$themeoption[$typeoption]['title_color'];
        
        if ( $is_feed )
            $csskey = "$dotboard .sb-content, $dotboard .toolbar";
        else
            $csskey = '#sb_'.$label;
            
        if ( @$themeoption[$typeoption]['background_color'] ) {
            if ( $themeoption[$typeoption]['background_color'] != 'transparent') {
                $bgexist = true;
                $style[$csskey][] = 'background-color: '.$themeoption[$typeoption]['background_color'];
            }
        }
        
        if ( $is_timeline ) {
            $fontcsskey = "$dotboard .timeline-row";
        } else
            $fontcsskey = "$dotboard .sb-item";
            
        if (@$themeoption[$typeoption]['font_color'])
        if ($themeoption[$typeoption]['font_color'] != 'transparent') {
            $rgbColorVal = ss_hex2rgb($themeoption[$typeoption]['font_color']); // returns the rgb values separated by commas

            if ( $is_timeline ) {
                $style["$dotboard .timeline-row small"][] = 'color: '.$themeoption[$typeoption]['font_color'];
            }
            
            $style["$fontcsskey .sb-title a"][] = 'color: '.$themeoption[$typeoption]['font_color'];
            $style["$fontcsskey"][] = 'color: rgba('.$rgbColorVal.', 0.8)';
        }
        if (@$themeoption[$typeoption]['link_color'])
        if ($themeoption[$typeoption]['link_color'] != 'transparent') {
            $rgbColorVal = ss_hex2rgb($themeoption[$typeoption]['link_color']); // returns the rgb values separated by commas
            $style["$fontcsskey a"][] = 'color: '.$themeoption[$typeoption]['link_color'];
            $style["$fontcsskey a:visited"][] = 'color: rgba('.$rgbColorVal.', 0.8)';
        }

        if ( @$themeoption[$typeoption]['background_image'] ) {
            $bgexist = true;
            $cssbgkey = $csskey;
            if ( $is_feed )
                $cssbgkey = "$dotboard .sb-content";
            $style[$cssbgkey][] = 'background-image: url('.$themeoption[$typeoption]['background_image'].');background-repeat: repeat';
        }

        $location = null;
        if ( $is_feed ) {
            $class[] = @$attr['position'];
            if ( @$attr['position'] != 'normal' ) {
                $class[] = @$attr['location'];
                if ( ! @$attr['autoclose'] ) {
                    $class[] = 'open';
                    $active = ' active';
                }
                
                $locarr = explode('_', str_replace('sb-', '', @$attr['location']) );
                $location = $locarr[0];
            }
        }

        if (@$attr['carousel'] && @$attr['tabable'])
            unset($attr['tabable']);
        
        if (@$attr['carousel'])
            $attr['layout_image'] = 'imgnormal';
            
        if (@$attr['tabable'])
            $class[] = 'tabable';
        
        if ( (@$attr['filters'] or @$attr['controls']) && !@$attr['carousel']) {
            $style[$dotboard.' .sb-content'][] = 'border-bottom-left-radius: 0 !important;border-bottom-right-radius: 0 !important';
        }
        if ( (@$attr['showheader'] || ($location == 'bottom' && ! @$attr['tabable']) ) && $is_feed) {
            $style[$dotboard.' .sb-content'][] = 'border-top: 0 !important;border-top-left-radius: 0 !important;border-top-right-radius: 0 !important';
        }
        if ( $location == 'left' )
            $style[$dotboard.' .sb-content'][] = 'border-top-left-radius: 0 !important';
        if ( $location == 'right' )
            $style[$dotboard.' .sb-content'][] = 'border-top-right-radius: 0 !important';
        
    	// set block border
        if ( @$themeoption[$typeoption]['border_color'] ) {
            if ( $themeoption[$typeoption]['border_color'] != 'transparent') {
                $bgexist = true;
                if ( $is_feed ) {
                    $style[$dotboard.' .toolbar'][] = 'border-top: 0 !important';
                }
                $style[$csskey][] = 'border: '.@$themeoption[$typeoption]['border_size'].'px solid '.$themeoption[$typeoption]['border_color'];
            }
        } else {
            if (@$attr['carousel'])
                $style[$dotboard.' .sb-content'][] = 'padding: 10px 0 5px 0';
        }
        
        // set block padding if required
        if (@$bgexist) {
            $border_radius = @$themeoption[$typeoption]['border_radius'];
            if ( ! $is_feed ) {
                if ($border_radius == '')
                    $border_radius = 7;
            }
            if ($border_radius != '') {
                $radius = 'border-radius: '.$border_radius.'px;-moz-border-radius: '.$border_radius.'px;-webkit-border-radius: '.$border_radius.'px';
                if ( ! $is_feed ) {
                    $style['#sb_'.$label][] = $radius;
                } else {
                    if ($location == 'bottom') {
                        $radiusval = ': '.$border_radius.'px '.$border_radius.'px 0 0;';
                        $style["$dotboard .sb-content, $dotboard.sb-widget, $dotboard .sb-heading"][] = 'border-radius'.$radiusval.'-moz-border-radius'.$radiusval.'-webkit-border-radius'.$radiusval;
                    } else {
                        $style["$dotboard .sb-content, $dotboard.sb-widget"][] = $radius;
                        $style[$dotboard.' .toolbar'][] = 'border-radius: 0 0 '.$border_radius.'px '.$border_radius.'px;-moz-border-radius: 0 0 '.$border_radius.'px '.$border_radius.'px;-webkit-border-radius: 0 0 '.$border_radius.'px '.$border_radius.'px';
                    }
                }
            }
            if ( $is_wall )
                $style['#sb_'.$label][] = 'padding: 10px';
        }
        
        if ($is_feed) {
            if (@$attr['width'] != '')
                $style["$dotboard"][] = 'width: '.$attr['width'].'px';
        }
        
        if ( @$attr['height'] != '' && ! $is_feed ) {
            $style[$csskey][] = 'height: '.$attr['height'].'px';
            if ( ! $is_feed ) {
                $style[$csskey][] = 'overflow: scroll';
                if ( $is_timeline )
                    $style[$csskey][] = 'padding-right: 0';
                $style[$dotboard][] = 'padding-bottom: 30px';
            }
        }
        } // end no ajax
        
        if ( @$theme ) {
            $class['theme'] = $attr['theme'];
        }
        
    	if ( ! $order = $attr['order'] )
            $order = 'date';
        
        $target = '';
        // nofollow links
        if (@$attr['nofollow'])
            $target .= ' rel="nofollow"';
        
        // open links in new window
        if (@$attr['links'])
            $target .= ' target="_blank"';
        $this->target = $layoutobj->target = $target;
        if ( isset($slideshow) )
            $slidelayoutobj->target = $target;
        
        // use https
        $protocol = (@$attr['https']) ? 'https' : 'http';
        
        $output = $ss_output = '';
        if ( ! $ajax_feed)
            $output .= "\n<!-- PHP Social Stream By Axent Media -->\t";

        $GLOBALS['islive'] = false;
        if (@$attr['live']) {
            $GLOBALS['islive'] = true;
        }
        
        if ($GLOBALS['islive']) {
            // Live update need cache to be disabled
            $forceCrawl = true;
        } else {
            // If a cache time is set in the admin AND the "cache" folder is writeable, set up the cache.
            if ( $attr['cache'] > 0 && is_writable( SB_DIR . '/cache/' ) ) {
                $cache->cache_path = SB_DIR . '/cache/';
                $cache->cache_time = $attr['cache'] * 60;
        		$forceCrawl = false;
        	} else {
        		// cache is not enabled, call local class
                $forceCrawl = true;
        	}
        }
        
        // if is ajax request
        if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            if (@$_SESSION["$label-temp"]) {
            	$_SESSION[$label] = $_SESSION["$label-temp"];
                $_SESSION["$label-temp"] = array();
            }
            if (@$_REQUEST['action'] == "sb_liveupdate") {
                $_SESSION["$label-temp"] = @$_SESSION[$label];
                $_SESSION[$label] = array();
            }
        } else {
            $_SESSION[$label] = array();
            unset($_SESSION["$label-temp"]);
        }
        
        // Check which feeds are specified
        $feeds = array();
        foreach ( $this->feed_keys as $key ) {
            for ($i = 1; $i <= 5; $i++) {
        		if ( $keyitems = @$this->sboption[$key][$key.'_id_'.$i] ) {
                    foreach ($keyitems as $key2 => $eachkey) {
                        if ( (@$_REQUEST['action'] == "sb_loadmore" && @$_SESSION[$label]['loadcrawl']) && ! @$_SESSION[$label]['loadcrawl'][$key.$i.$key2])
                            $load_stop = true;
                        else
                            $load_stop = false;
                        if ( ! @$load_stop) {
                        if ( $eachkey != '') {
         			        if ( ! @$attr['tabable'] || $ajax_feed ) {
                                if ( $crawl_limit && $GLOBALS['crawled'] >= $crawl_limit )
                                    break;
                                if ( $feed_data = $this->get_feed( $key, $i, $key2, $eachkey, $results, $this->sboption[$key], $cache, $forceCrawl, $label ) ) {
                                    $feeds[$key][$i][$key2] = $feed_data;
                                    $filterItems[$key] = ($is_feed) ? '<span class="sb-hover sb-'.$key.$filterlabel.'" data-filter=".sb-'.$key.'"><i class="sb-micon sb-'.$key.'"></i></span>' : '<span class="sb-hover sb-'.$key.$filterlabel.'" data-filter=".sb-'.$key.'"><i class="sb-icon sb-'.$key.'"></i></span>';
                                }
                            } else {
                                $activeTab = '';
                                if ( @$attr['position'] == 'normal' || (@$attr['slide'] && ! @$attr['autoclose']) ) {
                                    if ( ! isset($fistTab) ) {
                                        if ( $feed_data = $this->get_feed( $key, $i, $key2, $eachkey, $results, $this->sboption[$key], $cache, $forceCrawl, $label ) ) {
                                            $feeds[$key][$i][$key2] = $feed_data;
                                        }
                                        $fistTab = true;
                                        $activeTab = ' active';
                                    }
                                }
                                
                                $fi = '
                    			<li class="'.$key.@$activeTab.'" data-feed="'.$key.'">';
                                if (@$attr['position'] == 'normal') {
                                    $fi .= '
                                    <span><i class="sb-icon sb-'.$key.'"></i></span>';
                                } else {
                                $fi .= '
                                    <i class="sb-icon sb-'.$key.'"></i>';
                                    if ( $location != 'bottom' )
                                        $fi .= ' <span>'.ucfirst($key).'</span>';
                                }
                    			$fi .= '</li>';
                                $filterItems[$key] = $fi;
                            }
                        }
                        }
                    }
                }
            }
        }

        // set timeline style class
        if ( $is_timeline ) {
            $class[] = ($attr['onecolumn'] == 'true') ? 'timeline onecol' : 'timeline';
            $class[] = 'animated';
        }
        
        if ( ! $ajax_feed) {
            if (@$attr['add_files']) {
                $cssfiles = $jsfiles = '';
                if ( ! $GLOBALS['enqueue']['general']) {
                    // add css files
                    $cssfiles .= '<link href="'.SB_PATH . 'public/css/colorbox.css" rel="stylesheet" type="text/css" />';
                    $cssfiles .= '<link href="'.SB_PATH . 'public/css/styles.min.css" rel="stylesheet" type="text/css" />';

                    // add js files
                    $jsfiles .= '<script type="text/javascript" src="'.SB_PATH . 'public/js/jquery-1.12.4.min.js"></script>';
                    $jsfiles .= '<script type="text/javascript" src="'.SB_PATH . 'public/js/sb-utils.js"></script>';
                    
                    $GLOBALS['enqueue']['general'] = true;
                }
                if ( $is_timeline ) {
                    if ( ! $GLOBALS['enqueue']['timeline']) {
                        $cssfiles .= '<link href="'.SB_PATH . 'public/css/timeline-styles.css" rel="stylesheet" type="text/css" />';
                        $jsfiles .= '<script type="text/javascript" src="'.SB_PATH . 'public/js/sb-timeline.js"></script>';
                        $GLOBALS['enqueue']['timeline'] = true;
                    }
                } else {
                    if ( $is_feed ) {
                        if (@$attr['carousel']) {
                            if ( ! $GLOBALS['enqueue']['carousel']) {
                                $cssfiles .= '<link href="'.SB_PATH . 'public/css/lightslider.css" rel="stylesheet" type="text/css" />';
                                $jsfiles .= '<script type="text/javascript" src="'.SB_PATH . 'public/js/sb-carousel.js"></script>';
                                $GLOBALS['enqueue']['carousel'] = true;
                            }
                        } else {
                            if ( ! $GLOBALS['enqueue']['rotating']) {
                                $jsfiles .= '<script type="text/javascript" src="'.SB_PATH . 'public/js/sb-rotating.js"></script>';
                                $GLOBALS['enqueue']['rotating'] = true;
                            }
                        }
                    } else {
                        if ( ! $GLOBALS['enqueue']['wall']) {
                            $jsfiles .= '<script type="text/javascript" src="'.SB_PATH . 'public/js/sb-wall.js"></script>';
                            $GLOBALS['enqueue']['wall'] = true;
                        }
                    }
                }
                $output .= $cssfiles . $jsfiles;
            }

            if ( @$style ) {
                $output .= '<style type="text/css">';
                if ( @$themeoption['custom_css'] )
                    $output .= $themeoption['custom_css']."\n";
                if ( @$mediaqueries )
                    $output .= $mediaqueries."\n";
                foreach ($style as $stKey => $stItem) {
                    $output .= $stKey.'{'.implode(';', $stItem).'}';
                }
                $output .= '</style>';
            }

            if ($is_wall || $is_timeline)
                $output .= '<div id="sb_'.$label.'">';
                
            if ( ! $is_feed && (@$attr['position'] == 'normal' || ! $is_timeline && ! @$attr['tabable'] ) ) {
                $search_box = '';
                if ( @$attr['filters'] && @$filterItems ) {
                    $search_box .= '
                        <span class="sb-hover filter-label active" data-filter="*" title="'.ss_lang( 'show_all' ).'"><i class="sb-icon sb-ellipsis-h"></i></span>
                        '.implode("\n", $filterItems);
                }
                if ( @$attr['filter_search'] )
                    $search_box .= '<input type="text" class="sb-search" placeholder="Search..." />';
                if ($search_box) {
                    $output .= '
            		<div class="filter-items sb-'.$themeoption['layout'].'">
                        '.$search_box.'
            		</div>';
                }
            }

            $output .= '<div' . @$attr_id . ' class="' . @implode(' ', $class) . '" data-columns>' . "\n";
            if ($is_wall) {
                $output .= '<div class="sb-gsizer"></div>';
            }
            if ( $is_feed ) {
                if (@$attr['tabable']) {
                        $minitabs = ( count($filterItems) > 5 ) ? ' minitabs' : '';
                        $output .= '
                        <div class="sb-tabs'.$minitabs.'">
                    		<ul class="sticky" data-nonce="'.ss_nonce_create( 'tabable', $label ).'">
                            '.implode("\n", $filterItems).'
                    		</ul>
                    	</div>';
                }
                if ( $is_feed ) {
                    if ( ! @$attr['tabable'] && @$attr['slide'] ) {
                        if ( $location == 'left' || $location == 'right' ) {
                            $opener_image = (@$themeoption[$typeoption]['opener_image']) ? $themeoption[$typeoption]['opener_image'] : SB_PATH.'public/img/opener.png';
                            $output .= '<div class="sb-opener'.@$active.'" title="'.@$attr['label'].'"><img src="'.$opener_image.'" alt="" /></div>';
                        } else {
                            $upicon = '<i class="sb-arrow"></i>';
                        }
                    }
                    if ( @$attr['showheader'] || ($location == 'bottom' && ! @$attr['tabable']) )
                        $output .= '<div class="sb-heading'.@$active.'">'.@$attr['label'].@$upicon.'</div>';
                }
                
                $content_style = (!@$attr['carousel']) ? ' style="height: '.$block_height.'px"' : '';
                $output .= '<div class="sb-content"'.$content_style.'>';
                $output .= '<ul id="ticker_'.$label.'">';
            }
        }

        // Parsing the combined feed items and create a unique feed
        if ( ! empty($feeds) ) {
            foreach ( $feeds as $feed_class => $feeditem ) {
                foreach ($feeditem as $i => $feeds2) {
                foreach ($feeds2 as $ifeed => $feed) {
                $inner = '';
                // Facebook
                if ( $feed_class == 'facebook' ) {
                    if (@$feed) {
                        $facebook_output = ( ! empty($this->sboption['facebook']['facebook_output']) ) ? ss_explode($this->sboption['facebook']['facebook_output']) : array('title' => true, 'thumb' => true, 'text' => true, 'comments' => true, 'likes' => true, 'user' => true, 'share' => true, 'info' => true);

                    foreach ($feed as $data) {
                    if (@$data) {
                        if ($i == 3 || $i == 4) {
                            $loadcrawl[$feed_class.$i.$ifeed] = $data->paging->cursors->after;
                            $data = $data->data;
                        }
                    foreach ($data as $entry) {
                        $url = $play = $text = $mediasize = '';
                        // create link
                        $idparts = @explode('_', @$entry->id);
                        if ( @count($idparts) > 1 )
                            $link = 'https://www.facebook.com/'.$idparts[0].'/posts/'.$idparts[1];
                        elseif (@$entry->from->id && @$entry->id)
                            $link = 'https://www.facebook.com/'.$entry->from->id.'/posts/'.$entry->id;
                        
                        if ( ! $link)
                            $link = @$entry->link;
                        if ( ! $link)
                            $link = @$entry->source;
                        
                        if ( $this->make_remove($link) ) {
                        
                        // body text
                        $content = array();
                        if (@$entry->message)
                            $content[] = $entry->message;
                        if (@$entry->description)
                            $content[] = $entry->description;
                        if (@$entry->story)
                            $content[] = $entry->story;
                        $content = implode(" \n", $content);
                        $text = (@$attr['words']) ? $this->word_limiter($content, $link) : @$this->format_text($content);
                        
                        // comments
                        $count = 0;
                        $comments_data = '';
                        $comments_count = 3;
                        if ( isset($this->sboption['facebook']['facebook_comments']) )
                            $comments_count = ( @$this->sboption['facebook']['facebook_comments'] > 0 ) ? $this->sboption['facebook']['facebook_comments'] : 0;
                        if ( ! empty($entry->comments->data) && $comments_count ) {
                            foreach ( $entry->comments->data as $comment ) {
                                $count++;
                                $comment_message = (@$attr['commentwords']) ? $this->word_limiter(nl2br($comment->message), @$link, true) : nl2br($comment->message);
                                $comment_picture = $protocol.'://graph.facebook.com/' . $comment->from->id . '/picture?type=square';
                                $comments_data .= '<span class="sb-meta sb-mention"><img class="sb-commentimg" src="'.$comment_picture.'" alt="" /><a href="https://www.facebook.com/' . $comment->from->id . '"'.$target.'>' . $comment->from->name . '</a> ' . $comment_message . '</span>';
                                if ( $count >= $comments_count ) break;
                            }
                        }
                        // likes
                        $count = 0;
                        $likes_data = '';
                        $likes_count = 5;
                        if ( isset($this->sboption['facebook']['facebook_likes']) )
                            $likes_count = ( @$this->sboption['facebook']['facebook_likes'] > 0 ) ? $this->sboption['facebook']['facebook_likes'] : 0;
                        if ( ! empty($entry->likes->data) && $likes_count ) {
                            foreach ( $entry->likes->data as $like ) {
                                $count++;
                                $like_title = (@$like->name) ? ' title="' . $like->name . '"' : '';
                                $likes_data .= '<img src="'.$protocol.'://graph.facebook.com/' . $like->id . '/picture?type=square"'.$like_title.' alt="">';
                                if ( $count >= $likes_count ) break;
                            }
                        }
                        
                        $meta = '';
                        if ($comments_data || $likes_data) {
                            $meta .= '
                            <span class="sb-metadata">';
                            if (@$facebook_output['comments'] && $comments_data)
                                $meta .= '
                                <span class="sb-meta">
                                    <span class="comments"><i class="sb-bico sb-comments"></i> '.ucfirst( ss_lang( 'comments' ) ).'</span>
                                </span>
                                ' . $comments_data;
                            if (@$facebook_output['likes'] && $likes_data)
                            $meta .= '
                                <span class="sb-meta">
                                    <span class="likes"><i class="sb-bico sb-star"></i> '.ucfirst( ss_lang( 'likes' ) ).'</span>
                                </span>
                                <span class="sb-meta item-likes">
                                    ' . $likes_data . '
                                </span>';
                            $meta .= '
                            </span>';
                        }
                        
                        $image_width = (@$this->sboption['facebook']['facebook_image_width']) ? $this->sboption['facebook']['facebook_image_width'] : 300;
                        $source = @$entry->picture;
                        if ($iframe) {
                            $url = $source;
                            $image_width_iframe = 800;
                        }
                        if ( ! empty($entry->images) ) {
                            if ($image_width) {
                                // get closest image width
                                $closest = null;
                                foreach ($entry->images as $image) {
                                    if ($closest === null || abs($image_width - $closest) > abs($image->width - $image_width)) {
                                        $closest = $image->width;
                                        $source = $image->source;
                                    }
                                }
                            }
                            // set iframe image
                            if ($iframe) {
                                $closest = null;
                                foreach ($entry->images as $image2) {
                                    if ($closest === null || abs($image_width_iframe - $closest) > abs($image2->width - $image_width_iframe)) {
                                        $closest = $image2->width;
                                        $url = $image2->source;
                                        $mediasize = $image2->width.','.$image2->height;
                                    }
                                }
                            }
                        } else {
                            // get or create thumb
                            if ($image_width > 180) {
                                if (@$entry->full_picture) {
                                    $urlArr = explode('&url=', $entry->full_picture);
                                    if ($urlfb = @$urlArr[1]) {
                                        if (stristr($urlfb, 'fbcdn') == TRUE || stristr($urlfb, 'fbstaging') == TRUE) {
                                            $source = $entry->full_picture."&w=$image_width&h=$image_width";
                                        } else {
                                            $source = $protocol.'://images1-focus-opensocial.googleusercontent.com/gadgets/proxy?container=focus&resize_w='.$image_width.'&refresh=3600&url='.$urlfb;
                                        }
                                    } else {
                                        $source = $entry->full_picture;
                                    }
                                } else {
                                    if ( $object_id = @$entry->object_id )
                                        $source = $protocol.'://graph.facebook.com/'.$object_id.'/picture?type=normal';
                                }
                            }
                            // set iframe image
                            if ($iframe) {
                                if (@$entry->full_picture) {
                                    $url = $entry->full_picture;
                                } else {
                                    if ( $object_id = @$entry->object_id )
                                        $url = $protocol.'://graph.facebook.com/'.$object_id.'/picture?type=normal';
                                }
                            }
                        }
                        
                        if (@$entry->type == 'video' || $i == 4) {
                            $play = true;
                            if ($iframe) {
                                $url = @$entry->source;
                                $mediasize = '640,460';
                            }
                        }
                        
                        switch ($i) {
                            case 3:
                                $type = 'image';
                                $type_icon = @$themeoption['type_icons'][4];
                            break;
                            case 4:
                                $type = 'video-camera';
                                $type_icon = @$themeoption['type_icons'][6];
                            default:
                                $type = 'pencil';
                                $type_icon = @$themeoption['type_icons'][0];
                            break;
                        }
                        
                        $thetime = (@$entry->created_time) ? $entry->created_time : $entry->updated_time;
                        $sbi = $this->make_timestr($thetime, $link);
                        $itemdata = array(
                        'title' => (@$entry->name) ? '<a href="' . $link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($entry->name) : $entry->name) . '</a>' : '',
                        'thumb' => (@$source) ? $source : '',
                        'thumburl' => $url,
                        'text' => @$text,
                        'meta' => @$meta,
                        'url' => @$link,
                        'iframe' => $iframe ? (@$entry->type == 'video' ? 'iframe' : 'icbox') : '',
                        'date' => $thetime,
                        'user' => array(
                            'name' => @$entry->from->name,
                            'url' => 'https://www.facebook.com/' . $entry->from->id,
                            'image' => $protocol.'://graph.facebook.com/' . $entry->from->id . '/picture?type=square',
                            // Status type
                            'status' => (@$entry->status_type) ? ss_lang($entry->status_type) : ''
                            ),
                        'type' => $type,
                        'play' => @$play,
                        'icon' => array(@$themeoption['social_icons'][0], $type_icon)
                        );
                        
							if (@$mediasize && ($iframe || isset($slideshow) ) )
								$itemdata['size'] = $mediasize;
                            $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $facebook_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = @$this->format_text($content);
                                if ($url)
                                    $itemdata['thumb'] = $url;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $facebook_output, $sbi);
                            }
                        }
                    } // end foreach
                    
                    if ($i != 3 && $i != 4) {
                        // facebook get last item date
                        $loadcrawl[$feed_class.$i.$ifeed] = strtotime($thetime)-1;
                    }
                    
                    } // end $data
                    }
                    }
                }
                // Twitter
        		elseif ( $feed_class == 'twitter' ) {
                    if (@$feed) {
                        if ($i == 3)
                            $feed = $feed->statuses;

                        $twitter_output = ( ! empty($this->sboption['twitter']['twitter_output']) ) ? ss_explode($this->sboption['twitter']['twitter_output']) : array('thumb' => true, 'text' => true, 'user' => true, 'share' => true, 'info' => true);
                        
                    foreach ( $feed as $data ) {
                        if ( isset($data->created_at) ) {
                        if (@$_SESSION[$label]['loadcrawl'][$feed_class.$i.$ifeed] == $data->id_str)
                            continue;
                        $link = $protocol.'://twitter.com/' . $data->user->screen_name . '/status/' . $data->id_str;
                        if ( $this->make_remove($link) ) {
                            $text = (@$attr['words']) ? $this->word_limiter($data->text) : @$this->format_text($data->text);
                            $text = $this->twitter_add_links($text);
                        
                        // get image
                        $url = $thumb = $mediasize = $play = '';
                        if ($mediaobj = @$data->entities->media[0]) {
                            $twitter_images = (@$this->sboption['twitter']['twitter_images']) ? $this->sboption['twitter']['twitter_images'] : 'small';
                            $media_url = (@$attr['https']) ? $mediaobj->media_url_https : $mediaobj->media_url;
                            $thumb = $media_url . ':' . $twitter_images;
                            if ($iframe) {
                                $url = $media_url . ':large';
                                $mediasize = $mediaobj->sizes->large->w.','.$mediaobj->sizes->large->h;
                            }
                        }
                        
                        // get video
                        if ($extmediaobj = @$data->extended_entities->media[0]) {
                            if (@$extmediaobj->type == 'video' || @$extmediaobj->type == 'animated_gif') {
                                $play = true;
                                if ($iframe) {
                                    if ($variants = @$extmediaobj->video_info->variants) {
                                        $lastvar = end($variants);
                                        $url = $lastvar->url;
                                        // add 30% to media size
                                        $mediasize = round($extmediaobj->sizes->large->w * 1.3).','.round($extmediaobj->sizes->large->h * 1.3);
                                    }
                                }
                            }
                        }
                        
                        $sbi = $this->make_timestr($data->created_at, $link);
                        $itemdata = array(
                        'thumb' => $thumb,
                        'thumburl' => $url,
                        'iframe' => $iframe ? (@$play ? 'iframe' : 'icbox') : '',
                        'text' => $text,
                        'share' => (@$twitter_output['share']) ? '
                        <span class="sb-share sb-tweet">
                            <a href="https://twitter.com/intent/tweet?in_reply_to=' . $data->id_str . '&via=' . $data->user->screen_name . '"'.$target.'><i class="sb-bico sb-reply"></i></a>
                            <a href="https://twitter.com/intent/retweet?tweet_id=' . $data->id_str . '&via=' . $data->user->screen_name . '"'.$target.'><i class="sb-bico sb-retweet"></i> ' . $data->retweet_count . '</a>
                            <a href="https://twitter.com/intent/favorite?tweet_id=' . $data->id_str . '"'.$target.'><i class="sb-bico sb-star-o"></i> ' . $data->favorite_count . '</a>
                        </span>' : null,
                        'url' => $link,
                        'date' => $data->created_at,
                        'user' => array(
                            'name' => '@'.$data->user->screen_name,
                            'url' => 'https://twitter.com/'.$data->user->screen_name,
                            'title' => $data->user->name,
                            'image' => (@$attr['https']) ? $data->user->profile_image_url_https : $data->user->profile_image_url
                            ),
                        'type' => 'pencil',
                        'play' => @$play,
                        'icon' => array(@$themeoption['social_icons'][1], @$themeoption['type_icons'][0])
                        );
                            
							if (@$mediasize && ($iframe || isset($slideshow) ) )
								$itemdata['size'] = $mediasize;
                            $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, @$twitter_output, $sbi);
                            if ( isset($slideshow) ) {
                                $text = @$this->format_text($data->text);
                                $itemdata['text'] = $this->twitter_add_links($text);
                                if ($url)
                                    $itemdata['thumb'] = $url;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, @$twitter_output, $sbi);
                            }
                        }
                        }
                    } // end foreach
                    
                    // twitter get last id
                    $loadcrawl[$feed_class.$i.$ifeed] = @$data->id_str;
                    }
        		}
                // Google+
                elseif ( $feed_class == 'google' ) {
                    $keyTypes = array( 'note' => array('pencil', 0), 'article' => array('edit', 1), 'activity' => array('quote-right', 2), 'photo' => array('image', 5), 'video' => array('video-camera', 6) );
                    $google_output = ( ! empty($this->sboption['google']['google_output']) ) ? ss_explode($this->sboption['google']['google_output']) : array('title' => true, 'thumb' => true, 'text' => true, 'stat' => true, 'user' => true, 'share' => true, 'info' => true);
                    
                    // google next page
                    $loadcrawl[$feed_class.$i.$ifeed] = @$feed->nextPageToken;
                    
                    if (@$feed->items) {
                    foreach ($feed->items as $item) {
                        $url = $play = $text = $textlong = $content = $contentlong = $image_url = $mediasize = '';
                        $link = @$item->url;
                        if ( $this->make_remove($link) ) {
                            // get text
                            if ($attachments = @$item->object->attachments[0]) {
                                $image_url = @$attachments->image->url;
                                if ($iframe && @$attachments->fullImage) {
                                    $url = @$attachments->fullImage->url;
                                    $mediasize = @$attachments->fullImage->width.','.@$attachments->fullImage->height;
                                }
                                if ($iframe && ! $mediasize) {
                                    $mediasize = @$attachments->image->width.','.@$attachments->image->height;
                                }
                                
                                if (@$attachments->objectType == 'photo') {
                                    if (@$attachments->displayName) {
                                        $text = (@$attr['words']) ? $this->word_limiter($attachments->displayName, $link) : $this->format_text($attachments->displayName);
                                        if ( isset($slideshow) )
                                            $textlong = @$this->format_text($attachments->displayName);
                                    }
                                } else {
                                    if (@$attachments->content)
                                        $content = (@$attr['words']) ? $this->word_limiter($attachments->content, $link) : @$this->format_text($attachments->content);
                                    $text = '<span class="sb-title"><a href="' . $attachments->url . '"'.$target.'>'.$attachments->displayName.'</a></span>'.@$content;
                                    
                                    if ( isset($slideshow) ) {
                                        if (@$attachments->content)
                                            $contentlong = @$this->format_text($attachments->content);
                                        $textlong = '<span class="sb-title"><a href="' . $attachments->url . '"'.$target.'>'.$attachments->displayName.'</a></span>'.@$contentlong;
                                    }
                                }
                                
                                if (@$attachments->objectType == 'video') {
                                    if (@$attachments->embed && $iframe) {
                                        $play = true;
                                        $url = $attachments->embed->url;
                                        // add 30% to media size
                                        $medias = explode(',', $mediasize);
                                        $mediasize = round($medias[0] * 1.3).','.round($medias[1] * 1.3);
                                    } else {
                                        $url = $image_url;
                                    }
                                }
                            } else {
                                $text = (@$attr['words']) ? $this->word_limiter($item->object->content, $link) : @$this->format_text($item->object->content);
                                if ( isset($slideshow) )
                                    $textlong = @$this->format_text($item->object->content);
                            }
                                
                        $title = (@$attr['titles'] ? $this->title_limiter($item->title) : $item->title);
                        
                        if ($title || $image_url || $text) {
                        $sbi = $this->make_timestr($item->updated, $link);
                        $itemdata = array(
                        'thumb' => (@$image_url) ? $image_url : '',
                        'thumburl' => $url,
                        'title' => '<a href="' . $link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($item->title) : $item->title) . '</a>',
                        'text' => $text,
                        'iframe' => $iframe ? (@$play ? 'iframe' : 'icbox') : '',
                        'meta' => (@$google_output['stat']) ? '
                        <span class="sb-text">
                            <span class="sb-meta">
                                <span class="plusones">+1s ' . $item->object->plusoners->totalItems . '</span>
                                <span class="shares"><i class="sb-bico sb-users"></i> ' . $item->object->resharers->totalItems . '</span>
                                <span class="comments"><i class="sb-bico sb-comment"></i> ' . $item->object->replies->totalItems . '</span>
                            </span>
                        </span>' : null,
                        'url' => @$link,
                        'date' => @$item->published,
                        'user' => array(
                            'name' => $item->actor->displayName,
                            'url' => $item->actor->url,
                            'image' => @$item->actor->image->url
                            ),
                        'type' => $keyTypes[$item->object->objectType][0],
                        'play' => @$play,
                        'icon' => array(@$themeoption['social_icons'][2], @$themeoption['type_icons'][$keyTypes[$item->object->objectType][1]])
                        );
                            
							if (@$mediasize && ($iframe || isset($slideshow) ) )
								$itemdata['size'] = $mediasize;
                            $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $google_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = $textlong;
                                if ($url)
                                    $itemdata['thumb'] = $url;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, @$google_output, $sbi);
                            }
                            }
                        }
                    } // end foreach
                    }
                }
                elseif ( $feed_class == 'tumblr' ) {
                    $keyTypes = array( 'text' => array('pencil', 0), 'quote' => array('quote-right', 2), 'link' => array('link', 3), 'answer' => array('reply', 1), 'video' => array('video-camera', 6), 'audio' => array('youtube-play', 7), 'photo' => array('image', 5), 'chat' => array('comment', 9) );
                    $tumblr_thumb = (@$this->sboption['tumblr']['tumblr_thumb']) ? $this->sboption['tumblr']['tumblr_thumb'] : '250';
                    $tumblr_video = (@$this->sboption['tumblr']['tumblr_video']) ? $this->sboption['tumblr']['tumblr_video'] : '500';
                    
                    // tumblr next page start
                    $total_posts = @$feed->response->total_posts;
                    $loadcrawl[$feed_class.$i.$ifeed] = (@$_SESSION[$label]['loadcrawl'][$feed_class.$i.$ifeed]) ? $_SESSION[$label]['loadcrawl'][$feed_class.$i.$ifeed] + $results : $results;
                    
                    // blog info
                    $blog = $feed->response->blog;
                    
                    if (@$feed->response->posts) {
                        $tumblr_output = (@$this->sboption['tumblr']['tumblr_output']) ? ss_explode($this->sboption['tumblr']['tumblr_output']) : array('title' => true, 'thumb' => true, 'text' => true, 'user' => true, 'share' => true, 'info' => true, 'tags' => false);
                    
                    foreach ($feed->response->posts as $item) {
                        $title = $thumb = $text = $textlong = $body = $object = $tags = $url = $mediasrc = $mediasize = '';
                        
                        $link = @$item->post_url;
                        if ( $this->make_remove($link) ) {
                        
                        // tags
                        if ( @$tumblr_output['tags'] ) {
                            if ( @$item->tags ) {
                                $tags = implode(', ', $item->tags);
                            }
                        }
                        
                        if ( @$item->title ) {
                            $title = '<a href="' . $link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($item->title) : $item->title) . '</a>';
                        }
                        
                        // set image
                        if ($photoItem = @$item->photos[0]) {
                            if (@$photoItem->alt_sizes) {
                                foreach ($photoItem->alt_sizes as $photo) {
                                    if ($photo->width == $tumblr_thumb)
                                        $thumb = $photo->url;
                                }
                            }
                            // set iframe image
                            if ($iframe) {
                                if ($original = @$photoItem->original_size) {
                                    $url = $mediasrc = $original->url;
                                    $mediasize = $original->width.','.$original->height;
                                }
                            }
                        }
                        
                        if ($item->type == 'photo') {
                            $body = @$item->caption;
                        }
                        elseif ($item->type == 'video') {
                            $url = (@$item->video_type == 'tumblr') ? @$item->video_url : @$item->permalink_url;
                            if (@$item->thumbnail_url)
                                $thumb = $item->thumbnail_url;
                                
                            if ($iframe) {
                                // set player
                                if (@$item->player) {
                                    foreach ($item->player as $player) {
                                        if ($player->width == $tumblr_video) {
                                            $object = $player->embed_code;
											if (@$original->height) {
												$player_height = round( ($player->width * $original->height) / $original->width );
												$mediasize = $player->width.','.$player_height;
											}
											break;
										}
                                    }
                                }
                            }
                            $body = @$item->caption;
                        }
                        elseif ( $item->type == 'audio') {
                            $tit = @$item->artist . ' - ' . @$item->album . ' - ' . @$item->id3_title;
                            $title = '<a href="' . $link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($tit) : $tit) . '</a>';
                            $thumb = @$item->player;
                            $body = @$item->caption;
                        }
                        elseif ( $item->type == 'link') {
                            $title = '<a href="' . $item->url . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($item->title) : $item->title) . '</a>';
                            if (@$item->excerpt)
                                $excerpt = $item->excerpt." \n";
                            $text = $body = @$excerpt.@$item->description;
                            if ( ! @$thumb)
                                $thumb = @$item->link_image;
                            if ( ! $url) {
								$url = $item->link_image;
								$mediasize = $item->link_image_dimensions->width.','.$item->link_image_dimensions->height;
							}
                        }
                        elseif ( $item->type == 'answer') {
                            $text = $body = @$item->question." \n".@$item->answer;
                        }
                        elseif ( $item->type == 'quote') {
                            if (@$item->source)
                                $source = $item->source;
                            $text = $textlong = '<span class="sb-title">'.@$item->text.'</span>'.@$source;
                        }
                        elseif ( $item->type == 'chat') {
                            $text = $body = @$item->body;
                        }
                        // type = text
                        else {
                            if ( @$item->body )
                                $text = $body = $item->body;
                            
                            // find img
                            if ( ! @$thumb) {
                                $thumbarr = $this->getsrc($body);
                                $thumb = $thumbarr['src'];
                            }
                        }
                        
                        if ($iframe) {
                            if ( ! @$url )
                                $url = @$thumb;
                        }
                        
                        if ( empty($text) )
                            $text = @$item->summary;
                        $text = (@$attr['words']) ? $this->word_limiter($text, $link) : @$this->format_text($text);
                        
                        if ( isset($slideshow) && ! empty($body) && ! @$textlong ) {
                            $textlong = @$this->format_text($body);
                        }
                        
                        $sbi = $this->make_timestr($item->timestamp, $link);
                        $itemdata = array(
                        'title' => @$title,
                        'thumb' => @$thumb,
                        'thumburl' => $url,
                        'iframe' => $iframe ? (@$item->type == 'video' ? 'iframe' : 'icbox') : '',
                        'text' => @$text,
                        'tags' => @$tags,
                        'url' => @$link,
                        'object' => @$object,
                        'date' => $item->date,
                        'user' => array(
                            'name' => $blog->name,
                            'title' => $blog->title,
                            'url' => $blog->url,
                            'image' => $protocol.'://api.tumblr.com/v2/blog/'.$blog->name.'.tumblr.com/avatar/64'
                            ),
                        'type' => $keyTypes[$item->type][0],
                        'icon' => array(@$themeoption['social_icons'][3], @$themeoption['type_icons'][$keyTypes[$item->type][1]])
                        );
							if (@$mediasize && ($iframe || isset($slideshow) ) )
								$itemdata['size'] = $mediasize;
                            $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $tumblr_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = $textlong;
                                $itemdata['object'] = @$object;
                                if ($mediasrc)
                                    $itemdata['thumb'] = $mediasrc;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $tumblr_output, $sbi);
                            }
                        }
                    }
                    }
                }
                elseif ( $feed_class == 'delicious' ) {
                    if (@$feed) {
                        $delicious_output = (@$this->sboption['delicious']['delicious_output']) ? ss_explode($this->sboption['delicious']['delicious_output']) : array( 'title' => true, 'text' => true, 'user' => true, 'share' => true, 'info' => true, 'tags' => false );
                    foreach ($feed as $item) {
                        $link = @$item->u;
                        if ( $this->make_remove($link) ) {
                        // tags
                        $tags = '';
                        if ( @$delicious_output['tags'] ) {
                            $tags = '';
                            if ( @$item->t ) {
                                $tags = implode(', ', $item->t);
                            }
                        }
                        
                        $sbi = $this->make_timestr($item->dt, $link);
                        $itemdata = array(
                        'title' => '<a href="' . $link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($item->d) : $item->d) . '</a>',
                        'text' => (@$attr['words']) ? $this->word_limiter(@$item->n, $link) : @$item->n,
                        'tags' => $tags,
                        'url' => $link,
                        'date' => $item->dt,
                        'user' => array('name' => $item->a),
                        'type' => 'pencil',
                        'icon' => array(@$themeoption['social_icons'][4], @$themeoption['type_icons'][0])
                        );
                        $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $delicious_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = @$this->format_text($item->n);
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $delicious_output, $sbi);
                            }
                        }
                    }
                    }
                }
                elseif ( $feed_class == 'pinterest' ) {
                    $pinterest_output = (@$this->sboption['pinterest']['pinterest_output']) ? ss_explode($this->sboption['pinterest']['pinterest_output']) : array('title' => true, 'thumb' => true, 'text' => true, 'user' => true, 'share' => true, 'info' => true);

                    $fcount = $ikey = 0;
                    $pinuser = @$feed[0]->data->user;
                    $pinuser_url = @$pinuser->profile_url;
                    $pinuser_image = str_replace('_30.', '_140.', @$pinuser->image_small_url);
                    if (@$attr['https']) {
                        $pinuser_url = str_replace('http:', 'https:', $pinuser_url);
                        $pinuser_image = str_replace('http:', 'https:', $pinuser_image);
                    }
                    
                    if ($items = @$feed[1]->channel->item)
                    foreach($items as $item) {
                        $link = @$item->link;
                        $pin = @$feed[0]->data->pins[$ikey];
                        $ikey++;
                        if ( $this->make_remove($link) ) {
                        $fcount++;
        
                        $cats = array();
                        if (@$item->category) {
                            foreach($item->category as $category) {
                                $cats[] = (string) $category;
                            }
                        }
                        
                        // fix the links in description
                        $pattern = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";
                        if (preg_match($pattern, @$pin->description, $url1)) {
                            $description = preg_replace($pattern, "https://www.pinterest.com$url1[0]", @$pin->description);
                        } else {
                            $description = @$pin->description;
                        }
                        
                        // find img
                        $mediasrc = $meta = $mediasize = '';
                        $image_width = (@$this->sboption['pinterest']['pinterest_image_width']) ? $this->sboption['pinterest']['pinterest_image_width'] : 237;
                        if ($thumbobj = @$pin->images->{'237x'}) {
                            $thumb = $thumbobj->url;
                            if (@$attr['https'])
                                $thumb = str_replace('http:', 'https:', $thumb);
                            $bigthumb = str_replace('237x', '736x', $thumb);
                            if ($image_width == '736')
                                $thumb = $bigthumb;
                            if ($iframe) {
                                $mediasrc = $bigthumb;
                                $newwidth = 450;
                                $newheight = round( ($newwidth * $thumbobj->height) / $thumbobj->width );
                                $mediasize = $newwidth.','.$newheight;
                            }
                        }
                        
                        if (@$pin->is_video && @$pin->embed && $iframe) {
                            $mediasrc = @$pin->embed->src;
                            $mediasize = (@$pin->embed->width && @$pin->embed->height) ? $pin->embed->width.','.$pin->embed->height : $newwidth.','.$newheight;
                        }

                        // add meta
                        if (@$pinterest_output['stat']) {
                            $meta .= '
                            <span class="sb-text">
                                <span class="sb-meta">
                                    <span class="shares"><i class="sb-bico sb-star-o"></i> ' . $pin->repin_count . ' '.ucfirst(ss_lang( 'repin' )).'</span>
                                    <span class="comments"><i class="sb-bico sb-thumbs-up"></i> ' . $pin->like_count . ' '.ucfirst(ss_lang( 'likes' )).'</span>
                                </span>
                            </span>';
                        }
                        
                        $sbi = $this->make_timestr($item->pubDate, $link);
                        $itemdata = array(
                        'title' => '<a href="' . @$pin->link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($item->title) : $item->title) . '</a>',
                        'text' => (string) (@$attr['words']) ? $this->word_limiter($description, $link) : $this->format_text($description),
                        'thumb' => $thumb,
                        'thumburl' => ($mediasrc) ? $mediasrc : $link,
                        'tags' => @implode(', ', $cats),
                        'url' => $link,
                        'iframe' => $iframe ? (@$pin->is_video ? 'iframe' : 'icbox') : '',
                        'date' => $item->pubDate,
                        'meta' => @$meta,
                        'user' => array(
                            'name' => @$pinuser->full_name,
                            'url' => @$pinuser_url,
                            'image' => @$pinuser_image
                            ),
                        'type' => 'pencil',
                        'play' => (@$pin->is_video) ? true : false,
                        'icon' => array(@$themeoption['social_icons'][5], @$themeoption['type_icons'][0])
                        );
							if (@$mediasize && ($iframe || isset($slideshow) ) )
								$itemdata['size'] = $mediasize;
                            $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $pinterest_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = $this->format_text($description);
                                if ($mediasrc)
                                    $itemdata['thumb'] = $mediasrc;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $pinterest_output, $sbi);
                            }
                            
                            if ( $fcount >= $results ) break;
                        }
                    }
                }
                elseif ( $feed_class == 'flickr' ) {
                    // flickr next page
                    $loadcrawl[$feed_class.$i.$ifeed] = @$feed->photos->page+1;
                    
                    if (@$feed->photos->photo) {
                        $flickr_output = (@$this->sboption['flickr']['flickr_output']) ? ss_explode($this->sboption['flickr']['flickr_output']) : array( 'title' => true, 'thumb' => true, 'text' => true, 'user' => true, 'share' => true, 'info' => true, 'tags' => false );
                        
                    foreach ($feed->photos->photo as $media) {
                        $link = 'https://flickr.com/photos/' . $media->owner . '/' . $media->id;
                        if ( $this->make_remove($link) ) {
                        $text = $image = $url = $tags = '';
                        
                        // tags
                        if ( @$flickr_output['tags'] ) {
                            if ( @$media->tags ) {
                                $tags = $media->tags;
                            }
                        }

                        if (@$attr['carousel'])
                            $text = (@$attr['words']) ? $this->word_limiter($media->title, $link) : $media->title;
                        
                        $flickr_thumb = (@$this->sboption['flickr']['flickr_thumb']) ? $this->sboption['flickr']['flickr_thumb'] : 'm';
                        $image = $protocol.'://farm' . $media->farm . '.staticflickr.com/' . $media->server . '/' . $media->id . '_' . $media->secret . '_' . $flickr_thumb . '.jpg';
                        $author_icon = $protocol.'://farm' . $media->iconfarm . '.staticflickr.com/' . $media->iconserver . '/buddyicons/' . $media->owner . '_s.jpg';
                        if ($iframe) {
                            $url = $protocol.'://farm' . $media->farm . '.staticflickr.com/' . $media->server . '/' . $media->id . '_' . $media->secret . '_c.jpg';
                            $mediasize = $media->width_c.','.$media->height_c;
                        }
                        
                        $mediadate = (@$media->dateadded) ? $media->dateadded : $media->dateupload;
                        $sbi = $this->make_timestr($mediadate, $link);
                        $itemdata = array(
                        'thumb' => $image,
                        'thumburl' => @$url,
                        'title' => '<a href="' . $link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($media->title) : $media->title) . '</a>',
                        'text' => $text,
                        'tags' => @$tags,
                        'iframe' => $iframe ? 'icbox' : '',
                        'url' => $link,
                        'date' => $media->datetaken,
                        'user' => array(
                            'name' => @$media->ownername,
                            'url' => $protocol.'://www.flickr.com/people/' . $media->owner . '/',
                            'image' => $author_icon
                            ),
                        'type' => 'image',
                        'icon' => array(@$themeoption['social_icons'][6], @$themeoption['type_icons'][5])
                        );
                            $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $flickr_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = $media->title;
                                $itemdata['size'] = @$mediasize;
                                if ($url)
                                    $itemdata['thumb'] = $url;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $flickr_output, $sbi);
                            }
                        }
                    }
                    }
                }
                // Instagram
                elseif ( $feed_class == 'instagram' ) {
                    $keyTypes = array( 'image' => array('camera', 5), 'video' => array('video-camera', 6) );
                    $instagram_output = (@$this->sboption['instagram']['instagram_output']) ? ss_explode($this->sboption['instagram']['instagram_output']) : array( 'title' => true, 'thumb' => true, 'text' => true, 'comments' => true, 'likes' => true, 'user' => true, 'share' => true, 'info' => true, 'tags' => false );
                    
                    // instagram next page
                    if ($i != 4) {
                        if ($i == 2) {
                            $next_id = @$feed->pagination->next_max_tag_id;
                        } else {
                            $next_id = @$feed->pagination->next_max_id;
                        }
                        $loadcrawl[$feed_class.$i.$ifeed] = $next_id;
                    }
                    
                    if (@$feed->data) {
                    foreach ($feed->data as $item) {
                        $link = $url = @$item->link;
                        if ( $this->make_remove($link) ) {
                        $thumb = $mediasrc = '';
                        $instagram_images = (@$this->sboption['instagram']['instagram_images']) ? $this->sboption['instagram']['instagram_images'] : 'low_resolution';
                        if (@$item->images) {
                            $thumb = $item->images->{$instagram_images}->url;
                        
                            // set iframe image
                            $instagram_images_iframe = 'standard_resolution';
                            if ($iframe) {
                                $itemimages = $item->images->{$instagram_images_iframe};
                                $url = $mediasrc = $itemimages->url;
                                $mediasize = $itemimages->width.','.$itemimages->height;
                            }
                        }
                        
                        if (@$item->type == 'video' && $iframe) {
                            $instagram_videos = (@$this->sboption['section_instagram']['instagram_videos']) ? $this->sboption['section_instagram']['instagram_videos'] : 'low_resolution';
                            $itemvideos = $item->videos->{$instagram_videos};
                            $url = $mediasrc = $itemvideos->url;
                            $mediasize = $itemvideos->width.','.$itemvideos->height;
                        }
                        
                        // tags
                        $tags = '';
                        if ( @$instagram_output['tags'] ) {
                            if ( @$item->tags ) {
                                $tags = implode(', ', $item->tags);
                            }
                        }
                        
                        // comments
                        $count = 0;
                        $comments_data = '';
                        $comments_count = 3;
                        if ( isset($this->sboption['instagram']['instagram_comments']) )
                            $comments_count = ( @$this->sboption['instagram']['instagram_comments'] > 0 ) ? $this->sboption['instagram']['instagram_comments'] : 0;
                        if ( ! empty($item->comments->data) && $comments_count ) {
                            foreach ( $item->comments->data as $comment ) {
                                $count++;
                                $comment_message = (@$attr['commentwords']) ? $this->word_limiter($comment->text, $link, true) : $comment->text;
                                $comments_data .= '<span class="sb-meta sb-mention"><img class="sb-commentimg" src="' . $comment->from->profile_picture . '" alt=""><a href="https://instagram.com/' . $comment->from->username . '"'.$target.'>' . $comment->from->full_name . '</a> ' . $comment_message . '</span>';
                                if ( $count >= $comments_count ) break;
                            }
                        }
                        // likes
                        $count = 0;
                        $likes_data = '';
                        $likes_count = 5;
                        if ( isset($this->sboption['instagram']['instagram_likes']) )
                            $likes_count = ( @$this->sboption['instagram']['instagram_likes'] > 0 ) ? $this->sboption['instagram']['instagram_likes'] : 0;
                        if ( ! empty($item->likes->data) && $likes_count ) {
                            foreach ( $item->likes->data as $like ) {
                                $count++;
                                $likes_data .= '<img src="' . $like->profile_picture . '" title="' . $like->full_name . '" alt="">';
                                if ( $count >= $likes_count ) break;
                            }
                        }
                        
                        $meta = $textlong = '';
                        if ($comments_data || $likes_data || @$instagram_output['comments'] || @$instagram_output['likes']) {
                            $meta .= '
                            <span class="sb-metadata">';
                            if (@$instagram_output['comments']) {
                                $meta .= '
                                    <span class="sb-meta">
                                        <span class="comments"><i class="sb-bico sb-comments"></i> ' . $item->comments->count . ' '.ss_lang( 'comments' ).'</span>
                                    </span>';
                                if ($comments_data)
                                    $meta .= $comments_data;
                            }
                            if (@$instagram_output['likes']) {
                            $meta .= '
                                <span class="sb-meta">
                                    <span class="likes"><i class="sb-bico sb-star"></i> ' . $item->likes->count . ' '.ss_lang( 'likes' ).'</span>
                                </span>';
                            if ($likes_data)
                                $meta .= '
                                <span class="sb-meta item-likes">
                                    ' . $likes_data . '
                                </span>';
                            }
                            $meta .= '
                            </span>';
                        }
                        
                        $text = (@$attr['words']) ? $this->word_limiter(@$item->caption->text, $link) : @$this->format_text($item->caption->text);
                        if ( isset($slideshow) )
                            $textlong = @$this->format_text($item->caption->text);
                            
                        // Add links to all hash tags
                        $htreplace = $htsearch = array();
                        if ( @$item->tags ) {
                            foreach ($item->tags as $hashtag) {
                                $htsearch[] = '#'.$hashtag;
                                $htreplace[] = '<a href="https://instagram.com/explore/tags/'.$hashtag.'/"'.$target.'>#'.$hashtag.'</a>';
                            }
                            if (@$htsearch) {
                                $text = str_ireplace($htsearch, $htreplace, $text);

                                if ( isset($slideshow) )
                                    $textlong = str_ireplace($htsearch, $htreplace, $textlong);
                            }
                        }
                        
                        // create item
                        $sbi = $this->make_timestr($item->created_time, $link);
                        $itemdata = array(
                        'thumb' => @$thumb,
                        'thumburl' => $url,
                        'iframe' => $iframe ? (@$item->type == 'video' ? 'iframe' : 'icbox') : '',
                        'text' => $text,
                        'meta' => @$meta,
                        'tags' => $tags,
                        'url' => $link,
                        'date' => $item->created_time,
                        'user' => array(
                            'name' => $item->user->username,
                            'title' => @$item->user->full_name,
                            'url' => 'https://instagram.com/'.$item->user->username.'/',
                            'image' => @$item->user->profile_picture
                            ),
                        'type' => @$keyTypes[$item->type][0],
                        'play' => (@$item->type == 'video') ? true : false,
                        'icon' => array(@$themeoption['social_icons'][7], @$themeoption['type_icons'][$keyTypes[$item->type][1]])
                        );
							if (@$mediasize && ($iframe || isset($slideshow) ) )
								$itemdata['size'] = $mediasize;
                            $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $instagram_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = $textlong;
                                if ($mediasrc)
                                    $itemdata['thumb'] = $mediasrc;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $instagram_output, $sbi);
                            }
                        }
                    } // end foreach
                    
                    // next page timestamp only for /media/search
                    if ( $i == 4 && ! @$next_id)
                        $loadcrawl[$feed_class.$i.$ifeed] = @$item->created_time;
                    }
                }
                elseif ( $feed_class == 'youtube' ) {
                    // youtube next page
                    $loadcrawl[$feed_class.$i.$ifeed] = @$feed->nextPageToken;
                    
                    $youtube_output = (@$this->sboption['youtube']['youtube_output']) ? ss_explode($this->sboption['youtube']['youtube_output']) : array('title' => true, 'thumb' => true, 'text' => true, 'user' => true, 'share' => true, 'info' => true);
                    
                    if (@$feed->items)
                    foreach ($feed->items as $item) {
                        $watchID = ($i == 3) ? @$item->id->videoId : @$item->snippet->resourceId->videoId;
                        $link = $protocol.'://www.youtube.com/watch?v='.$watchID;
                        $snippet = $item->snippet;
                        if ( $this->make_remove($link) ) {
                        $dateof = @$snippet->publishedAt;
                        $title = @$snippet->title;
                        $text = @$snippet->description;
                        $text = (@$attr['words']) ? $this->word_limiter(@$text, $link) : @$this->format_text($text);

                        $thumb = $mediasrc = $mediasize = '';
                        if ($iframe) {
                            $mediasrc = $protocol.'://www.youtube.com/embed/' . $watchID . '?rel=0&wmode=transparent';
                        }
                        $youtube_thumb = (@$this->sboption['youtube']['youtube_thumb']) ? $this->sboption['youtube']['youtube_thumb'] : 'medium';
                        $thumbnail = @$snippet->thumbnails->{$youtube_thumb};
                        if ( ! $thumbnail )
                            $thumbnail = @$snippet->thumbnails->{'medium'};
                        $thumb = @$thumbnail->url;
                        
						// user info
						$userdata = array(
							'name' => $snippet->channelTitle,
							'url' => 'https://www.youtube.com/channel/'.$snippet->channelId
						);
						if (@$feed->userInfo->thumbnails)
							$userdata['image'] = @$feed->userInfo->thumbnails->default->url;
                            
                        $sbi = $this->make_timestr($dateof, $link);
                        $itemdata = array(
                        'thumb' => $thumb,
                        'thumburl' => ($mediasrc) ? $mediasrc : $link,
                        'iframe' => $iframe ? 'iframe' : '',
                        'title' => '<a href="' . $link . '"'.$iframe.$target.'>' . (@$attr['titles'] ? $this->title_limiter($title) : $title) . '</a>',
                        'text' => $text,
                        'url' => $link,
                        'date' => $dateof,
                        'user' => $userdata,
                        'type' => 'youtube-play',
                        'play' => true,
                        'icon' => array(@$themeoption['social_icons'][8], @$themeoption['type_icons'][6])
                        );
							$mediasize = '640,460';
							if (@$mediasize && ($iframe || isset($slideshow) ) )
								$itemdata['size'] = $mediasize;
                            $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $youtube_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = @$this->format_text(@$snippet->description);
                                if ($mediasrc)
                                    $itemdata['thumb'] = $mediasrc;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $youtube_output, $sbi);
                            }
                        }
                    }
                }
                elseif ( $feed_class == 'vimeo' ) {
                    $vimeo_output = ( ! empty($this->sboption['vimeo']['vimeo_output']) ) ? ss_explode($this->sboption['vimeo']['vimeo_output']) : array('title' => true, 'thumb' => true, 'text' => true, 'user' => true, 'share' => true, 'info' => true);
                    
                    if (@$feed) {
                        // vimeo next page
                        $loadcrawl[$feed_class.$i.$ifeed] = @$feed->page+1;
                        
                        if ($data = @$feed->data)
                        foreach ($data as $item) {
                            $link = @$item->link;
                            if ( $this->make_remove($link) ) {
                                $thumb = $mediasrc = $mediasize = '';
                                $vimeo_thumb = (@$attr['vimeo_thumb']) ? $attr['vimeo_thumb'] : '295';
                                if ($pictures = @$item->pictures->sizes) {
                                    foreach ($pictures as $photo) {
                                        if ($photo->width == $vimeo_thumb) {
                                            $thumb = $photo->link;
                                            break;
                                        }
                                    }
                                }
                                
                                $title = $item->name;
                                $id = preg_replace('/\D/', '', $item->uri);
                                if ($iframe || $slideshow) {
                                    $url = $mediasrc = 'https://player.vimeo.com/video/'. $id;
                                    $mediasize = $item->width.','.$item->height;
                                } else {
                                    $url = $link;
                                }
                                
                                $datetime = (@$item->modified_time) ? @$item->modified_time : @$item->created_time;
                                $connections = @$item->metadata->connections;
                                $meta = '
                                <span class="sb-text">
                                    <span class="sb-meta">
                                        <span class="likes"><i class="sb-bico sb-thumbs-up"></i> ' . @$connections->likes->total . '</span>
                                        <span class="views"><i class="sb-bico sb-play-circle"></i> ' . @$item->stats->plays . '</span>
                                        <span class="comments"><i class="sb-bico sb-comment"></i> ' . @$connections->comments->total . '</span>
                                        <span class="duration"><i class="sb-bico sb-clock-o"></i> ' . @$item->duration . ' secs</span>
                                    </span>
                                </span>';
                                $user_name = @$item->user->name;
                                $user_url = @$item->user->link;
                                $user_image = @$item->user->pictures->sizes[1]->link;

                                $sbi = $this->make_timestr($datetime, $link);
                                $itemdata = array(
                                'thumb' => @$thumb,
                                'thumburl' => @$url,
                                'iframe' => $iframe ? 'iframe' : '',
                                'title' => '<a href="' . $link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($title) : $title) . '</a>',
                                'text' => (@$attr['words']) ? $this->word_limiter($item->description, $link) : $item->description,
                                'meta' => (@$vimeo_output['share']) ? $meta : null,
                                'url' => $link,
                                'date' => $datetime,
                                'user' => array(
                                    'name' => $user_name,
                                    'url' => $user_url,
                                    'image' => $user_image
                                    ),
                                'type' => 'video-camera',
                                'play' => true,
                                'icon' => array(@$themeoption['social_icons'][9], @$themeoption['type_icons'][6])
                                );
								if (@$mediasize && ($iframe || isset($slideshow) ) )
									$itemdata['size'] = $mediasize;
                                $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $vimeo_output, $sbi);
                                if ( isset($slideshow) ) {
                                    $itemdata['text'] = $item->description;
                                    $itemdata['thumb'] = $mediasrc;
                                    $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, @$vimeo_output, $sbi);
                                }
                            }
                        }
                    }
                }
                elseif ( $feed_class == 'stumbleupon' ) {
                    $stumbleupon_output = ( ! empty($this->sboption['stumbleupon']['stumbleupon_output']) ) ? ss_explode($this->sboption['stumbleupon']['stumbleupon_output']) :  array( 'title' => true, 'thumb' => true, 'text' => true, 'user' => true, 'share' => true, 'info' => true );
                    $stumbleupon_feeds = (@$this->sboption['stumbleupon']['stumbleupon_feeds']) ? ss_explode($this->sboption['stumbleupon']['stumbleupon_feeds']) : array( 'comments' => true, 'likes' => true );
                    $fcount = 0;
                    if (@$feed)
                    foreach ($feed as $dataKey => $data) {
                        if (@$stumbleupon_feeds[$dataKey]) {
                        $channel = $data->channel;
                        $items = ( $dataKey == 'likes' ) ? $channel->item : $data->item;
                        foreach($items as $item) {
                            $link = @$item->link;
                            if ( $this->make_remove($link) ) {
                            $fcount++;
                            
                            // find user
                            $pattern = ( $dataKey == 'likes' ) ? '/http:\/\/www.stumbleupon.com\/stumbler\/(\w+)/i' : '/http:\/\/www.stumbleupon.com\/stumbler\/(\w+)\/comments/i';
                            $replacement = '$1';
                            $user_name = preg_replace($pattern, $replacement, $channel->link);
                            
                            $thumb = '';
                            $text = '';
                            $image = array();
                            if ($description = (string) @$item->description) {
                                if (@$attr['words']) {
                                    $thumbarr = $this->getsrc($description);
                                    $thumb = $thumbarr['src'];
                                    $text = $this->word_limiter($description, $link);
                                }
                                else {
                                    $text = $description;
                                }
                            }

                            $sbi = $this->make_timestr($item->pubDate, $link);
                            $itemdata = array(
                            'thumb' => $thumb,
                            'title' => '<a href="' . $link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($item->title) : $item->title) . '</a>',
                            'text' => $text,
                            'url' => $link,
                            'date' => $item->pubDate,
                            'user' => array(
                                'name' => $user_name,
                                'url' => "http://www.stumbleupon.com/stumbler/$user_name",
                                'title' => @$channel->title
                                ),
                            'type' => ( $dataKey == 'likes' ) ? 'star-o' : 'comment-o',
                            'icon' => array(@$themeoption['social_icons'][10], @$themeoption['type_icons'][( $dataKey == 'likes' ) ? 8 : 9])
                            );
                                $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $stumbleupon_output, $sbi);
                                if ( isset($slideshow) ) {
                                    $itemdata['text'] = $description;
                                    $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $stumbleupon_output, $sbi);
                                }
                                if ( $fcount >= $results ) break;
                            }
                        }
                        }
                    }
                }
                elseif ( $feed_class == 'deviantart' ) {
                    $fcount = 0;
                    $channel = @$feed->channel;
                    if (@$channel->item)
                    foreach($channel->item as $item) {
                        $link = @$item->link;
                        if ( $this->make_remove($link) ) {
                        $fcount++;

                        $description = $item->children('media', true)->description;
                        
                        $sbi = $this->make_timestr($item->pubDate, $link);
                        $itemdata = array(
                        'thumb' => @$item->children('media', true)->thumbnail->{1}->attributes()->url,
                        'title' => '<a href="' . $link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($item->title) : $item->title) . '</a>',
                        'text' => (@$attr['words']) ? $this->word_limiter($description, $link) : $description,
                        'tags' => '<a href="' . $item->children('media', true)->category . '"'.$target.'>' . $item->children('media', true)->category->attributes()->label . '</a>',
                        'url' => $link,
                        'date' => $item->pubDate,
                        'user' => array(
                            'name' => $item->children('media', true)->credit->{0},
                            'url' => $item->children('media', true)->copyright->attributes()->url,
                            'image' => $item->children('media', true)->credit->{1}),
                        'type' => 'image',
                        'icon' => array(@$themeoption['social_icons'][11], @$themeoption['type_icons'][4])
                        );
                        $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $stumbleupon_output, $sbi);
                        if ( isset($slideshow) ) {
                            $itemdata['text'] = $description;
                            $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $stumbleupon_output, $sbi);
                        }
                        if ( $fcount >= $results ) break;
                        }
                    }
                }
                elseif ( $feed_class == 'rss' ) {
                    $rss_output = (@$this->sboption['rss']['rss_output']) ? ss_explode($this->sboption['rss']['rss_output']) : array('title' => true, 'thumb' => true, 'text' => true, 'user' => true, 'tags' => false, 'share' => true, 'info' => true);

                    $fcount = 0;
                    $MIMETypes = array('image/jpeg', 'image/jpg', 'image/gif', 'image/png');
                    if ( $channel = @$feed->channel ) { // rss
                        if (@$channel->item)
                        foreach($channel->item as $item) {
                            $link = @$item->link;
                            if ( $this->make_remove($link) ) {
                            $fcount++;
    
                            $thumb = $url = '';
                            if (@$item->children('media', true)->thumbnail)
                            foreach($item->children('media', true)->thumbnail as $thumbnail) {
                                $thumb = $thumbnail->attributes()->url;
                            }
                            if ( ! $thumb && @$item->children('media', true)->content) {
                                foreach($item->children('media', true)->content as $content) {
                                    $thumb = @$content->children('media', true)->thumbnail->attributes()->url;
                                    if ( @in_array($content->attributes()->type, $MIMETypes) )
                                        $url = @$content->attributes()->url;
                                }
                                if ( ! $thumb && $url) {
                                    $thumb = $url;
                                }
                            }
                            
                            if ( ! $thumb) {
                                if ( @in_array($item->enclosure->attributes()->type, $MIMETypes) )
                                    $thumb = @$item->enclosure->attributes()->url;
                            }
                            
                            if (@$item->category && @$rss_output['tags'])
                            foreach($item->category as $category) {
                                $cats[] = (string) $category;
                            }
                            
                            // set Snippet or Full Text
                            $text = $description = '';
                            if (@$this->sboption['rss']['rss_text'])
                                $description = $item->description;
                            else
                                $description = (@$item->children("content", true)->encoded) ? $item->children("content", true)->encoded : $item->description;

                            if (@$description) {
                                $description = preg_replace("/<script.*?\/script>/s", "", $description);
                                if (@$attr['words']) {
                                    if ( ! $thumb) {
                                        $thumbarr = $this->getsrc($description);
                                        $thumb = $thumbarr['src'];
                                    }
                                    $text = $this->word_limiter($description, $link);
                                } else {
                                    $text = $description;
                                }
                            }
                            if ($iframe) {
                                if ( ! $url)
                                    $url = (@$thumb) ? $thumb : '';
                            }
                            if (@$thumb && @$attr['https'])
                                $thumb = "https://images1-focus-opensocial.googleusercontent.com/gadgets/proxy?".http_build_query(array('container' => 'focus', 'refresh' => 3600, 'url' => $thumb));
                            
                            $sbi = $this->make_timestr($item->pubDate, $link);
                            $itemdata = array(
                            'thumb' => (@$thumb) ? $thumb : '',
                            'thumburl' => $url,
                            'title' => '<a href="' . $link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($item->title) : $item->title) . '</a>',
                            'text' => $text,
                            'tags' => @implode(', ', $cats),
                            'url' => $link,
                            'iframe' => $iframe ? 'icbox' : '',
                            'date' => $item->pubDate,
                            'user' => array(
                                'name' => $channel->title,
                                'url' => $channel->link,
                                'image' => @$channel->image->url
                                ),
                            'type' => 'pencil',
                            'icon' => array(@$themeoption['social_icons'][12], @$themeoption['type_icons'][0])
                            );
                            $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $rss_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = @$this->format_text($description);
                                if ($url)
                                    $itemdata['thumb'] = $url;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $rss_output, $sbi);
                            }
                            if ( $fcount >= $results ) break;
                            }
                        }
                    } elseif ( $entry = @$feed->entry ) { // atom
                        // get feed link
                        if (@$feed->link)
                        foreach($feed->link as $link) {
                            if (@$link->attributes()->rel == 'alternate')
                                $user_url = @$link->attributes()->href;
                        }
                        foreach($feed->entry as $item) {
                            $link = @$item->link[0]->attributes()->href;
                            if ( $this->make_remove($link) ) {
                            $fcount++;
    
                            $title = (string) @$item->title;
                            $thumb = $url = '';
                            if (@$item->media)
                            foreach($item->media as $thumbnail) {
                                $thumb = @$thumbnail->attributes()->url;
                            }
                            if ( ! $thumb && @$item->link) {
                                foreach($item->link as $linkitem) {
                                    if (@$linkitem->attributes()->rel == 'enclosure') {
                                        if ( in_array(@$linkitem->attributes()->type, $MIMETypes) )
                                            $thumb = @$content->attributes()->url;
                                    }
                                }
                            }
                            
                            $cats = '';
                            if (@$item->category && @$rss_output['tags']) {
                                foreach($item->category as $category) {
                                    $cats .= @$category->attributes()->term.', ';
                                }
                                $cats = rtrim($cats, ", ");
                            }

                            // set Snippet or Full Text
                            $text = $description = '';
                            if (@$this->sboption['rss']['rss_text']) {
                                $description = (string) $item->summary;
                            } else {
                                $content = (string) @$item->content;
                                $description = ($content) ? $content : (string) @$item->summary;
                            }
                            
                            if (@$description) {
                                if (@$attr['words']) {
                                    if ( ! $thumb) {
                                        $thumbarr = $this->getsrc($description);
                                        $thumb = $thumbarr['src'];
                                    }
                                    $text = $this->word_limiter($description, $link);
                                }
                                else {
                                    $text = $description;
                                }
                            }
                            if ($iframe)
                                $url = (@$thumb) ? $thumb : '';
                            if (@$thumb && @$attr['https'])
                                $thumb = "https://images1-focus-opensocial.googleusercontent.com/gadgets/proxy?".http_build_query(array('container' => 'focus', 'refresh' => 3600, 'url' => $thumb));
                            
                            $sbi = $this->make_timestr($item->published, $link);
                            $itemdata = array(
                            'thumb' => @$thumb,
                            'thumburl' => $url,
                            'title' => '<a href="' . $link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($title) : $title) . '</a>',
                            'text' => @$text,
                            'tags' => @$cats,
                            'url' => $link,
                            'iframe' => $iframe ? 'icbox' : '',
                            'date' => $item->published,
                            'user' => array(
                                'name' => $feed->title,
                                'url' => @$user_url,
                                'image' => @$feed->logo
                                ),
                            'type' => 'pencil',
                            'icon' => array(@$themeoption['social_icons'][12], @$themeoption['type_icons'][0])
                            );
                            $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $rss_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = @$this->format_text($description);
                                if ($url)
                                    $itemdata['thumb'] = $url;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $rss_output, $sbi);
                            }
                            
                            if ( $fcount >= $results ) break;
                            }
                        }
                    }
                }
                elseif ( $feed_class == 'soundcloud' ) {
                    $soundcloud_output = (@$this->sboption['soundcloud']['soundcloud_output']) ? ss_explode($this->sboption['soundcloud']['soundcloud_output']) : array('title' => true, 'text' => true, 'thumb' => true, 'user' => true, 'share' => true, 'info' => true, 'meta' => true, 'tags' => false);
                    if (@$feed)
                    foreach ($feed as $item) {
                        $link = @$item->permalink_url;
                        if ( $this->make_remove($link) ) {
                        // tags
                        $tags = '';
                        if ( @$soundcloud_output['tags'] ) {
                            if (@$item->tag_list)
                                $tags .= $item->tag_list;
                        }
                        
                        // convert duration to mins
                        $duration = '';
                        if (@$item->duration) {
                            $seconds = floor($item->duration / 1000);
                            $duration = floor($seconds / 60);
                        }
                        
                        $download = '';
                        if (@$item->download_url) {
                            $download .= '<span class="download"><i class="sb-bico sb-cloud-download"></i> ' . @$item->download_count . '</span>';
                        }
                        
                        $meta = '
                        <span class="sb-text">
                            <span class="sb-meta">
                                <span class="likes"><i class="sb-bico sb-thumbs-up"></i> ' . @$item->favoritings_count . '</span>
                                <span class="views"><i class="sb-bico sb-play-circle"></i> ' . @$item->playback_count . '</span>
                                <span class="comments"><i class="sb-bico sb-comment"></i> ' . @$item->comment_count . '</span>
                                <span class="duration"><i class="sb-bico sb-clock-o"></i> ' . @$duration . ' mins</span>
                                ' . $download . '
                            </span>
                        </span>';
                        
                        $sbi = $this->make_timestr($item->created_at, $link);
                        $itemdata = array(
                        'title' => '<a href="' . $link . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($item->title) : $item->title) . '</a>',
                        'text' => (@$attr['words']) ? $this->word_limiter(@$item->description, $link) : @$item->description,
                        'thumb' => (@$item->artwork_url) ? $item->artwork_url : '',
                        'tags' => $tags,
                        'url' => $link,
                        'meta' => (@$soundcloud_output['meta']) ? $meta : null,
                        'date' => $item->created_at,
                        'user' => array(
                            'name' => $item->user->username,
                            'url' => $item->user->permalink_url,
                            'image' => $item->user->avatar_url
                            ),
                        'type' => 'youtube-play',
                        'icon' => array(@$themeoption['social_icons'][13], @$themeoption['type_icons'][7])
                        );
                        $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $soundcloud_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = @$item->description;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $soundcloud_output, $sbi);
                            }
                        }
                    }
                }
                elseif ( $feed_class == 'vk' ) {
                    if (@$feed) {
                        $vk_output = (@$this->sboption['vk']['vk_output']) ? ss_explode($this->sboption['vk']['vk_output']) : array( 'thumb' => true, 'text' => true, 'stat' => true, 'user' => true, 'share' => true, 'info' => true );
                        
                    // vk next page start
                    $offset = @$feed->offset;
                    $loadcrawl[$feed_class.$i.$ifeed] = ($offset == 0) ? $results : $results + $offset;
                    
                    if ($groups = @$feed->response->groups) {
                        foreach ($feed->response->groups as $group) {
                            $groupdata['-'.$group->id] = $group;
                        }
                    }
                    if ($profiles = @$feed->response->profiles) {
                        foreach ($feed->response->profiles as $profile) {
                            $userdata[$profile->id] = $profile;
                        }
                    }
                    if (@$feed->response)
                    foreach ($feed->response->items as $entry) {
                        $link = $protocol.'://vk.com/wall'.@$entry->owner_id.'_'.@$entry->id;
                        if ( $this->make_remove($link) ) {
                        
                        // body text
                        $text = @$entry->text;
                        if ( ! $text) {
                            if (@$entry->copy_history)
                                $text = $entry->copy_history[0]->text;
                        }
                        if ( isset($slideshow) ) {
                            $textlong = @$this->format_text($text);
                            $textlong = preg_replace('/#([^\s]+)/', '<a href="'.$protocol.'://vk.com/feed?section=search&q=%23$1"'.$target.'>#$1</a>', $textlong);
                        }
                        $text = (@$attr['words']) ? @$this->word_limiter($text, $link) : @$this->format_text($text);
                        // Add links to all hash tags
                        $text = preg_replace('/#([^\s]+)/', '<a href="'.$protocol.'://vk.com/feed?section=search&q=%23$1"'.$target.'>#$1</a>', $text );
                        
                        // user info
                        $user = (@$userdata[$entry->from_id]) ? $userdata[$entry->from_id] : $groupdata[$entry->from_id];
                        $user_name = (@$user->name) ? $user->name : $user->first_name.' '.$user->last_name;
                        $user_image = $user->photo_50;
                        $user_url = ($user->screen_name) ? $protocol.'://vk.com/' . $user->screen_name : $protocol.'://vk.com/id' . $entry->from_id;
                        
                        // get image
                        $image_width = (@$this->sboption['vk']['vk_image_width']) ? $this->sboption['vk']['vk_image_width'] : '604';
                        $attachments = @$entry->attachments;
                        if ( ! $attachments) {
                            if (@$entry->copy_history)
                                $attachments = $entry->copy_history[0]->attachments;
                        }
                        $source = $iframe2 = $play = $url = $mediasrc = '';
                        if ( ! empty($attachments) ) {
                            if ($image_width) {
                                foreach ($attachments as $attach) {
                                    if ($attach->type == 'photo') {
                                        $photo_width = "photo_$image_width";
                                        if ( ! @$attach->photo->{$photo_width} ) {
                                            $source = $this->vk_get_photo(@$attach->photo);
                                        } else {
                                            $source = @$attach->photo->{$photo_width};
                                        }
                                        if ($iframe) {
                                            $iframe2 = $iframe;
                                            $photo_width_iframe = "photo_1280";
                                            if ( ! @$attach->photo->{$photo_width_iframe} ) {
                                                $url = $mediasrc = $this->vk_get_photo(@$attach->photo);
                                            } else {
                                                $url = $mediasrc = @$attach->photo->{$photo_width_iframe};
                                                if ($attach->photo->width)
                                                    $mediasize = $attach->photo->width.','.$attach->photo->height;
                                            }
                                        }
                                        break;
                                    } elseif ($attach->type == 'link') {
                                        $source = (@$attach->link->image_big) ? $attach->link->image_big : @$attach->link->image_src;
                                        $url = @$attach->link->url;
                                        break;
                                    } elseif ($attach->type == 'video') {
                                        $play = true;
                                        $source = ($image_width <= 130) ? @$attach->video->photo_130 : @$attach->video->photo_320;
                                        break;
                                    } elseif ($attach->type == 'doc') {
                                        $source = $this->vk_get_photo(@$attach->doc);
                                        break;
                                    }
                                }
                            }
                        }
                        
                        $meta = (@$vk_output['stat']) ? '
                        <span class="sb-text">
                            <span class="sb-meta">
                                <span class="likes"><i class="sb-bico sb-thumbs-up"></i>' . $entry->likes->count . '</span>
                                <span class="shares"><i class="sb-bico sb-retweet"></i> ' . $entry->reposts->count . '</span>
                                <span class="comments"><i class="sb-bico sb-comment"></i> ' . $entry->comments->count . '</span>
                            </span>
                        </span>' : null;
                        
                        $sbi = $this->make_timestr($entry->date, $link);
                        $itemdata = array(
                        'thumb' => (@$source) ? $source : '',
                        'thumburl' => $url,
                        'text' => @$text,
                        'meta' => @$meta,
                        'url' => @$link,
                        'iframe' => @$iframe2 ? 'icbox' : '',
                        'date' => $entry->date,
                        'user' => array(
                            'name' => @$user_name,
                            'url' => $user_url,
                            'image' => @$user_image
                            ),
                        'type' => 'pencil',
                        'play' => @$play,
                        'icon' => array(@$themeoption['social_icons'][14], ($i == 2) ? @$themeoption['type_icons'][4] : @$themeoption['type_icons'][0] )
                        );
                        $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $vk_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = @$textlong;
                                $itemdata['size'] = @$mediasize;
                                if ($mediasrc)
                                    $itemdata['thumb'] = $mediasrc;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $vk_output, $sbi);
                            }
                        }
                    } // end foreach
                    } // end $feed
                }
        		elseif ( $feed_class == 'linkedin' ) {
                    if (@$feed->values) {
                        $linkedin_output = ( ! empty($this->sboption['linkedin']['linkedin_output']) ) ? ss_explode($this->sboption['linkedin']['linkedin_output']) : array('title' => true, 'thumb' => true, 'text' => true, 'comments' => true, 'likes' => true, 'user' => true, 'share' => true, 'info' => true);
                    
                    // linkedin next page
                    $loadcrawl[$feed_class.$i.$ifeed] = (@$feed->_start) ? @$feed->_start + @$feed->_count : $results;
                    
                    foreach ( $feed->values as $data ) {
                        if ( isset($data->timestamp) ) {
                        
                        $updateKey = explode('-', $data->updateKey);
                        $link = 'https://www.linkedin.com/nhome/updates?topic='.$updateKey[2];
                        if ( $this->make_remove($link) ) {
                        
                        $url = $thumb = $mediasrc = $title = $longtext = '';
                        $updateContent = $data->updateContent;
                        $share = $updateContent->companyStatusUpdate->share;
                        
                        if (@$share->content->title) {
                            $titleurl = (@$share->content->shortenedUrl) ? $share->content->shortenedUrl : $link;
                            $title = '<a href="' . $titleurl . '"'.$target.'>' . (@$attr['titles'] ? $this->title_limiter($share->content->title) : $share->content->title) . '</a>';
                        }
                        
                        if (@$share->comment)
                            $longtext .= $share->comment;
                        if (@$share->content->description) {
                            if ($longtext) $longtext .= " \n";
                            $longtext .= $share->content->description;
                        }
                        $text = (@$attr['words']) ? $this->word_limiter($longtext) : @$this->format_text($longtext);
                        
                        // comments
                        $count = 0;
                        $comments_data = '';
                        $comments_count = ( @$this->sboption['linkedin']['linkedin_comments'] > 0 ) ? $this->sboption['linkedin']['linkedin_comments'] : 0;
                        if ($updateComments = $data->updateComments)
                        if ( ! empty($updateComments->values) && $comments_count ) {
                            foreach ( $updateComments->values as $comment ) {
                                if ( ! @$comment->comment)
                                    continue;
                                $count++;
                                $comment_message = (@$attr['commentwords']) ? $this->word_limiter(nl2br($comment->comment), @$link, true) : nl2br($comment->comment);
                                if (@$comment->company->name)
                                    $comment_title = $comment->company->name;
                                else
                                    $comment_title = @$comment->person->firstName.' '.@$comment->person->lastName;
                                $comment_user_url = (@$comment->company->id) ? 'https://www.linkedin.com/company/'.$comment->company->id : @$comment->person->siteStandardProfileRequest->url;
                                $comment_user_img = (@$comment->person->pictureUrl) ? '<img class="sb-commentimg" src="' . $comment->person->pictureUrl . '" alt="" />' : '';
                                $comments_data .= '<span class="sb-meta sb-mention">'.$comment_user_img.'<a href="' . $comment_user_url . '"'.$target.'>' . $comment_title . '</a> ' . $comment_message . '</span>';
                                if ( $count >= $comments_count ) break;
                            }
                        }
                        // likes
                        $count = 0;
                        $likes_data = '';
                        $likes_count = ( @$this->sboption['linkedin']['linkedin_likes'] > 0 ) ? $this->sboption['linkedin']['linkedin_likes'] : 0;
                        if (@$data->likes)
                        if ( ! empty($data->likes->values) && $likes_count ) {
                            $like_title = array();
                            foreach ( $data->likes->values as $like ) {
                                if ( ! @$like->person)
                                    continue;
                                $count++;
                                if (@$like->person->firstName && @$like->person->lastName)
                                    $like_title[] = $like->person->firstName.' '.$like->person->lastName;
                                if ( $count >= $likes_count ) break;
                            }
                            $likes_data .= implode(', ', $like_title);
                        }
                        
                        $meta = '';
                        if ($comments_data || $likes_data) {
                            $meta .= '
                            <span class="sb-metadata">';
                            if (@$linkedin_output['comments'] && $comments_data)
                                $meta .= '
                                <span class="sb-meta">
                                    <span class="comments"><i class="sb-bico sb-comments"></i> ' . @$updateComments->_total . ' '.ucfirst(ss_lang( 'comments' )).'</span>
                                </span>
                                ' . $comments_data;
                            if (@$linkedin_output['likes'] && $likes_data)
                            $meta .= '
                                <span class="sb-meta">
                                    <span class="likes"><i class="sb-bico sb-star"></i> ' . @$data->numLikes . ' '.ucfirst(ss_lang( 'likes' )).'</span>
                                </span>
                                <span class="sb-meta item-likes">
                                    ' . $likes_data . '
                                </span>';
                            $meta .= '
                            </span>';
                        }
                        
                        // get image
                        if (@$share->content) {
                            $submittedImageUrl = @$share->content->submittedImageUrl;
                            if ($submittedImageUrl) {
                                if (@$attr['https'])
                                    $submittedImageUrl = str_replace('http:', 'https:', $submittedImageUrl);
                                $thumb = $submittedImageUrl;
                            } elseif (@$share->content->thumbnailUrl)
                                $thumb = $share->content->thumbnailUrl;
                            
                            if ($iframe && @$submittedImageUrl) {
                                $url = $mediasrc = $submittedImageUrl;
                            } else {
                                if (@$share->content->shortenedUrl)
                                    $url = $share->content->shortenedUrl;
                            }
                        }
                        
                        $sbi = $this->make_timestr($data->timestamp, $link);
                        $itemdata = array(
                        'title' => $title,
                        'thumb' => $thumb,
                        'thumburl' => $url,
                        'iframe' => $iframe ? 'icbox' : '',
                        'text' => $text,
                        'url' => $link,
                        'meta' => @$meta,
                        'date' => $data->timestamp,
                        'user' => array(
                            'name' => $updateContent->company->name,
                            'url' => 'https://www.linkedin.com/company/'.$updateContent->company->id,
                            'image' => (@$feed->company->squareLogoUrl) ? $feed->company->squareLogoUrl : $feed->company->logoUrl
                            ),
                        'type' => 'pencil',
                        'icon' => array(@$themeoption['social_icons'][1], @$themeoption['type_icons'][0])
                        );
                        $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $linkedin_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = $longtext;
                                if ($mediasrc)
                                    $itemdata['thumb'] = $mediasrc;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $linkedin_output, $sbi);
                            }
                        }
                        }
                    } // end foreach
                    }
        		} // end linkedin
        		elseif ( $feed_class == 'vine' ) {
                    if (@$feed->data->records) {
                        $vine_output = ( ! empty($this->sboption['vine']['vine_output']) ) ? ss_explode($this->sboption['vine']['vine_output']) : array('title' => true, 'thumb' => true, 'text' => true, 'comments' => true, 'likes' => true, 'user' => true, 'share' => true, 'info' => true);
                    
                    // vine next page
                    $loadcrawl[$feed_class.$i.$ifeed] = @$feed->data->nextPage;
                    
                    foreach ( $feed->data->records as $data ) {
                        if ( isset($data->created) ) {
                        
                        $link = $data->permalinkUrl;
                        if ( $this->make_remove($link) ) {
                        
                        $url = $thumb = $object = $text = '';
                        if (@$data->description) {
                            $text = (@$attr['words']) ? $this->word_limiter($data->description) : @$this->format_text($data->description);
                            $text = preg_replace('/#([\\d\\w]+)/', '<a href="https://vine.co/tags/$1">$0</a>', $text);
                        }
                        
                        // comments
                        $count = 0;
                        $comments_data = '';
                        $comments_count = ( @$attr['comments'] > 0 ) ? $attr['comments'] : 0;
                        if ($comments = $data->comments)
                        if ( ! empty($comments->records) && $comments_count ) {
                            foreach ( $comments->records as $comment ) {
                                if ( ! @$comment->comment)
                                    continue;
                                $count++;
                                $comment_message = (@$attr['commentwords']) ? $this->word_limiter(nl2br($comment->comment), @$link, true) : nl2br($comment->comment);
                                $comment_user_img = (@$comment->avatarUrl) ? '<img class="sb-commentimg" src="' . $comment->avatarUrl . '" alt="" />' : '';
                                $comments_data .= '<span class="sb-meta sb-mention">'.$comment_user_img.'<a href="https://vine.co/u/' . $comment->userId . '"'.$target.'>' . @$comment->username . '</a> ' . $comment_message . '</span>';
                                if ( $count >= $comments_count ) break;
                            }
                        }
                        // likes
                        $count = 0;
                        $likes_data = '';
                        $likes_count = ( @$attr['likes'] > 0 ) ? $attr['likes'] : 0;
                        if (@$data->likes)
                        if ( ! empty($data->likes->records) && $likes_count ) {
                            $like_title = array();
                            foreach ( $data->likes->records as $like ) {
                                if ( ! @$like->username)
                                    continue;
                                $count++;
                                $like_title[] = '<a href="https://vine.co/u/'.@$like->userId.'" title="'.@$like->created.'"'.$target.'>'.$like->username.'</a>';
                                if ( $count >= $likes_count ) break;
                            }
                            $likes_data .= implode(', ', $like_title);
                        }
                        
                        $meta = '';
                        if ($comments_data || $likes_data) {
                            $meta .= '
                            <span class="sb-metadata">';
                            if (@$vine_output['comments'] && $comments_data)
                                $meta .= '
                                <span class="sb-meta">
                                    <span class="comments"><i class="sb-bico sb-comments"></i> ' . @$data->likes->count . ' '.ucfirst(ss_lang( 'comments' )).'</span>
                                </span>
                                ' . $comments_data;
                            if (@$vine_output['likes'] && $likes_data)
                            $meta .= '
                                <span class="sb-meta">
                                    <span class="likes"><i class="sb-bico sb-star"></i> ' . @$data->comments->count . ' '.ucfirst(ss_lang( 'likes' )).'</span>
                                </span>
                                <span class="sb-meta item-likes">
                                    ' . $likes_data . '
                                </span>';
                            $meta .= '
                            </span>';
                        }
                        
                        // get image
                        if (@$data->thumbnailUrl)
                            $thumb = (@$attr['https']) ? str_replace('http:', 'https:', $data->thumbnailUrl) : $data->thumbnailUrl;
                        
                        $url = $link;
                        if ($iframe && @$data->shareUrl) {
                            $object = '<iframe src="'.$data->shareUrl.'/embed/simple" width="600" height="600" frameborder="0"></iframe><script src="https://platform.vine.co/static/scripts/embed.js"></script>';
                            $play = true;
                        }
                        
                        $sbi = $this->make_timestr($data->created, $link);
                        $itemdata = array(
                        'thumb' => $thumb,
                        'thumburl' => $url,
                        'iframe' => $iframe ? 'iframe' : '',
                        'text' => $text,
                        'url' => $link,
                        'meta' => @$meta,
                        'date' => $data->created,
                        'user' => array(
                            'name' => $data->username,
                            'url' => 'https://vine.co/u/' . $data->userId,
                            'image' => (@$attr['https']) ? str_replace('http:', 'https:', $data->avatarUrl) : $data->avatarUrl
                            ),
                        'type' => 'play-circle',
                        'play' => @$play,
                        'icon' => array(@$themeoption['social_icons'][1], @$themeoption['type_icons'][0])
                        );
							$mediasize = '560,460';
							if (@$mediasize && ($iframe || isset($slideshow) ) )
								$itemdata['size'] = $mediasize;
                            $this->final[$sbi] = $layoutobj->create_item($feed_class, $itemdata, $attr, $vine_output, $sbi);
                            if ( isset($slideshow) ) {
                                $itemdata['text'] = $data->description;
                                if ($object)
                                    $itemdata['object'] = $object;
                                $this->finalslide[$sbi] = $slidelayoutobj->create_slideitem($feed_class, $itemdata, $attr, $vine_output, $sbi);
                            }
                        }
                        }
                    } // end foreach
                    }
        		} // end vine

				$final = $this->final;
				
                // each network sorting
                if ( ! empty($final) ) {
                    krsort($final);
                    reset($final);
                    $ifeedclass = $feed_class.$i.$ifeed;
                    
                    if ( ! empty($loadmore) ) {
                        // filter last items
                        if ( $lastloaditem = $loadmore[$ifeedclass] ) {
                            $loadremovefrom = array_search( $lastloaditem, array_keys($final) );
                            if ( ! @$loadremovefrom) $loadremovefrom = 0;
                            if ( empty($_SESSION[$label]['loadcrawl'][$ifeedclass]) )
                                $loadremovefrom++;
                            $final = array_slice($final, $loadremovefrom);
                        }
                    }

                    $ranking[key($final)] = $ifeedclass;
                    $finals[$ifeedclass] = $final;
                    $rankcount[$ifeedclass] = count($final);
                }
                $final = $this->final = array();
                
                } // end foreach
                }
            } // end foreach $feeds

            if ( @$ranking ) {
                // defining limits by recent basis
                krsort($ranking);
                $rsum = 0;
                $rnum = count($ranking);
                for ($i = 1; $i <= $rnum; $i++) {
                    $rsum += $i;
                }
                $i = $rnum;
                foreach ($ranking as $cfeed) {
                    $rank[$cfeed] = round( ($i * 100) / $rsum );
                    $i--;
                }
            }

            if ( @$rankcount ) {
                $maxcountkey = array_search(max($rankcount), $rankcount);
                foreach ($rankcount as $rkey => $rval) {
                    $fresults[$rkey] = @round($rank[$rkey] * $results / 100);
                }
                foreach ($rankcount as $rkey => $rval) {
                    if ( $fresults[$rkey] > $rval ) {
                        $diffrankcount = $fresults[$rkey] - $rval;
                        $fresults[$rkey] -= $diffrankcount;
                        $fresults[$maxcountkey] += $diffrankcount;
                    }
                }
            }
            
            if ( @$finals ) {
                // filnal sorting and adding
                foreach ($finals as $fkey => $fval) {
                    $fcount = 0;
                    // limit last result
                    foreach ($fval as $key => $val) {
                        $fcount++;
                        $final[$key] = $val;
                        $loadmore[$fkey] = $key;
                        if ( $fcount >= $fresults[$fkey] ) break;
                    }
                }

                if ( array_sum($rankcount) <= $results && ! $is_feed && ( ! $GLOBALS['islive'] || @$_REQUEST['action'] == "sb_loadmore" ) ) {
                    // set next pages if exist
                    foreach ($rankcount as $rkey => $rval) {
                        if (@$loadcrawl[$rkey])
                            $_SESSION[$label]['loadcrawl'][$rkey] = $loadcrawl[$rkey];
                        else
                            $_SESSION[$label]['loadcrawl'][$rkey] = null;
                    }
                }
                
                krsort($final);
                if ( $order == 'random' )
                    $final = ss_shuffle_assoc($final);
                foreach ($final as $key => $val) {
                    $output .= $val;
                    
                    if ( isset($slideshow) ) {
                        $ss_output .= $this->finalslide[$key];
                    }
                }
            } else {
                if ( empty($loadmore) )
                    $output_error = '<p class="sboard-nodata"><strong>PHP Social Stream:</strong> There is no feed data to display!</p>';
            }
        } else {
            if ( empty($loadmore) )
                $output_error = '<p class="sboard-nodata"><strong>PHP Social Stream: </strong>There is no feed to show or you are not connected to the world wide web!</p>';
        }

        if (@$attr['loadmore']) {
            $_SESSION[$label]['loadmore'] = $loadmore;
        }
        
        if ($ajax_feed && $is_feed) {
            if (@$output_error)
                $output .= $output_error;
        }
        
    	if ( ! $ajax_feed) {
            if ( $is_feed ) {
                if (@$output_error)
                    $output .= $output_error;
                $output .= "</ul></div>";
                
                if ( ! @$attr['carousel']) {
                    if (@$attr['autostart']) {
                        $play_none = ' style="display: none;"';
                    } else {
                        $pause_none = ' style="display: none;"';
                    }
                    $controls = (@$attr['controls']) ? '
                    <div class="control">
                        <span class="sb-hover" id="ticker-next-'.$label.'"><i class="sb-bico sb-wico sb-arrow-down"></i></span>
                        <span class="sb-hover" id="ticker-prev-'.$label.'"><i class="sb-bico sb-wico sb-arrow-up"></i></span>
                        <span class="sb-hover" id="ticker-pause-'.$label.'"'.@$pause_none.'><i class="sb-bico sb-wico sb-pause"></i></span>
                        <span class="sb-hover" id="ticker-play-'.$label.'"'.@$play_none.'><i class="sb-bico sb-wico sb-play"></i></span>
                    </div>' : '';
                    
                $filters = '';
                if ( ! @$attr['tabable'] && @$filterItems && ! empty($feeds) ) {
                    $filters = (@$attr['filters']) ? '
                    <div class="filter">
                        <span class="sb-hover active" data-filter="all"><i class="sb-bico sb-wico sb-ellipsis-h" title="'.ss_lang( 'show_all' ).'"></i></span>
                        '.implode("\n", $filterItems).'
                    </div>' : '';
                }
                
                if (@$attr['filters'] or @$attr['controls'])
                $output .= '
                <div class="toolbar">
                    '.$controls.'
                    '.$filters.'
                </div>'."\n";
            }
            }
        }
        
        if ($is_wall || $is_timeline) {
            if (@$output_error) {
                $output = str_replace(' timeline ', ' ', $output);
                $output .= $output_error;
            }
        }

        if ( ! $ajax_feed) {
        $output .= "</div>\n";
        if ( ( ! $is_feed && ! @$output_error ) && @$attr['loadmore'] )
            $output .= '<div class="sb-loadmore" data-nonce="'.ss_nonce_create( 'loadmore', $label ).'"><p>'.ss_lang( 'load_more' ).'</p></div>'."\n";
        if ($is_wall || $is_timeline)
            $output .= "</div>\n";

        $iframe_output = $iframe_slideshow = $iframe_media = '';
        if (@$attr['iframe'] == 'slide') {
            $iframe_output = $iframe_slideshow = '
                $(".sb-inline").colorbox({
                    inline:true,
                    rel:"sb-inline",
                    href: function(){
                      return $(this).data("href");
                    },
                    maxHeight:"95%",
					width:"85%",
                    current:"slide {current} of {total}",
                    onComplete: function() {
                        var href, attrwidth, aspectratio, newheight = "";
						var winCurrentWidth = $(window).width();
						var winCurrentHeight = $(window).height();
						if (winCurrentWidth >= 768) {
							href = $(this).data("href");
							thumbimg = $(href + " .sb-inner .sb-thumb img," + href + " .sb-inner .sb-thumb iframe");
							attrwidth = thumbimg.attr("width");
							if (!attrwidth) {
								sizearrY = thumbimg.height();
								sizearrX = thumbimg.width();
								if (sizearrY) {
								    var gapHeight = Math.round((winCurrentHeight * 5) / 100);
								    var currentHeight = winCurrentHeight-gapHeight-30;
    								if (currentHeight < sizearrY) {
    									var newheight = currentHeight;
    									
    									aspectratio = sizearrX * newheight;
    									newwidth = Math.round(aspectratio / sizearrY);
    									sizearrX = newwidth;
    									sizearrY = newheight;
    									
    									thumbimg.height(newheight);
    								} else {
    									var newheight = "500";
    								}
    								$(href + " .sb-inner .sb-body").innerHeight(newheight);
    								
    								if (thumbimg.height() > 500) {
    									thumbimg.height(newheight);
    								}
								}
								$(this).colorbox.resize({innerHeight:newheight});
							}
						}
                    },
                    onLoad:function(){
                        $(".sb-slide .sb-thumb").empty();
                        var sizestr, href, inner, type, media, size = "";
						var wsize = sb_getwinsize();
						var bheight = (wsize.newHeight < 500) ? wsize.newHeight : 500;
                        href = $(this).data("href");
                        inner = $(href + " .sb-inner");
                        type = inner.data("type");
                        if (type) {
                            media = inner.data("media");
                            size = inner.data("size");
                            sizearr = size.split(",");
                            sizearrX = sizearr[0];
                            sizearrY = sizearr[1];
							thumb = inner.children(".sb-thumb");
                            
							if ( (sizearrX && sizearrY) && (sizearrX > 400 || sizearrY > 400) ) {
								if (wsize.winCurrentWidth > 768) {
									if (sizearrY < 400) {
										thumb.width("50%");
										inner.children(".sb-body").width("50%").children(".sb-slide-footer").width("50%");
									}
									
									newConWidth = Math.round((wsize.newWidth * 70) / 100);
									if (wsize.currentHeight < sizearrY || newConWidth < sizearrX) {
										aspectratio = sizearrX * wsize.newHeight;
										sizearrX = Math.round(aspectratio / sizearrY);
										sizearrY = wsize.newHeight;
										
										if (sizearrX > newConWidth) {
											aspectratio = sizearrY * newConWidth;
											sizearrY = Math.floor(aspectratio / sizearrX);
											sizearrX = newConWidth;
											$(href + " .sb-inner .sb-body").innerHeight(sizearrY);
										} else {
											$(href + " .sb-inner .sb-body").innerHeight(wsize.newHeight);
										}
									} else {
										if (sizearrY && sizearrY > 400) {
											$(href + " .sb-inner .sb-body").innerHeight(sizearrY);
										}
									}
								} else {
									if (wsize.newWidth < sizearrX) {
										aspectratio = sizearrY * wsize.newWidth;
										sizearrY = Math.round(aspectratio / sizearrX);
										sizearrX = wsize.newWidth;
									}
								}
							} else {
								sizestr = "";
								thumb.width("50%");
								inner.children(".sb-body").width("50%").children(".sb-slide-footer").width("50%");
							}
							
                            if (type == "image") {
                                if ( (sizearrX && sizearrY) && (sizearrX > 400 || sizearrY > 400) ) {
                                    sizestr = " style=\'width:" + sizearrX + "px;height:" + sizearrY + "px\' width=\'" + sizearrX + "\' height=\'" + sizearrY + "\'";
                                    thumb.html("<img src=\'" + media + "\'" + sizestr + " alt=\'\'>");
                                } else {
                                    thumb.html("<span><img src=\'" + media + "\' class=\'sb-imgholder\' alt=\'\'></span>");
                                }
                            } else if (type == "video") {
                                if ( (sizearrX && sizearrY) && (sizearrX > 400 || sizearrY > 400) ) {
                                        sizestr = " style=\'width:" + sizearrX + "px;height:" + sizearrY + "px\' width=\'" + sizearr[0] + "\' height=\'" + sizearr[1] + "\'";
                                } else {
                                    sizestr = " width=\'560\' height=\'315\'";
                                }
                                var imedia = "<iframe" + sizestr + " src=\'" + media + "\' allowfullscreen=\'\' webkitallowfullscreen=\'\' mozallowfullscreen=\'\' autoplay=\'0\' wmode=\'opaque\' frameborder=\'0\'></iframe>";
								if (sizearr[1] && sizearr[1] > 400) {
									thumb.html(imedia);
								} else {
									thumb.html("<span>" + imedia + "</span>");
									$(href + " .sb-inner .sb-body").innerHeight(bheight);
								}
                            } else {
                                if (sizearrY && sizearrY > 400) {
									thumb.html(media);
								} else {
									thumb.html("<span>" + media + "</span>");
									if (wsize.winCurrentWidth > 768)
										$(href + " .sb-inner .sb-body").innerHeight(bheight);
								}
                            }
                        } else {
							$(href + " .sb-inner .sb-body").innerHeight(bheight);
						}
                    },
                    onClosed:function(){ $(".sb-slide .sb-thumb").empty(); }
                });';
            } else {
                $iframe_output = $iframe_media = '
				$(".sboard .sb-thumb .iframe").colorbox({
					iframe: true,
                    maxWidth: "85%",
                    maxHeight: "95%",
					width: function() {
                        var size = $(this).data("size");
                        if (size) {
                            sizearr = size.split(",");
				            return parseInt(sizearr[0])+10;
                        } else {
                            return 640;
                        }
					},
					height: function() {
                        var size = $(this).data("size");
                        if (size) {
                            sizearr = size.split(",");
                            return parseInt(sizearr[1])+10;
                        } else {
                            return 460;
                        }
					},
					onComplete: function() {
						var size = $(this).data("size");
                        if (size) {
    						var sizearr = size.split(",");
    						var iframebox = $( "#cboxLoadedContent iframe" );
    						if (iframebox.length) {
    							iframebox.attr("width", sizearr[0]).attr("height", sizearr[1]);
    						}
                        }
					}
				});
				$(".sboard .sb-thumb .icbox").colorbox({photo:true, maxWidth:"95%", maxHeight:"95%"});
				$(".sboard .sb-thumb .inline").colorbox({inline:true, maxWidth:"95%", maxHeight:"95%"});';
            }
        
        // Lazy load images
        $lazyload_output = '
			$(".sb-thumb img").lazyload({
				effect: "fadeIn",
				skip_invisible: true,
				placeholder: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iLTQzIC00MyAxMjQgMTI0IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHN0cm9rZT0iI2ZmZiI+ICAgIDxnIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+ICAgICAgICA8ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxIDEpIiBzdHJva2Utd2lkdGg9IjIiPiAgICAgICAgICAgIDxjaXJjbGUgc3Ryb2tlLW9wYWNpdHk9Ii41IiBjeD0iMTgiIGN5PSIxOCIgcj0iMTgiLz4gICAgICAgICAgICA8cGF0aCBkPSJNMzYgMThjMC05Ljk0LTguMDYtMTgtMTgtMTgiPiAgICAgICAgICAgICAgICA8YW5pbWF0ZVRyYW5zZm9ybSAgICAgICAgICAgICAgICAgICAgYXR0cmlidXRlTmFtZT0idHJhbnNmb3JtIiAgICAgICAgICAgICAgICAgICAgdHlwZT0icm90YXRlIiAgICAgICAgICAgICAgICAgICAgZnJvbT0iMCAxOCAxOCIgICAgICAgICAgICAgICAgICAgIHRvPSIzNjAgMTggMTgiICAgICAgICAgICAgICAgICAgICBkdXI9IjFzIiAgICAgICAgICAgICAgICAgICAgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiLz4gICAgICAgICAgICA8L3BhdGg+ICAgICAgICA8L2c+ICAgIDwvZz48L3N2Zz4="
			});';
            
        // loadmore ajax function
        $more_output = '';
        if (@$attr['loadmore']) {
        	$more_output = '
                $("#sb_'.$label.'").on("click", ".sb-loadmore", function() {
                  lmnonce = $(this).attr("data-nonce");';
                $more_output .= "$('#sb_".$label." .sb-loadmore').html('<p class=\"sb-loading\">&nbsp;</p>');";
                $more_output .= '
                  $.ajax({
                    type: "post",
                    url: "'.SB_PATH.'ajax.php",
                    data: {action: "sb_loadmore", attr: '.$attr_ajax.', nonce: lmnonce, label: "'.$label.'"},
                    cache: false
                    })
                    .done(function( response ) {
                        /* append and lay out items */';
                    if ( $is_wall ) {
                        $more_output .= '
                        var $items = $(response);
                        $wall.append( $items ).isotope( "appended", $items );
                        $(window).trigger("scroll");';
    				} else {
    				    $more_output .= '
    				    $("#timeline_'.$label.'").append(response);';
    				}
                    $more_output .= $lazyload_output . $iframe_output . '
                        $("#sb_'.$label.' .sb-loadmore").html("<p>'.ss_lang( 'load_more' ).'</p>");
                    })
                    .fail(function() {
                        alert( "Problem reading the feed data!" );
                    });
                });';
        }
        
        $output .= '
        <script type="text/javascript">
            $(document).ready(function($) {
				function sb_getwinsize() {
					var wsize = {
						winCurrentWidth: $(window).width(),
						newWidth: 0,
						winCurrentHeight: $(window).height(),
						newHeight: 0
					};
					var gapWidth = Math.round((wsize.winCurrentWidth * 15) / 100);
					var currentWidth = wsize.winCurrentWidth-gapWidth;
					wsize.newWidth = currentWidth-9;
					
					var gapHeight = Math.round((wsize.winCurrentHeight * 5) / 100);
					var currentHeight = wsize.winCurrentHeight-gapHeight;
					wsize.newHeight = currentHeight-30;
					return wsize;
				}';
             
        $ticker_id_t = '';
        if ( $is_feed ) {
            if (@$attr['carousel']) {
                $cs_auto = (@$attr['cs_auto']) ? 'slider.play();' : '';
                if ( ! @is_array($attr['cs_item']) )
                    $attr['cs_item'] = $setoption['carouselsetting']['cs_item'];
            $output .= '
    			var slider = $("#ticker_'.$label.'").lightSlider({
                    item: '.@$attr['cs_item'][0].',
                    autoWidth: '.@$attr['autoWidth'].',
                    slideMove: '.@$attr['slideMove'].',
                    slideMargin: '.@$attr['slideMargin'].',
                    mode: "slide",
                    pauseOnHover: true,
                    auto: '.(@$attr['cs_auto'] ? 'true' : 'false').',
                    loop: '.(@$attr['cs_loop'] ? 'true' : 'false').',
                    controls: '.(@$attr['cs_controls'] ? 'true' : 'false').',
                    rtl: '.@$attr['cs_rtl'].',
                    pager: '.(@$attr['cs_pager'] ? 'true' : 'false').',
                    speed: '.@$attr['cs_speed'].',
                    pause: '.@$attr['cs_pause'].',
                    responsive : [
                        {
                            breakpoint:960,
                            settings: {
                                item: '.@$attr['cs_item'][1].'
                              }
                        },
                        {
                            breakpoint:768,
                            settings: {
                                item: '.@$attr['cs_item'][2].'
                              }
                        },
                        {
                            breakpoint:600,
                            settings: {
                                item: '.@$attr['cs_item'][3].'
                              }
                        },
                        {
                            breakpoint:480,
                            settings: {
                                item: '.@$attr['cs_item'][4].'
                              }
                        }
                    ],
					onBeforeNextSlide: function (el, scene) {
						var slidetotal = el.getTotalSlideCount();
						var vsnum = el.find(".clone.left").length;
						for (i = 0; i < 4; i++) {
							var inum = scene+vsnum+i;
							if (inum > slidetotal)
								inum = inum - slidetotal;
							var lielem = $("#ticker_'.$label.' li").eq(inum).find(".sb-thumb .sb-crop");
							if (typeof lielem.attr("data-original") !== "undefined" && lielem.attr("data-original") !== null)
								$( "a[data-original=\'"+lielem.attr("data-original")+"\']" ).trigger("appear");
						}
					},
					onBeforePrevSlide: function (el, scene) {
						var slidetotal = el.getTotalSlideCount();
						for (i = 0; i < 4; i++) {
							var inum = scene-i;
							if (inum < 0)
								inum = inum + slidetotal;
							var lielem = $("#ticker_'.$label.' li").eq(inum).find(".sb-thumb .sb-crop");
							if (typeof lielem.attr("data-original") !== "undefined" && lielem.attr("data-original") !== null)
								$( "a[data-original=\'"+lielem.attr("data-original")+"\']" ).trigger("appear");
						}
					}
                });
                ' . $cs_auto . '
				$(".sb-thumb .sb-crop").lazyload({
					effect: "fadeIn",
					skip_invisible: true,
					threshold: '.$block_height.',
					placeholder: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iLTIzIC0yMyA4NCA4NCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiBzdHJva2U9IiNmZmYiPiAgICA8ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPiAgICAgICAgPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMSAxKSIgc3Ryb2tlLXdpZHRoPSIyIj4gICAgICAgICAgICA8Y2lyY2xlIHN0cm9rZS1vcGFjaXR5PSIuNSIgY3g9IjE4IiBjeT0iMTgiIHI9IjE4Ii8+ICAgICAgICAgICAgPHBhdGggZD0iTTM2IDE4YzAtOS45NC04LjA2LTE4LTE4LTE4Ij4gICAgICAgICAgICAgICAgPGFuaW1hdGVUcmFuc2Zvcm0gICAgICAgICAgICAgICAgICAgIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgICAgICAgICAgICAgICAgICAgIHR5cGU9InJvdGF0ZSIgICAgICAgICAgICAgICAgICAgIGZyb209IjAgMTggMTgiICAgICAgICAgICAgICAgICAgICB0bz0iMzYwIDE4IDE4IiAgICAgICAgICAgICAgICAgICAgZHVyPSIxcyIgICAgICAgICAgICAgICAgICAgIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIi8+ICAgICAgICAgICAgPC9wYXRoPiAgICAgICAgPC9nPiAgICA8L2c+PC9zdmc+"
				});';
            }

            if ( ! @$attr['carousel']) {
                $ticker_id = '#ticker_'.$label;
                
				$ticker_lazyload_output = '
				$(".sb-thumb img").lazyload({
					effect: "fadeIn",
					skip_invisible: true,
					container: $("'.$ticker_id.'"),
					threshold: '.($block_height * 2).',
					failure_limit: '.$results.',
					placeholder: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iLTQzIC00MyAxMjQgMTI0IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHN0cm9rZT0iI2ZmZiI+ICAgIDxnIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+ICAgICAgICA8ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxIDEpIiBzdHJva2Utd2lkdGg9IjIiPiAgICAgICAgICAgIDxjaXJjbGUgc3Ryb2tlLW9wYWNpdHk9Ii41IiBjeD0iMTgiIGN5PSIxOCIgcj0iMTgiLz4gICAgICAgICAgICA8cGF0aCBkPSJNMzYgMThjMC05Ljk0LTguMDYtMTgtMTgtMTgiPiAgICAgICAgICAgICAgICA8YW5pbWF0ZVRyYW5zZm9ybSAgICAgICAgICAgICAgICAgICAgYXR0cmlidXRlTmFtZT0idHJhbnNmb3JtIiAgICAgICAgICAgICAgICAgICAgdHlwZT0icm90YXRlIiAgICAgICAgICAgICAgICAgICAgZnJvbT0iMCAxOCAxOCIgICAgICAgICAgICAgICAgICAgIHRvPSIzNjAgMTggMTgiICAgICAgICAgICAgICAgICAgICBkdXI9IjFzIiAgICAgICAgICAgICAgICAgICAgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiLz4gICAgICAgICAgICA8L3BhdGg+ICAgICAgICA8L2c+ICAgIDwvZz48L3N2Zz4="
				});';
                
                $output .= '
				function sb_tickerlazyload() {
					var lielem = $("'.$ticker_id.' li:last-child");
					var lix = lielem.index();
					for (i = 0; i < 4; i++) {
						var inum = lix-i;
						var imgelem = $("'.$ticker_id.' li").eq(inum).find(".sb-thumb img");
						if (typeof imgelem.attr("data-original") !== "undefined" && imgelem.attr("data-original") !== null)
							$( "img[data-original=\'"+imgelem.attr("data-original")+"\']" ).trigger("appear");
					}
				}
                
                var $sbticker = $("'.$ticker_id.'").newsTicker({
                    row_height: '.$block_height.',
                    max_rows: 1,
                    speed: '.@$attr['rotate_speed'].',
                    duration: '.@$attr['duration'].',
                    direction: "'.@$attr['direction'].'",
                    autostart: '.@$attr['autostart'].',
                    pauseOnHover: '.@$attr['pauseonhover'].',
                    prevButton: $("#ticker-prev-'.$label.'"),
                    nextButton: $("#ticker-next-'.$label.'"),
                    stopButton: $("#ticker-pause-'.$label.'"),
                    startButton: $("#ticker-play-'.$label.'"),
                    start: function() {
                    	$("#timeline_'.$label.' #ticker-pause-'.$label.'").show();
                        $("#timeline_'.$label.' #ticker-play-'.$label.'").hide();
                    },
                    stop: function() {
                    	$("#timeline_'.$label.' #ticker-pause-'.$label.'").hide();
                        $("#timeline_'.$label.' #ticker-play-'.$label.'").show();
                    },
					movingUp: function() {
						$("'.$ticker_id.'").trigger("scroll");
					},
					movingDown: function() {
						sb_tickerlazyload();
					}
                });';
                if (@$attr['tabable'] && @$attr['autoclose'] ) {
				    $output .= '$sbticker.newsTicker("pause");';
				}
                $output .= $ticker_lazyload_output . '
				sb_tickerlazyload();';
                
                // Filtering rotating feed
                if ( ! @$attr['tabable'] && @$attr['filters'] ) {
                $output .= "
                $('#timeline_$label .filter span').click(function() {
            		/* fetch the class of the clicked item */
            		var ourClass = $(this).data('filter');
            		
            		/* reset the active class on all the buttons */
            		$('#timeline_$label .filter span').removeClass('active');
            		/* update the active state on our clicked button */
            		$(this).addClass('active');
            		
            		if (ourClass == 'all') {
            			/* show all our items */
            			$('$ticker_id').children('li.sb-item').show();
            		} else {
            			/* hide all elements that don't share ourClass */
            			$('$ticker_id').children('li:not(' + ourClass + ')').fadeOut('fast');
            			/* show all elements that do share ourClass */
            			$('$ticker_id').children('li' + ourClass).fadeIn('fast');
            		}
            		return false;
            	});";
                }
            }
             
            if ( @$attr['slide'] && ! (@$attr['tabable'] && @$attr['position'] == 'normal') ) {
                if ( $location == 'left' || $location == 'right' ){
                    $getsizeof = 'Width';
                    $opener = 'sb-opener';
                    $padding = '';
                } else {
                    $getsizeof = 'Height';
                    $opener = 'sb-heading';
                    $padding = ( @$attr['showheader'] || ($location == 'bottom' && ! @$attr['tabable']) ) ? ' - 30' : '';
                }
                $openid = (@$attr['tabable']) ? "#timeline_$label .sb-tabs li" : "#timeline_$label .$opener";
                $output .= "
                /* slide in-out */
                var padding = $('#timeline_$label').outer$getsizeof();
                padding = parseFloat(padding)$padding;";
                $output .= ( @$attr['autoclose'] ) ? "$('#timeline_$label').animate({ '$location': '-='+padding+'px' }, 'fast' );" : '';
                $output .= "
                $('$openid').click(function(event) {
                    if ( $('#timeline_$label').hasClass('open') ) {
                        if ( $(this).hasClass( 'active' ) ) {
                            $('$openid').removeClass('active');
                            $('#timeline_$label').animate({ '$location': '-='+padding+'px' }, 'slow' ).removeClass('open');
                        } else {
                            $('$openid').removeClass('active');
                            $(this).addClass('active');
                        }
                    } else {
                        $(this).addClass('active');
                        $('#timeline_$label').animate({ '$location': '+='+padding+'px' }, 'slow' ).addClass('open');
                    }
                    event.preventDefault();
                });";
                }
                else { // only for normal tabable
                    $openid = "#timeline_$label .sb-tabs li";
                $output .= "
                $('$openid').click(function(event) {
                    $('$openid').removeClass('active');
                    if ( $('#timeline_$label').hasClass('open') ) {
                        if ( $(this).hasClass( 'active' ) ) {
                            $('#timeline_$label').removeClass('open');
                        } else {
                            $(this).addClass('active');
                        }
                    } else {
                        $(this).addClass('active');
                        $('#timeline_$label').addClass('open');
                    }
                    event.preventDefault();
                });";
                }
                
            if (@$ticker_id)
                $ticker_id_t = ' '.$ticker_id;
        } elseif ( $is_wall ) {
            if ( ! empty($feeds) ) {
            $stagger = (@$attr['stagger']) ? 'stagger: '.$attr['stagger'] : '';
            $columnWidth = (@$attr['fixWidth'] == 'false') ? '".sb-item"' : $itemwidth;
            $gutter = (@$attr['fixWidth'] == 'false') ? '".sb-gsizer"' : $gutterX;
            $percentPosition = (@$attr['fixWidth'] == 'false') ? 'true' : 'false';
            $output .= '
    			var $wall = $("#timeline_'.$label.$ticker_id_t.'").isotope({
                    itemSelector: ".sb-item",
                    layoutMode: "masonry",
                    percentPosition: '.$percentPosition.',
                    masonry: {
                      columnWidth: '.$columnWidth.',
                      gutter: '.$gutter.'
                    },
                    transitionDuration: '.@$attr['transition'].',
                    originLeft: '.@$attr['originLeft'].',
                    '.$stagger.'
    			});
				' . $lazyload_output . '
                
				/* wall in cache */
				setTimeout(function() {
					$wall.isotope("layout");
                    $(window).trigger("scroll");
				}, 500);
                $(window).resize(function() {
                    setTimeout(function() {
                        $(window).trigger("scroll");
                    }, 500);
                });

    			/* Filter wall by networks */
				$(".filter-items").on("click", "span", function() {
                    $(".filter-label").removeClass("active");
                    var filterValue = $(this).addClass("active").attr("data-filter");
					$wall.isotope({ filter: filterValue });
                    $wall.on( "arrangeComplete", function() {
                        $(window).trigger("scroll");
                    });
    			});';
                
                // fix lazyload after live update interval
                if (@$GLOBALS['islive'] && ! $is_feed) {
                    $output .= '
                    $wall.on( "removeComplete", function() {
                        $(window).trigger("scroll");
                    });';
                }
                
                // filter wall with a text phrase
				if ( @$attr['filter_search'] ) {
                    $output .= '
                $("#sb_'.$label.$ticker_id_t.' .sb-search").keyup(function(){
                    var filterValue = $(this).val();
                    if (filterValue != "") {
                        $wall.isotope({
                            filter: function() {
                                return ($(this).text().search(new RegExp(filterValue, "i")) > 0);
                            }
                        });
                    } else {
                        $wall.isotope({ filter: "*" });
                    }
                });';
                }
                
				$output .= '
				$(window).bind("scrollstop", function(){
					$wall.isotope("layout");
				});';
                $output .= $more_output;
            }
        } elseif ( $is_timeline ) {
			$output .= '
				$(".sb-thumb img").lazyload({
					effect: "fadeIn",
					skip_invisible: true,
					placeholder: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iLTQzIC00MyAxMjQgMTI0IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHN0cm9rZT0iI2ZmZiI+ICAgIDxnIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+ICAgICAgICA8ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxIDEpIiBzdHJva2Utd2lkdGg9IjIiPiAgICAgICAgICAgIDxjaXJjbGUgc3Ryb2tlLW9wYWNpdHk9Ii41IiBjeD0iMTgiIGN5PSIxOCIgcj0iMTgiLz4gICAgICAgICAgICA8cGF0aCBkPSJNMzYgMThjMC05Ljk0LTguMDYtMTgtMTgtMTgiPiAgICAgICAgICAgICAgICA8YW5pbWF0ZVRyYW5zZm9ybSAgICAgICAgICAgICAgICAgICAgYXR0cmlidXRlTmFtZT0idHJhbnNmb3JtIiAgICAgICAgICAgICAgICAgICAgdHlwZT0icm90YXRlIiAgICAgICAgICAgICAgICAgICAgZnJvbT0iMCAxOCAxOCIgICAgICAgICAgICAgICAgICAgIHRvPSIzNjAgMTggMTgiICAgICAgICAgICAgICAgICAgICBkdXI9IjFzIiAgICAgICAgICAgICAgICAgICAgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiLz4gICAgICAgICAgICA8L3BhdGg+ICAgICAgICA8L2c+ICAgIDwvZz48L3N2Zz4="
				});';
            if (@$more_output)
                $output .= $more_output;
        }

        // load tabs and rebuild feed ticker
        if (@$attr['tabable']) {
        	$output .= '
               $("#timeline_'.$label.' .sb-tabs").on("click", "li", function() {
                if ( $(this).hasClass( "active" ) ) {
                  feed = $(this).attr("data-feed");
                  tabnonce = $(this).parent().attr("data-nonce");
                  ';
               $output .= "
                  $('#timeline_".$label." .sb-content ul').html('<p class=\"sb-loading\"><i class=\"sb-icon sb-'+feed+'\"></i></p>');";
               $output .= '
                  $.ajax({
                    type: "post",
                    url: "'.SB_PATH.'ajax.php",
                    data: {action: "sb_tabable", feed: feed, attr: '.$attr_ajax.', nonce: tabnonce, label: "'.$label.'"},
                    cache: false
                    })
                    .done(function( response ) {
                        $("#timeline_'.$label.$ticker_id_t.'").html(response);
                        $sbticker.newsTicker();
                        ' . $ticker_lazyload_output . $iframe_output . '
                    })
                    .fail(function() {
                        alert( "Problem reading the feed data!" );
                  });
                }
               });';
            }
            
            if (@$iframe) {
				if (@$attr['iframe'] == 'slide') {
					if ( ! isset($GLOBALS['sb_scripts']['iframe_slideshow']) ) {
						$output .= $iframe_slideshow;
						$GLOBALS['sb_scripts']['iframe_slideshow'] = true;
					}
				} else {
					if ( ! isset($GLOBALS['sb_scripts']['iframe_media']) ) {
						$output .= $iframe_media;
						$GLOBALS['sb_scripts']['iframe_media'] = true;
					}
				}

				if ( isset($slideshow) ) {
					$colorbox_resize = 'width:"85%"';
					$slicepoint = (@$attr['slicepoint']) ? $attr['slicepoint'] : 300;
					$output .= '
					  $("div.sb-body .sb-text").expander({
						slicePoint: '.$slicepoint.',
						expandText: "'.ss_lang( 'read_more' ).'",
						userCollapseText: "'.ss_lang( 'read_less' ).'"
					  });';
				} else {
					$colorbox_resize = 'maxWidth:"95%", maxHeight:"95%"';
				}
				
				// resize colorbox on screen rotation
				$resize_part1 = '
                $(window).resize(function() {
                    if (jQuery("#cboxOverlay").is(":visible")) {
						var wsize = sb_getwinsize();
						var cbox = $( "#cboxLoadedContent" );';
					// Slide autosize
					if ( isset($slideshow) ) {
						$resize_part2 = '
						var slidespan = $("#cboxLoadedContent .sb-slide .sb-thumb");
						if (slidespan.length > 0) {
							var slidethumb = $(".sb-slide .sb-thumb iframe, .sb-slide .sb-thumb img");
							if ( slidethumb.attr("height") ) {
								var cwidth = ( cbox.width() < slidethumb.attr("width") ) ? cbox.width() : slidethumb.width();
								var wwidth = Math.round((wsize.newWidth * 70) / 100);
								if (cwidth < wwidth && wsize.newHeight > slidethumb.attr("height")) {
									cwidth = wwidth;
								}
								var newheight = Math.floor( (cwidth * slidethumb.attr("height") ) / slidethumb.attr("width") );
								slidethumb.height(newheight);
								if (slidethumb.width() < cwidth)
									slidethumb.width(cwidth);
							} else {
								var newheight = cbox.height() / 2;
							}
							
							if ( $(window).width() >= 768 ) {
								if (slidespan.children("span").length > 0) {
									$(".sb-slide .sb-inner .sb-body").innerHeight(500);
								} else {
									$(".sb-slide .sb-inner .sb-body").innerHeight(newheight);
								}
							} else {
								var bheight = wsize.newHeight - newheight;
								if (bheight < 150) {
									bheight = 150;
								}
								$(".sb-slide .sb-inner .sb-body").css("height", "auto").css("min-height", bheight);
							}
						} else {
							var bheight = (wsize.newHeight < 500) ? wsize.newHeight : 500;
							$(".sb-slide .sb-inner .sb-body").innerHeight(bheight);
						}';
					}
					$resize_part3 = '
						var iframebox = $( "#cboxLoadedContent iframe" );
						if ( iframebox.length ) {
							var iframeWidth = iframebox.attr("width");
							var iframeHeight = iframebox.attr("height");
                            if ( $(window).width() <= 767 ) {
                                var pheight = Math.round( (iframeHeight / iframeWidth) * 95 );
                                jQuery.colorbox.resize({width: "95%", height: pheight+"%"});
                            } else {
								if ( cbox.children("div.sb-slide").length > 0) {
									jQuery.colorbox.resize({'.$colorbox_resize.'});
								} else {
									if ( iframeHeight > wsize.newHeight ) {
										var newWidth = Math.round( (wsize.newHeight * iframeWidth) / iframeHeight);
										iframeWidth = newWidth;
										iframeHeight = wsize.newHeight;
										
										if ( iframeWidth > wsize.newWidth ) {
											iframeWidth = wsize.newWidth;
											iframeHeight = wsize.newHeight;
										}
									}
									jQuery.colorbox.resize({ width: parseInt(iframeWidth)+10, height: parseInt(iframeHeight)+10 });
								}
							}
                        } else {
                            jQuery.colorbox.resize({'.$colorbox_resize.'});
                        }
                    }
                });';
				
			if ( isset($slideshow) ) {
				if ( ! isset($GLOBALS['sb_scripts']['resize_slideshow']) ) {
					$output .= $resize_part1.$resize_part2.$resize_part3;
					$GLOBALS['sb_scripts']['resize_slideshow'] = true;
				}
			} else {
				if ( ! isset($GLOBALS['sb_scripts']['resize_media']) && ! isset($GLOBALS['sb_scripts']['resize_slideshow']) ) {
					$output .= $resize_part1.$resize_part3;
					$GLOBALS['sb_scripts']['resize_media'] = true;
				}
			}
        }
            
            if (@$GLOBALS['islive'] && ! $is_feed) {
                $timeinterval = (@$attr['live_interval'] ? intval($attr['live_interval']) * 60000 : 60000); // 60000 = 1 Min
                $stdiv = ($is_wall) ? 'div.sb-item' : 'div.timeline-row';
                $output .= '
              setInterval(function(){
                  var stlen = $("#timeline_'.$label.' '.$stdiv.'").length;
                  $.ajax({
                    type: "post",
                    url: "'.SB_PATH.'ajax.php",
                    data: {action: "sb_liveupdate", attr: '.$attr_ajax.', nonce: "'.ss_nonce_create( 'liveupdate' ).'", results: stlen, label: "'.$label.'"},
                    cache: false
                  })
                  .done(function( data ) {
                    if (data != "") {
                        var $elems = $(data).filter("'.$stdiv.'");
                        if ( $elems.first().attr("id") != $("#timeline_'.$label.' '.$stdiv.'").first().attr("id") ) {
                            var rm = 0;
                            var rms = false;
                            var rmcount = $elems.length;
                            var items = [];
                            $elems.each(function() {
                                if ( $("#timeline_'.$label.' '.$stdiv.'#" + $(this).attr("id") ).length == 0 ) {
                                    items.push(this);
                                    rm++;
                                } else {
                                    rms = true;
                                }
                            });';
                        if ( $is_wall ) {
                            $output .= '
                            if (rm > 0) {
                                $wall.isotope( "remove", $("#timeline_'.$label.'").find("'.$stdiv.'").slice(-rm) );
                            }
                            if (rms == true || rm == rmcount ) {
                                $wall.prepend( items ).isotope( "prepended", items );
                            }';
                        } elseif ( $is_timeline ) {
                            $output .= '
                            if (rm > 0) {
                                $("#timeline_'.$label.'").find("'.$stdiv.'").slice(-rm).remove();
                            }
                            if (rms == true || rm == rmcount ) {
                                $("#timeline_'.$label.'").prepend(items);
                            }
                            $(window).trigger("scroll");';
                        }
                        $output .= $lazyload_output . $iframe_output.'
                        }
                    }
                  });
              }, '.$timeinterval.');';
            }
            
                $output .= '
            });
        </script>';
        }
        
        if ( ! $ajax_feed)
            $output .= ($forceCrawl) ? "\t<!-- End PHP Social Stream - cache is disabled. -->\n" : "\t<!-- End PHP Social Stream - cache is enabled - duration: " . $attr['cache'] . " minutes -->\n";
        $output = str_replace( array("\r\n","\r","\t","\n"), '', $output );
        
        // slideshow output
        if (@$attr['iframe'] == 'slide' && @$ss_output)
            $output .= '
    		<div style="display:none">
                '.$ss_output.'
    		</div>';
            
    	if ( $echo )
    		echo $output;
    	else
    		return $output;
    }
    
    // function for retrieving data from feeds
    public function get_feed( $feed_key, $i, $key2, $feed_value, $results, $sboption, $cache, $forceCrawl = false, $sb_label = null ) {
        $feed_value = trim($feed_value);
        switch ( $feed_key ) {
            case 'facebook':
                $pageresults = 9; // the max results that is possible to fetch from facebook API - for group = 9 for page = 30 - we used 9 to support both
                $stepresults = ceil($results / $pageresults);
                $facebook_access_token = @$GLOBALS['api']['facebook']['facebook_access_token'];
                if ($locale = SB_LOCALE)
                    $locale_str = '&locale='.$locale;
                
                if ($datetime_from = @$sboption['facebook_datetime_from'])
                    $since_str = '&since='.strtotime($datetime_from);
                    
                if ($datetime_to = @$sboption['facebook_datetime_to'])
                    $until_str = '&until='.strtotime($datetime_to);
                
                if ($i == 3 || $i == 4) {
                    if ($after = @$_SESSION[$sb_label]['loadcrawl'][$feed_key.$i.$key2])
                        $after_str = '&after='.$after;
                } else {
                    if ($until = @$_SESSION[$sb_label]['loadcrawl'][$feed_key.$i.$key2])
                        $until_str = '&until='.$until;
                }
                
                $afields = array('id','created_time','updated_time','link','from','name','source','message','description','story','comments','likes','picture','full_picture','object_id','type','status_type');
                // define the feed url
                if ($i == 1) {
                    // Page Feed
                    $feedType = (@$sboption['facebook_pagefeed']) ? $sboption['facebook_pagefeed'] : 'feed';
                } elseif ($i == 2) {
                    // Group Feed
                    $feedType = 'feed';
                } elseif ($i == 3) {
                    $feedType = 'photos';
                    $afields[] = 'images';
                } elseif ($i == 4) {
                    $feedType = 'videos';
                    $afields[] = 'images';
                }
                
                $fields = implode(',', $afields);
                $feed_url = 'https://graph.facebook.com/v2.3/' . $feed_value . '/' . $feedType . '?limit=' . ( ($i == 2) ? $pageresults : $results ) . @$since_str . @$until_str . @$after_str . '&fields=' . $fields . '&access_token=' . $facebook_access_token;
                $label = 'https://graph.facebook.com/' . $feed_value . '/' . $feedType . '?limit=' . $results;
                
                // if group feed
                if ($i == 2) {
                    // crawl the feed or read from the cache
                    $get_feed = TRUE;
                    if ( ! $forceCrawl ) {
                        if ( $cache->is_cached($label) ) {
                            $content = $cache->get_cache($label);
                            $get_feed = FALSE;
                        }
                    }
                    if ($get_feed) {
                        $feed = array();
                        for ($i = 1; $i <= $stepresults; $i++) {
                            $content = $cache->do_curl($feed_url);
                            $pagefeed = @json_decode($content);
                            if ( ! empty($pagefeed) ) {
                                $feed[] = $pagefeed->data;
                                if ( count($pagefeed->data) < $pageresults )
                                    break;
                                $feed_url = $pagefeed->paging->next;
                            }
                        }
               			if ( ! $forceCrawl )
                            $cache->set_cache($label, json_encode($feed));
                    } else {
                        $feed = @json_decode($content);
                    }
                } else {
                    $content = ( ! $forceCrawl ) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                    if ( $pagefeed = @json_decode($content) ) {
                        if ( isset( $pagefeed->error ) ) {
                            if (@$this->attr['debuglog'])
                                ss_debug_log( 'Facebook error: '.@$pagefeed->error->message.' - ' . $feedType, SB_LOGFILE );
                            $feed[] = null;
                        } else {
                            if ($i == 3 || $i == 4) {
                                $feed[] = $pagefeed;
                            } else {
                                $feed[] = $pagefeed->data;
                            }
                        }
                    }
                }
            break;
            case 'twitter':
                $consumer_key = @trim($GLOBALS['api']['twitter']['twitter_api_key']);
                $consumer_secret = @trim($GLOBALS['api']['twitter']['twitter_api_secret']);
                $oauth_access_token = @trim($GLOBALS['api']['twitter']['twitter_access_token']);
                $oauth_access_token_secret = @trim($GLOBALS['api']['twitter']['twitter_access_token_secret']);
                if ( isset($sboption['twitter_feeds']) )
                    $twitter_feeds = explode(',', str_replace(' ', '', $sboption['twitter_feeds']) );
                else
                    $twitter_feeds = array('retweets', 'replies');
                switch($i)
                {
                	case 1:
                        $rest = 'statuses/user_timeline';
                        $params = array(
                            'exclude_replies' => in_array('replies', $twitter_feeds) ? 'false' : 'true',
                            'screen_name' => $feed_value
                            );
                        if ( ! in_array('retweets', $twitter_feeds) )
                            $params['include_rts'] = 'false';
                	break;
                	case 2:
                        $rest = "lists/statuses";
                        if ( is_numeric($feed_value) )
                            $params = array('list_id' => $feed_value);
                        else {
                            $feedvalarr = explode('/', $feed_value);
                            $params = array('owner_screen_name' => $feedvalarr[0], 'slug' => $feedvalarr[1]);
                            if ( in_array('retweets', $twitter_feeds) )
                                $params['include_rts'] = 'true';
                        }
                	break;
                	case 3:
                        $rest = "search/tweets";
                        $feed_value = urlencode($feed_value);
                        if ( ! in_array('retweets', $twitter_feeds) )
                            $feed_value .= ' AND -filter:retweets';
                        $params = array('q' => $feed_value);
                	break;
                }
                $params['count'] = $results;
                
                if ($id_from = @$sboption['twitter_since_id'])
                    $params['since_id'] = $id_from;
                    
                if ($id_to = @$sboption['twitter_max_id'])
                    $params['max_id'] = $id_to;
                    
                if ($max_id = @$_SESSION[$sb_label]['loadcrawl'][$feed_key.$i.$key2])
                    $params['max_id'] = $max_id;
        		
                $get_feed = TRUE;
                $label = 'https://api.twitter.com/1.1/'.$rest.'/'.serialize($params);
                if ( ! $forceCrawl ) {
                    if ( $cache->is_cached($label) ) {
                        $content = $cache->get_cache($label);
                        $get_feed = FALSE;
                    }
                }
                if ($get_feed) {
                    if ( ! class_exists( 'TwitterOAuth' ) )
                        require_once('oauth/twitteroauth.php');
                    $auth = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_access_token, $oauth_access_token_secret);
                    $auth->timeout = SB_API_TIMEOUT;
                    $auth->connecttimeout = SB_API_TIMEOUT;
                    $auth->decode_json = FALSE;
                    $content = $auth->get( $rest, $params );
                    if ( ! $content ) {
                    	if (@$this->attr['debuglog'])
                            ss_debug_log( 'Twitter error: An error occurs while reading the feed, please check your connection or settings.', SB_LOGFILE );
                    }
                    else {
                        $feed = @json_decode($content);
                        if ( isset( $feed->errors ) ) {
                            foreach( $feed->errors as $key => $val ) {
                                if (@$this->attr['debuglog'])
                                    ss_debug_log( 'Twitter error: '.$val->message.' - ' . $rest, SB_LOGFILE );
                            }
                            $feed = null;
                        }
                    }
           			if ( ! $forceCrawl )
                        $cache->set_cache($label, $content);
                }
                else
                    $feed = @json_decode($content);
    		break;
    		case 'google':
    			$google_api_key = @$GLOBALS['api']['google']['google_api_key'];
                if ($nextPageToken = @$_SESSION[$sb_label]['loadcrawl'][$feed_key.$i.$key2])
                    $pageToken = '&pageToken='.$nextPageToken;
                $feed_url = 'https://www.googleapis.com/plus/v1/people/' . $feed_value . '/activities/public?maxResults=' . $results . @$pageToken . '&key=' . $google_api_key;
                $content = ( ! $forceCrawl ) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                $feed = @json_decode($content);
                if (@$feed->error) {
                    $feed = null;
                }
    		break;
            case 'flickr':
                $flickr_api_key = @$GLOBALS['api']['flickr']['flickr_api_key'];
                if ($nextPage = @$_SESSION[$sb_label]['loadcrawl'][$feed_key.$i.$key2])
                    $pageToken = '&page='.$nextPage;
                if ($i == 1) {
                    $feedType = 'flickr.people.getPublicPhotos';
                    $feedID = '&user_id='.$feed_value;
                } elseif ($i == 2) {
                    $feedType = 'flickr.groups.pools.getPhotos';
                    $feedID = '&group_id='.$feed_value;
                }
                $feed_url = 'https://api.flickr.com/services/rest/?method='.$feedType.'&api_key='.$flickr_api_key . $feedID . '&per_page=' . $results . @$pageToken . '&extras=date_upload,date_taken,owner_name,icon_server,tags,views&format=json&nojsoncallback=1';
                $content = ( ! $forceCrawl ) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                $feed = @json_decode($content);
    		break;
            case 'delicious':
                if ( empty($_SESSION[$sb_label]['loadcrawl']) ) {
                    $feed_url = "http://feeds.del.icio.us/v2/json/" . $feed_value . '?count=' . $results;
                    $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                    $feed = @json_decode($content);
                }
            break;
    		case 'pinterest':
                if ( empty($_SESSION[$sb_label]['loadcrawl']) ) {
                    // get json data
                    $json_uri = ($i == 1) ? 'users' : 'boards';
                    $feed_url = "https://api.pinterest.com/v3/pidgets/$json_uri/" . $feed_value . "/pins/";
                    $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                    $feed[0] = @json_decode($content);
                    if (@$feed[0]->status == 'success') {
                        // get rss data
                        $rss_uri = ($i == 1) ? '/feed.rss' : '.rss';
                        $feed_url = "https://www.pinterest.com/" . $feed_value . "$rss_uri";
                        $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                        $feed[1] = @simplexml_load_string($content);
                    } else {
                        $feed = null;
                        ss_debug_log( 'Pinterest error: An error occurs while reading the feed - ' . $feed_url, SB_LOGFILE );
                    }
                }
    		break;
    		case 'instagram':
                $instagram_access_token = @$GLOBALS['api']['instagram']['instagram_access_token'];
                $max_str = 'max_id';
                $feed_url = '';
                if ($i == 1) {
                    $user_url = 'https://api.instagram.com/v1/users/search?q=' . $feed_value .'&access_token=' . $instagram_access_token;
                    $user_content = ( ! $forceCrawl) ? $cache->get_data($user_url, $user_url) : @$cache->do_curl($user_url);
                    if ($user_content) {
                        $user_feed = @json_decode($user_content);
                        if ( ! empty($user_feed->data) ) {
                            foreach($user_feed->data as $userdata) {
                                if ($userdata->username == $feed_value) {
                                    $user_id = $userdata->id;
                                    break;
                                }
                            }
                            $feed_url = 'https://api.instagram.com/v1/users/' . @$user_id . '/media/recent?count=' . $results;
                        }
                    }
                } elseif ($i == 2) {
                    $feed_url = 'https://api.instagram.com/v1/tags/' . urlencode($feed_value) . '/media/recent?count=' . $results;
                    $max_str = 'max_tag_id';
                } elseif ($i == 3) {
                    $feed_url = 'https://api.instagram.com/v1/locations/' . $feed_value . '/media/recent?access_token=' . $instagram_access_token;
                } elseif ($i == 4) {
                    $coordinates = explode(',', $feed_value);
                    $feed_url = 'https://api.instagram.com/v1/media/search?lat=' . $coordinates[0] . '&lng=' . $coordinates[1] . '&distance=' . $coordinates[2];
                    $max_str = 'max_timestamp';
                }
                $feed_url .= '&access_token=' . $instagram_access_token;
				
                if (@$feed_url) {
                    if ($next_max_id = @$_SESSION[$sb_label]['loadcrawl'][$feed_key.$i.$key2])
                        $feed_url .= '&'. $max_str .'='.$next_max_id;
                    
                    $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                    $feed = @json_decode($content);
                }
    		break;
    		case 'youtube':
                $google_api_key = @$GLOBALS['api']['google']['google_api_key'];
                if ($nextPageToken = @$_SESSION[$sb_label]['loadcrawl'][$feed_key.$i.$key2])
                    $pageToken = '&pageToken='.$nextPageToken;
                switch($i)
                {
                	case 1:
                    case 4:
                        $channel_filter = ($i == 1) ? 'forUsername' : 'id';
                        $user_url = 'https://www.googleapis.com/youtube/v3/channels?part=contentDetails&'.$channel_filter.'=' . $feed_value .'&key=' . $google_api_key;
                        $user_content = ( ! $forceCrawl) ? $cache->get_data($user_url, $user_url) : @$cache->do_curl($user_url);
                        if ($user_content) {
                            $user_feed = @json_decode($user_content);
                            if (@$user_feed->items[0])
                                $feed_url = 'https://www.googleapis.com/youtube/v3/playlistItems?playlistId=' . $user_feed->items[0]->contentDetails->relatedPlaylists->uploads;
                        }
                    break;
                    case 2:
                        $feed_url = 'https://www.googleapis.com/youtube/v3/playlistItems?playlistId=' . $feed_value;
                    break;
                    case 3:
                        $feed_url = 'https://www.googleapis.com/youtube/v3/search?q=' . rawurlencode($feed_value);
                    break;
                }
                if ($results > 50) $results = 50;
                if (@$feed_url) {
                    $feed_url .= '&part=snippet&maxResults=' . $results . @$pageToken . '&key=' . $google_api_key;
                    $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                    $feed = @json_decode($content);
                    
					if (is_object($feed) && @$user_feed)
						$feed->userInfo = @$user_feed->items[0]->snippet;
                }
    		break;
    		case 'vimeo':
                $vimeo_access_token = @$GLOBALS['api']['vimeo']['vimeo_access_token'];
                $type = 'videos';
                $feed_url = 'https://api.vimeo.com/users/' . $feed_value . '/' . $type . "?per_page=$results&access_token=$vimeo_access_token";
                if ($nextPage = @$_SESSION[$sb_label]['loadcrawl'][$feed_key.$i.$key2])
                    $feed_url .= '&page='.$nextPage;
                    
                $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                $feed = @json_decode($content);
    		break;
    		case 'tumblr':
                $tumblr_api_key = @$GLOBALS['api']['tumblr']['tumblr_api_key'];
                $feed_url = "https://api.tumblr.com/v2/blog/" . $feed_value . ".tumblr.com/posts?api_key={$tumblr_api_key}&limit=$results";
                if ($posts_start = @$_SESSION[$sb_label]['loadcrawl'][$feed_key.$i.$key2])
                    $feed_url .= '&offset='.$posts_start;
                    
                $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                $feed = @json_decode($content);
            break;
    		case 'stumbleupon':
                if ( empty($_SESSION[$sb_label]['loadcrawl']) ) {
                    $stumbleupon_feeds = (@$sboption['stumbleupon_feeds']) ? ss_explode($sboption['stumbleupon_feeds']) : array( 'comments' => true, 'likes' => true );
                $feedtypes = array('comments', 'likes');
                foreach ($feedtypes as $type) {
                    if (@$stumbleupon_feeds[$type]) {
                        $feed_url = "http://www.stumbleupon.com/rss/stumbler/" . $feed_value . "/" . $type;
                        $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                        if ( $data = @simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA) )
                            $feed[$type] = $data;
                    }
                }
                }
    		break;
    		case 'deviantart':
                if ( empty($_SESSION[$sb_label]['loadcrawl']) ) {
                    $feed_url = "https://backend.deviantart.com/rss.xml?type=deviation&q=by%3A" . $feed_value . "+sort%3Atime+meta%3Aall";
                    $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                    $feed = @simplexml_load_string($content);
                }
    		break;
            case 'rss':
                if ( empty($_SESSION[$sb_label]['loadcrawl']) ) {
                    $content = ( ! $forceCrawl) ? $cache->get_data($feed_value, $feed_value) : $cache->do_curl($feed_value);
                    $feed = @simplexml_load_string($content);
                }
            break;
            case 'soundcloud':
                if ( empty($_SESSION[$sb_label]['loadcrawl']) ) {
                    $soundcloud_client_id = @$GLOBALS['api']['soundcloud']['soundcloud_client_id'];
                    $feed_url = "http://api.soundcloud.com/users/$feed_value/tracks.json?client_id=" . $soundcloud_client_id . "&limit=$results";
                    $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                    $feed = @json_decode($content);
                }
            break;
            case 'vk':
                $pagefeed = (@$sboption['vk_pagefeed']) ? $sboption['vk_pagefeed'] : 'all';
                $wall_by = ($i == 1) ? 'domain' : 'owner_id';
                $feed_url = "https://api.vk.com/method/wall.get?v=5.34&{$wall_by}={$feed_value}&count={$results}&extended=1&lang=en&filter={$pagefeed}";
                if (@$this->attr['https'])
                    $feed_url .= '&https=1';
                if ($offset = @$_SESSION[$sb_label]['loadcrawl'][$feed_key.$i.$key2])
                    $feed_url .= '&offset='.$offset;
                else
                    $offset = 0;
                $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                $content = @mb_convert_encoding($content, "UTF-8", "auto");
                $feed = @json_decode($content);
                if (is_object($feed))
                    $feed->offset = $offset;
            break;
            case 'linkedin':
                $linkedin_access_token = @$GLOBALS['api']['linkedin']['linkedin_access_token'];
                $feed_url = "https://api.linkedin.com/v1/companies/{$feed_value}/updates?oauth2_access_token={$linkedin_access_token}&count={$results}&format=json";
                
                $sboption['linkedin_pagefeed'] = 'status-update';
                if ($pagefeed = @$sboption['linkedin_pagefeed']) {
                    if ($pagefeed != 'all')
                        $feed_url .= '&event-type='.$pagefeed;
                }
                
                if ($offset = @$_SESSION[$sb_label]['loadcrawl'][$feed_key.$i.$key2])
                    $feed_url .= '&start='.$offset;
                    
                $company_url = "https://api.linkedin.com/v1/companies/{$feed_value}:(id,name,logo-url,square-logo-url)?oauth2_access_token={$linkedin_access_token}&format=json";
                $company_content = ( ! $forceCrawl) ? $cache->get_data($company_url, $company_url) : @$cache->do_curl($company_url);
                
                $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                $feed = @json_decode($content);
                if ( is_object($feed) ) {
                    if ($company_content) {
                        $company_feed = @json_decode($company_content);
                        $feed->company = $company_feed;
                    }
                }
            break;
            case 'vine':
                $feed_url = "https://api.vineapp.com/timelines/users/{$feed_value}";
                if ($offset = @$_SESSION[$sb_label]['loadcrawl'][$feed_key.$i.$key2])
                    $feed_url .= '?page='.$offset;
                $content = ( ! $forceCrawl) ? $cache->get_data($feed_url, $feed_url) : $cache->do_curl($feed_url);
                $feed = @json_decode($content, false, 512, JSON_BIGINT_AS_STRING);
            break;
    	}
        
    	return @$feed;
    }
    
    // create time string for sorting and applying pinning options
    private function make_timestr($time, $link) {
        $timestr = ( is_numeric($time) ) ? $time : strtotime($time);
        if ( ! empty($this->attr['pins']) ) {
            $dkey = array_search($link, $this->attr['pins']);
            if ($dkey !== false)
                $timestr = strtotime("+$dkey day");
        }
		$linkstr = sprintf("%u", crc32($link) );
        return $timestr.'-'.$linkstr;
    }
    
    // applying stream items removal
    private function make_remove($link) {
        if ( ! empty($this->attr['remove']) ) {
            if ( in_array($link, $this->attr['remove']) )
                return false;
        }
        return true;
    }
    
    /**
     * Word Limiter
     *
     * Limits a string to X number of words.
     *
     * @param   $end_char   the end character. Usually an ellipsis
     */
    function word_limiter($text, $url = '', $comment = false) {
        $limit = ($comment) ? @$this->attr['commentwords'] : @$this->attr['words'];
        $end_char = '...';
        
        $str = trim( strip_tags($text) );
        $str1 = trim( strip_tags($text, '<a>') );
    	if ($str == '') {
    		return $str;
    	}
        
        if ($this->str_word_count_utf8($str) < $limit) {
            return ($str1 == $str) ? $this->append_links($str1) : $str1;
        }
        
    	preg_match('/^\s*+(?:\S++\s*+){1,'.(int) $limit.'}/', $str, $matches);
        if (strlen($str) == strlen($matches[0])) {
    		$end_char = '';
    	}
        $str = $this->append_links($matches[0]);
    	if (@$this->attr['readmore'] && $url)
            $end_char = ' <a href="' . $url . '"'.$this->target.' style="font-size: large;">' . $end_char . '</a>';
            
        return $str.$end_char;
    }
    
    // Title Limiter (limits the title of each item to X number of words)
    function title_limiter($str, $url = '') {
        $end_char = '...';
        $limit = (@$this->attr['titles']) ? $this->attr['titles'] : 15;
        $str = strip_tags($str);

    	if (trim($str) == '')
    	{
    		return $str;
    	}
        
        if ($this->str_word_count_utf8($str) < $limit) {
            return $str;
        }

    	preg_match('/^\s*+(?:\S++\s*+){1,'.(int) $limit.'}/', $str, $matches);

        if (strlen($str) == strlen($matches[0]))
    	{
    		$end_char = '';
    	}
            
        return rtrim($matches[0]).$end_char;
    }
    
    function append_links($str) {
        // make the urls hyper links
        $regex = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';
        $str = preg_replace_callback($regex, array(&$this, 'links_callback'), $str);
        return nl2br($str);
    }
    
    function links_callback($matches) {
        return '<a href="'.$matches[0].'"'.$this->target.'>'.$matches[0].'</a>';
    }
    
    function format_text($str) {
        $str = trim( strip_tags($str) );
    	if ($str == '')
    	{
    		return $str;
    	}
        
        $str = $this->append_links($str);
        return $str;
    }
    
    // Word counter function
    function str_word_count_utf8($str) {
        return count(preg_split('~[^\p{L}\p{N}\']+~u',$str));
    }
    
    // get all URLs from string
    function geturls($string) {
        $regex = '/https?\:\/\/[^\" ]+/i';
        preg_match_all($regex, $string, $matches);
        return ($matches[0]);
    }
    
    function getsrc($html) {
        preg_match_all('/<img[^>]+>/i', $html, $rawimagearray, PREG_SET_ORDER);
        if ( isset($rawimagearray[0][0]) ) {
            preg_match('@src="([^"]+)"@', $rawimagearray[0][0], $match);
            $img['src'] = @array_pop($match);
            preg_match('@width="([^"]+)"@', $rawimagearray[0][0], $matchwidth);
            $img['width'] = @array_pop($matchwidth);
            
            return (@$img['width'] && $img['width'] < 10) ? false : $img;
        }
    }

    function twitter_add_links($text) {
        // Add links to all @ mentions
        $text = preg_replace('/@([^\s]+)/', '<a href="https://twitter.com/$1"'.$this->target.'>@$1</a>', $text );
        // Add links to all hash tags
        $text = preg_replace('/#([^\s]+)/', '<a href="https://twitter.com/search/%23$1"'.$this->target.'>#$1</a>', $text );
        
        return $text;
    }

    function vk_get_photo($photo){
        foreach ($photo as $ikey => $iphoto) {
            if (stristr($ikey, 'photo_') == TRUE) {
                $source = $iphoto;
            }
        }
        return @$source;
    }
} // end class

// Check for Windows to find and replace the %e modifier correctly
function ss_format_locale( $format ) {
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
        $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%d', $format);
    }
    return $format;
}

// Friendly dates (i.e. "2 days ago")
function ss_friendly_date( $date ) {
	// Get the time difference in seconds
	$post_time = ( is_numeric($date) ) ? $date : strtotime( $date );
    if ( strlen($post_time) > 10 ) $post_time = substr( $post_time, 0, -3 );
	$current_time = time();
	$time_difference = $current_time - $post_time;
	
	// Seconds per...
	$minute = 60;
	$hour = 3600;
	$day = 86400;
	$week = $day * 7;
	$month = $day * 31;
	$year = $day * 366;
	
	// if over 3 years
	if ( $time_difference > $year * 3 ) {
		$friendly_date = ss_lang( 'a_long_while_ago' );
	}
	
	// if over 2 years
	else if ( $time_difference > $year * 2 ) {
		$friendly_date = ss_lang( 'over_2_years_ago' );
	}
	
	// if over 1 year
	else if ( $time_difference > $year ) {
		$friendly_date = ss_lang( 'over_a_year_ago' );
	}
	
	// if over 11 months
	else if ( $time_difference >= $month * 11 ) {
		$friendly_date = ss_lang( 'about_a_year_ago' );
	}
	
	// if over 2 months
	else if ( $time_difference >= $month * 2 ) {
		$months = (int) $time_difference / $month;
		$friendly_date = sprintf( ss_lang( 'd_months_ago' ), $months );
	}
	
	// if over 4 weeks ago
	else if ( $time_difference > $week * 4 ) {
		$friendly_date = ss_lang( 'last_month' );
	}
	
	// if over 3 weeks ago
	else if ( $time_difference > $week * 3 ) {
		$friendly_date = ss_lang( '3_weeks_ago' );
	}
	
	// if over 2 weeks ago
	else if ( $time_difference > $week * 2 ) {
		$friendly_date = ss_lang( '2_weeks_ago' );
	}
	
	// if equal to or more than a week ago
	else if ( $time_difference >= $day * 7 ) {
		$friendly_date = ss_lang( 'last_week' );
	}
	
	// if equal to or more than 2 days ago
	else if ( $time_difference >= $day * 2 ) {
		$days = (int) $time_difference / $day;
		$friendly_date = sprintf( ss_lang( 'd_days_ago' ), $days );
	}
	
	// if equal to or more than 1 day ago
	else if ( $time_difference >= $day ) {
		$friendly_date = ss_lang( 'yesterday' );
	}
	
	// 2 or more hours ago
	else if ( $time_difference >= $hour * 2 ) {
		$hours = (int) $time_difference / $hour;
		$friendly_date = sprintf( ss_lang( 'd_hours_ago' ), $hours );
	}
	
	// 1 hour ago
	else if ( $time_difference >= $hour ) {
		$friendly_date = ss_lang( 'an_hour_ago' );
	}
	
	// 2–59 minutes ago
	else if ( $time_difference >= $minute * 2 ) {
		$minutes = (int) $time_difference / $minute;
		$friendly_date = sprintf( ss_lang( 'd_minutes_ago' ), $minutes );
	}
	
	else {
		$friendly_date = ss_lang( 'just_now' );
	}
	
	// HTML 5 FTW
	return '<time title="' . strftime( SB_DT_FORMAT, $post_time ) . '" datetime="' . date( 'c', $post_time ) . '" pubdate>' . ucfirst( $friendly_date ) . '</time>';
}

// i18n dates
function ss_i18n_date( $date, $format ) {
    $post_time = ( is_numeric($date) ) ? $date : strtotime( $date );
    return strftime( $format, $post_time );
}

function ss_explode( $output = array() ) {
    if ( ! empty($output) ) {
        $outputArr = explode(',', str_replace(' ', '', $output) );
        foreach ($outputArr as $val)
            $out[$val] = true;
        
        return $out;
    }
    return false;
}

// hex to rgb numerical converter for color styling
function ss_hex2rgb($hex, $str = true) {
   $hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   } 
   // returns the rgb values separated by commas OR returns an array with the rgb values
   $rgb = ($str) ? "$r, $g, $b" : array($r, $g, $b);
   return $rgb;
}

// Shuffle associative and non-associative array
function ss_shuffle_assoc($list) {
  if (!is_array($list)) return $list;

  $keys = array_keys($list);
  shuffle($keys);
  $random = array();
  foreach ($keys as $key)
    $random[$key] = $list[$key];

  return $random;
}

// This method creates an nonce. It should be called by one of the previous two functions.
function ss_nonce_create( $action = '' , $user = '' ) {
	return substr( ss_nonce_generate_hash( $action . $user ), -12, 10);
}

// This method validates an nonce
function ss_nonce_verify( $nonce , $action = '' , $user = '' ) {
	// Nonce generated 0-12 hours ago
	if ( substr(ss_nonce_generate_hash( $action . $user ), -12, 10) == $nonce ) {
		return true;
	}
	return false;
}

// This method generates the nonce timestamp
function ss_nonce_generate_hash( $action = '' , $user = '' ) {
	return md5( SB_NONCE_KEY . $action . $user . $action );
}

// Retrieves the translated string for language
function ss_lang($label) {
    return $GLOBALS['_'][$label];
}

function ss_win_locale($locale) {
    $winlocale = array(
    "en" => 'USA_ENU',
    "ar" => 'SAU_ARA',
    "az" => 'AZE_AZE',
    "bg_BG" => 'BGR_BGR',
    "bs_BA" => 'Bosanski',
    "ca" => 'ESP_CAT',
    "cy" => 'Cymraeg',
    "da_DK" => 'DNK_DAN',
    "de_CH" => 'CHE_DES',
    "de_DE" => 'DEU_DEU',
    "el" => 'GRC_ELL',
    "en_CA" => 'CAN_ENC',
    "en_AU" => 'AUS_ENA',
    "en_GB" => 'GBR_ENG',
    "eo" => 'Esperanto',
    "es_PE" => 'PER_ESR',
    "es_ES" => 'ESP_ESN',
    "es_MX" => 'MEX_ESM',
    "es_CL" => 'CHL_ESL',
    "eu" => 'ESP_EUQ',
    "fa_IR" => 'IRN_FAR',
    "fi" => 'FIN_FIN',
    "fr_FR" => 'FRA_FRA',
    "gd" => 'Gàidhlig',
    "gl_ES" => 'ESP_GLC',
    "haz" => 'هزاره گی',
    "he_IL" => 'ISR_HEB',
    "hr" => 'HRV_HRV',
    "hu_HU" => 'HUN_HUN',
    "id_ID" => 'IDN_IND',
    "is_IS" => 'ISL_ISL',
    "it_IT" => 'ITA_ITA',
    "ja" => 'JPN_JPN',
    "ko_KR" => 'KOR_KOR',
    "lt_LT" => 'LTU_LTH',
    "my_MM" => 'ဗမာစာ',
    "nb_NO" => 'NOR_NOR',
    "nl_NL" => 'nld_nld',
    "nn_NO" => 'Norsk nynorsk',
    "oci" => 'Occitan',
    "pl_PL" => 'POL_PLK',
    "ps" => 'پښتو',
    "pt_PT" => 'PRT_PTG',
    "pt_BR" => 'BRA_PTB',
    "ro_RO" => 'ROM_ROM',
    "ru_RU" => 'Русский',
    "sk_SK" => 'Slovenčina',
    "sl_SI" => 'Slovenščina',
    "sq" => 'Shqip',
    "sr_RS" => 'Српски језик',
    "sv_SE" => 'Svenska',
    "th" => 'ไทย',
    "tr_TR" => 'Türkçe',
    "ug_CN" => 'Uyƣurqə',
    "uk" => 'Українська',
    "zh_CN" => '简体中文',
    "zh_TW" => '繁體中文');
    return $winlocale[$locale];
}

// grab the layout files
function ss_getFileTitles( $path = '/layout/' ) {
    $_aFileTitles = array();
    if ($handle = opendir( SB_DIRNAME . $path )) {
        while($file = readdir($handle)) {
            if ($file !== '.' && $file !== '..')
            {
                $finfo = pathinfo($file);
                if ( isset($finfo['extension']) ) {
                    if ( $finfo['extension'] == 'php' )
                        $_aFileTitles[$finfo['filename']] = $finfo['filename'];
                }
            }
        }
        closedir($handle);
    }
    if ( empty($_aFileTitles) ) {
        $_aFileTitles[''] = '-- There is no layout created --';
    }
    return $_aFileTitles;
}

function ss_debug_log($mValue, $sFilePath = null) {
    $msg = date( "Y/m/d H:i:s", time() ) . ' - ' . $mValue . PHP_EOL;
    error_log($msg, 3, $sFilePath);
}

// function for using in template files
function social_stream( $atts ) {
    $sb = new SocialStream();
    return $sb->init( $atts, false );
}

// End of file social-stream.php