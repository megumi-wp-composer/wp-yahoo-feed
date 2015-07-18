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

		$this->post_ids = $this->factory->post->create_many( 25 );

		foreach ( $this->post_ids as $pid ) {
			$attachment_id = $this->factory->attachment->create_object( 'image.jpg', $pid, array(
				'post_mime_type' => 'image/jpeg',
				'post_type' => 'attachment'
			) );
			set_post_thumbnail( $pid, $attachment_id );
		}

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

	/**
	 * @test
	 */
	public function do_feed()
	{
		$this->go_to( '/feed/' . $this->my_custom_feed_url );

		ob_start();
		$this->yahoo->do_feed();
		$feed = ob_get_clean();
		$xml = xml_to_array( $feed );

		$channel = xml_find( $xml, 'rss', 'channel' );
		$this->assertTrue( empty( $channel[0]['attributes'] ) );

		$title = xml_find( $xml, 'rss', 'channel', 'title' );
		$this->assertSame( get_option( 'blogname' ), $title[0]['content'] );

		$desc = xml_find( $xml, 'rss', 'channel', 'description' );
		$this->assertSame( get_option( 'blogdescription' ), $desc[0]['content'] );

		$link = xml_find( $xml, 'rss', 'channel', 'link' );
		$this->assertSame( get_option( 'siteurl' ), $link[0]['content'] );

		$pubdate = xml_find( $xml, 'rss', 'channel', 'lastBuildDate' );
		$this->assertSame( strtotime( get_lastpostmodified(  ) ), strtotime( $pubdate[0]['content'] ) );

		$items = xml_find( $xml, 'rss', 'channel', 'item' );
		$this->assertSame( intval( get_option( 'posts_per_page' ) ), count( $items ) );

		$enclosures = xml_find( $xml, 'rss', 'channel', 'item', 'enclosure' );
		$guids = xml_find( $xml, 'rss', 'channel', 'item', 'guid' );
		for ( $i = 0; $i < intval( get_option( 'posts_per_page' ) ); $i++ ) {
			$this->assertRegExp( '#^http://#', $enclosures[ $i ]['content'] );
			$this->assertRegExp( '#^[0-9]+$#', $guids[ $i ]['content'], 'GUID should be numeric.' );
		}
	}

	/**
	 * @test
	 */
	public function deleted_post_feed()
	{
		foreach ( $this->post_ids as $post_id ) {
			update_post_meta( $post_id, '_yahoo_feed_category_' . $this->my_custom_feed_url, 3 );
		}

		wp_delete_post( $this->post_ids[0] );

		$this->go_to( '/feed/' . $this->my_custom_feed_url );

		ob_start();
		$this->yahoo->do_feed();
		$feed = ob_get_clean();
		$xml = xml_to_array( $feed );

		$items = xml_find( $xml, 'rss', 'channel', 'item' );
		$this->assertSame( intval( get_option( 'posts_per_page' ) ) + 1, count( $items ), 'Feed should includes trashed items.' );

		$categories = xml_find( $xml, 'rss', 'channel', 'item', 'category' );
		$this->assertSame( "0", $categories[0]['content'] );
		$this->assertSame( "3", $categories[1]['content'] );

		$guids = xml_find( $xml, 'rss', 'channel', 'item', 'guid' );
		$this->assertRegExp( '#^[0-9]+$#', $guids[0]['content'] );
		$this->assertRegExp( '#^[0-9]+$#', $guids[1]['content'] );
	}

	/**
	 * @test
	 */
	public function tests_on_default_feed()
	{
		foreach ( $this->post_ids as $post_id ) {
			update_post_meta( $post_id, '_yahoo_feed_category_' . $this->my_custom_feed_url, 3 );
		}

		wp_delete_post( $this->post_ids[0] );

		$this->go_to( '/feed/' );

		ob_start();
		$this->yahoo->do_feed();
		$feed = ob_get_clean();
		$xml = xml_to_array( $feed );

		$items = xml_find( $xml, 'rss', 'channel', 'item' );
		$this->assertSame( intval( get_option( 'posts_per_page' ) ), count( $items ), 'Feed should includes trashed items.' );

		$categories = xml_find( $xml, 'rss', 'channel', 'item', 'category' );
		$this->assertSame( 'Uncategorized', $categories[0]['content'] );

		$guids = xml_find( $xml, 'rss', 'channel', 'item', 'guid' );
		$this->assertRegExp( '#^http://#', $guids[0]['content'] );

		$enclosures = xml_find( $xml, 'rss', 'channel', 'item', 'enclosure' );
		$this->assertSame( 0, count( $enclosures ) );
	}
}
