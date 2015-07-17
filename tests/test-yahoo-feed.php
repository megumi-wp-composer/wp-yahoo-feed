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

		// activate the plugin
		$this->yahoo = new YahooFeed( $this->my_custom_feed_url );
		$this->yahoo->register_activation_hook();
		$this->yahoo->register();
	}

	public function tearDown() {
		parent::tearDown();
		$GLOBALS['wp_rewrite']->init();
	}

	/**
	 * @test
	 */
	public function permalink_structure()
	{
		$this->go_to( '/feed/' . $this->my_custom_feed_url );
		$this->assertQueryTrue( 'is_feed' );
		$this->assertSame( true, $this->yahoo->is_yahoo_feed() );

		$this->go_to( '/feed/' );
		$this->assertQueryTrue( 'is_feed' );

		$this->assertSame( false, $this->yahoo->is_yahoo_feed() );

		set_query_var( 'type', $this->my_custom_feed_url );
		$this->assertSame( true, $this->yahoo->is_yahoo_feed() );
	}

	/**
	 * @test
	 */
	public function the_title_rss()
	{
		$this->go_to( '/feed/' . $this->my_custom_feed_url );

		$this->assertSame(
			'1. これも事実同時にこんな始末院とかいうののためがやり',
			$this->yahoo->the_title_rss( '1. これも事実同時にこんな始末院とかいうののためがやりたでしょ。どうしても今を［＃「ようもちっともその妨害たらしいなりにやっでいですをは説明見ただろて、そうにはめがけましたたです。' )
		);

		$this->assertSame(
			28,
			mb_strlen( $this->yahoo->the_title_rss( '1. これも事実同時にこんな始末院とかいうののためがやりたでしょ。どうしても今を［＃「ようもちっともその妨害たらしいなりにやっでいですをは説明見ただろて、そうにはめがけましたたです。' ) )
		);

		$this->go_to( '/feed/' );

		$this->assertSame(
			'1. これも事実同時にこんな始末院とかいうののためがやりたでしょ。どうしても今を［＃「ようもちっともその妨害たらしいなりにやっでいですをは説明見ただろて、そうにはめがけましたたです。',
			$this->yahoo->the_title_rss( '1. これも事実同時にこんな始末院とかいうののためがやりたでしょ。どうしても今を［＃「ようもちっともその妨害たらしいなりにやっでいですをは説明見ただろて、そうにはめがけましたたです。' )
		);
	}

	/**
	 * @test
	 */
	public function option_rss_use_excerpt()
	{
		$this->go_to( '/feed/' . $this->my_custom_feed_url );
		$this->assertTrue( get_option( 'rss_use_excerpt' ), 'It always should be true on the custom feed.' );

		$this->go_to( '/' );
		$this->assertSame( '0', get_option( 'rss_use_excerpt' ), 'It should be WordPress default.' );
	}

	/**
	 * @test
	 */
	public function image_caption()
	{
		$this->assertSame(
			'<figure id="attachment_1692" style="width: 165px;" class="wp-caption alignnone"><a href="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif"><img class="size-full wp-image-1692" src="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif" alt="これはキャプションです。" width="165" height="210" /></a><figcaption class="wp-caption-text">これはキャプションです。</figcaption></figure>',
			do_shortcode( '[caption id="attachment_1692" align="alignnone" width="165"]<a href="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif"><img class="size-full wp-image-1692" src="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif" alt="これはキャプションです。" width="165" height="210" /></a> これはキャプションです。[/caption]' ),
			"It should be WordPress default."
		);

		$this->go_to( '/feed/' . $this->my_custom_feed_url );

		$this->assertSame(
			'<p><a href="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif"><img caption="これはキャプションです。" class="size-full wp-image-1692" src="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif" alt="これはキャプションです。" width="165" height="210" /></a></p>',
			do_shortcode( '[caption id="attachment_1692" align="alignnone" width="165"]<a href="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif"><img class="size-full wp-image-1692" src="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif" alt="これはキャプションです。" width="165" height="210" /></a> これはキャプションです。[/caption]' ),
			"The `caption` attribute should be added to `<img />`."
		);

		$this->assertSame(
			'<p><a href="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif"><img caption="これはキャプションです。" src="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif" alt="これはキャプションです。" width="165" height="210" /></a></p>',
			$this->yahoo->yahoo_feed_item_excerpt( do_shortcode( '[caption id="attachment_1692" align="alignnone" width="165"]<a href="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif"><img class="size-full wp-image-1692" src="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif" alt="これはキャプションです。" width="165" height="210" /></a> これはキャプションです。[/caption]' ) ),
			"The `caption` attribute should be added to `<img />`."
		);

		$this->assertSame(
			'<p><a href="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif"><img class="size-full wp-image-1692" src="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif" alt="これはキャプションです。" caption="もともとあるキャプション" width="165" height="210" /></a></p>',
			do_shortcode( '[caption id="attachment_1692" align="alignnone" width="165"]<a href="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif"><img class="size-full wp-image-1692" src="http://yahoo-feed.dev/wp-content/uploads/2014/01/spectacles1.gif" alt="これはキャプションです。" caption="もともとあるキャプション" width="165" height="210" /></a> これはキャプションです。[/caption]' ),
			"The `caption` already been at `<img />`."
		);
	}
}
