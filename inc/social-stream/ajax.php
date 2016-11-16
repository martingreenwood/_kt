<?php

/**
 * PHP Social Stream 2.5.1
 * Copyright 2015 Axent Media (axentmedia@gmail.com)
 */

@header( 'Content-Type: text/html; charset=utf-8' );
@header( 'X-Robots-Tag: noindex' );

require_once( dirname( __FILE__ ) . '/social-stream.php' );

// create the ajax callback for tabable widget
if ($_POST['action'] == 'sb_tabable') {
    
    if ( ! ss_nonce_verify( $_REQUEST['nonce'], "tabable", $_REQUEST['label'] )) {
        exit("No naughty business please!");
    }

    if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $sb = new SocialStream();
        $sb->init( $_REQUEST['attr'], true, null, array( $_REQUEST['feed'] ) );
    }
    else {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
    }
}
// create the ajax callback for load more
elseif ($_POST['action'] == 'sb_loadmore') {

    if ( ! ss_nonce_verify( $_REQUEST['nonce'], "loadmore", $_REQUEST['label'] ) ) {
        exit("No naughty business please!");
    }

    if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $sb = new SocialStream();
        $sb->init( $_REQUEST['attr'], true, null, 'all', @$_SESSION[$_REQUEST['label']]['loadmore'] );
    }
    else {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
    }
}
// creates the ajax callback for live update
elseif ($_POST['action'] == 'sb_liveupdate') {

    if ( ! ss_nonce_verify( $_REQUEST['nonce'], "liveupdate")) {
        exit("No naughty business please!");
    }

    if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $args = array('liveresults' => @$_REQUEST['results']);
        $sb = new SocialStream();
        $sb->init( $_REQUEST['attr'], true, $args, 'all', array() );
    }
    else {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
    }
}

die();

// End of file ajax.php