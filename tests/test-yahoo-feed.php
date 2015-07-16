<?php

use Megumi\WP\YahooFeed;

class YahooFeed_Test extends WP_UnitTestCase
{
	private $my_custom_feed_url = 'my-feed';

	function setUp()
	{
		parent::setUp();

		update_option( 'permalink_structure', '/archives/%post_id%' );
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules();
	}

	function tearDown() {
		parent::tearDown();
		$GLOBALS['wp_rewrite']->init();
	}

	/**
	 * @test
	 */
	function permalink_structure()
	{

		$yahoo_feed = new YahooFeed( $this->my_custom_feed_url );
		$yahoo_feed->register_activation_hook();

		$this->go_to( '/feed/' . $this->my_custom_feed_url );
		$this->assertQueryTrue( 'is_feed' );
	}
}
