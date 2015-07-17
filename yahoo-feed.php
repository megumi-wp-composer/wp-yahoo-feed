<?php
/*
Plugin Name: yahoo-feed
*/

require_once dirname( __FILE__ ) . '/vendor/autoload.php';

$yahoo = new Megumi\WP\YahooFeed( 'hoge' );
$yahoo->register();

add_action( 'yahoo_feed_item_hoge', function(){
	echo '<hoge>aaaaaaaaaaaaaaa</hoge>';
} );
