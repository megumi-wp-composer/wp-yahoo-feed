<?php

namespace Megumi\WP;

class YahooFeed
{
	private $feed_name;

	public function __construct( $feed_name )
	{
		$this->feed_name = $feed_name;

		add_action( 'init', array( $this, 'init') );
		register_activation_hook( __FILE__, array( $this, 'register_activation_hook' ) );
	}

	public function register_activation_hook()
	{
		$this->init();
		flush_rewrite_rules();
	}

	public function init()
	{
		var_dump( 'init action' );
		add_feed( $this->feed_name, array( $this, 'feed' ) );
	}

	public function feed()
	{
		global $wp_query;
		var_dump($wp_query);
		exit;
	}
}
