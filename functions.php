<?php
/**
 * _kt functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package _kt
 */

if ( ! function_exists( '_kt_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function _kt_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on _kt, use a find and replace
	 * to change '_kt' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( '_kt', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo' );

	add_image_size( '169cover', '1650', '645', true );
	add_image_size( 'large-slider', '1290', '645', true );
	add_image_size( 'hp-thumb', '410', '275', true );
	add_image_size( '300x430', '300', '430', true );
	add_image_size( '600x430', '600', '430', true );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary', '_kt' ),
		'footer1' => esc_html__( 'Footer One', '_kt' ),
		'footer2' => esc_html__( 'Footer Two', '_kt' ),
		'footer3' => esc_html__( 'Footer Three', '_kt' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );
}
endif;
add_action( 'after_setup_theme', '_kt_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function _kt_content_width() {
	$GLOBALS['content_width'] = apply_filters( '_kt_content_width', 640 );
}
add_action( 'after_setup_theme', '_kt_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function _kt_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', '_kt' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', '_kt' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', '_kt_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function _kt_scripts() {
	wp_enqueue_style( '_kt_style', get_stylesheet_uri() );
	wp_enqueue_style( '_kt_picker_css', get_template_directory_uri() . '/assets/themes/classic.css' );
	wp_enqueue_style( '_kt_picker_css_date', get_template_directory_uri() . '/assets/themes/classic.date.css' );

	wp_enqueue_script( 'jquery' );

	wp_enqueue_script( '_ky_gmaps', '//maps.googleapis.com/maps/api/js?key=AIzaSyDC6NXY8XZrS6mELrD8_Dj3Hg_OTqHret8', array(), '', true );
	wp_enqueue_script( '_kt_fa', '//use.fontawesome.com/7390ec371d.js', array(), '', true);
	
	if(is_front_page()) {
		wp_enqueue_script( '_kt_mc', '//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js', array(), '', true);
	}

	wp_enqueue_script( '_kt_legacy', get_template_directory_uri() . '/js/legacy.js', array(), '', true );
	wp_enqueue_script( '_kt_picker', get_template_directory_uri() . '/js/picker.js', array(), '', true );
	wp_enqueue_script( '_kt_picker_date', get_template_directory_uri() . '/js/picker.date.js', array(), '', true );

	wp_enqueue_script( '_kt_easyPaginate', get_template_directory_uri() . '/js/jquery.easyPaginate.js', array(), '', true );

	wp_enqueue_script( '_kt_map', get_stylesheet_directory_uri() . '/js/maps.js', array(), '', true);
	//wp_enqueue_script( '_kt_navigation', get_template_directory_uri() . '/js/navigation.js', array(), '', true );

	wp_enqueue_script( '_kt_base', get_stylesheet_directory_uri() . '/js/base.js', array(), '', true);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', '_kt_scripts' );

/**
 * Enqueue Tyypekit
 */
function _kt_typekit () { ?>
<script>
  (function(d) {
    var config = {
      kitId: 'rte3wfq',
      scriptTimeout: 3000,
      async: true
    },
    h=d.documentElement,t=setTimeout(function(){h.className=h.className.replace(/\bwf-loading\b/g,"")+" wf-inactive";},config.scriptTimeout),tk=d.createElement("script"),f=false,s=d.getElementsByTagName("script")[0],a;h.className+=" wf-loading";tk.src='https://use.typekit.net/'+config.kitId+'.js';tk.async=true;tk.onload=tk.onreadystatechange=function(){a=this.readyState;if(f||a&&a!="complete"&&a!="loaded")return;f=true;clearTimeout(t);try{Typekit.load(config)}catch(e){}};s.parentNode.insertBefore(tk,s)
  })(document);
</script>
<?php 
}
add_action( 'wp_head', '_kt_typekit', 999 );

function excerpt($limit) {

	$excerpt = explode(' ', get_the_excerpt(), $limit);
	if (count($excerpt)>=$limit) {
		array_pop($excerpt);
		$excerpt = implode(" ",$excerpt).'...';
	} else {
		$excerpt = implode(" ",$excerpt);
	}
	$excerpt = preg_replace('`[[^]]*]`','',$excerpt);
	return $excerpt;
}

function content($limit) {
	$content = explode(' ', get_the_content(), $limit);
	if (count($content)>=$limit) {
		array_pop($content);
		$content = implode(" ",$content).'...';
	} else {
		$content = implode(" ",$content);
	}
	$content = preg_replace('/[.+]/','', $content);
	$content = apply_filters('the_content', $content);	
	$content = str_replace(']]>', ']]&gt;', $content);
	return $content;
}

/**
 * Move Jetpack
 */
function jptweak_remove_share() {
    remove_filter( 'the_content', 'sharing_display',19 );
    remove_filter( 'the_excerpt', 'sharing_display',19 );
    if ( class_exists( 'Jetpack_Likes' ) ) {
        remove_filter( 'the_content', array( Jetpack_Likes::init(), 'post_likes' ), 30, 1 );
    }
}
add_action( 'loop_start', 'jptweak_remove_share' );

/*=============================
=            CLEAN            =
=============================*/

function cleanMe($string) {
  $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
  return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

function cleanMeExta($string) {
    //Lower case everything
    $string = strtolower($string);
    //Make alphanumeric (removes all other characters)
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    //Clean up multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", "", $string);
    //Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "", $string);
    return $string;
}

/*=============================
=            CACHE            =
=============================*/

function create_cache_folders() {
  if (wp_mkdir_p(FEED_CACHE)) {
  }
}
add_action( 'init', 'create_cache_folders' );

/*================================
=            GET DATA            =
================================*/

function get_remote_data($url)
{
  $curl = curl_init();
  $options = array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => 1,
  );
  curl_setopt_array($curl, $options);
  $string = curl_exec($curl);
  return $string;
}

/*====================================
=            ROLES / EDIT            =
====================================*/

// Allow editors to see Appearance menu
$role_object = get_role( 'editor' );
$role_object->add_cap( 'edit_theme_options' );
function hide_menu() { 
    // Hide theme selection page
    remove_submenu_page( 'themes.php', 'themes.php' );
    // Hide widgets page
    //remove_submenu_page( 'themes.php', 'widgets.php' );
    // Hide customize page
    global $submenu;
    unset($submenu['themes.php'][6]);
 
}
 
add_action('admin_head', 'hide_menu');


/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Load custom posts file.
 */
require get_template_directory() . '/inc/cpts.php';
