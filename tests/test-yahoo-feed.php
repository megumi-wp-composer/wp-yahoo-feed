<?php

use Megumi\WP\YahooFeed;

/**
 * @runTestsInSeparateProcesses
 */
class YahooFeed_Test extends WP_UnitTestCase
{
	private $my_custom_feed_url = 'my-feed';

	public function setUp()
	{
		parent::setUp();

		update_option( 'permalink_structure', '/archives/%post_id%' );
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules();

		$this->factory->post->create_many( 25 );
	}

	public function tearDown() {
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
		$this->assertSame( true, $yahoo_feed->is_yahoo_feed() );

		$this->go_to( '/feed/' );
		$this->assertSame( true, is_feed() );

		$this->assertSame( false, $yahoo_feed->is_yahoo_feed() );

		set_query_var( 'type', $this->my_custom_feed_url );
		$this->assertSame( true, $yahoo_feed->is_yahoo_feed() );
	}
}
