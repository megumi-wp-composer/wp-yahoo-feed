<?php

namespace Megumi\WP;

class YahooFeed
{
	private $feed_name;

	public function __construct( $feed_name )
	{
		$this->feed_name = $feed_name;
	}

	public function register()
	{
		add_action( 'init', array( $this, 'init') );
		add_action( 'rss2_item', array( $this, 'rss2_item' ) );
		add_filter( 'the_guid', array( $this, 'guid') );
		add_filter( 'the_title_rss', array( $this, 'the_title_rss') );
		add_filter( 'the_category_rss', array( $this, 'the_category_rss' ), 10, 2 );
		add_filter( 'the_excerpt_rss', array( $this, 'the_excerpt_rss' ), 10, 2 );
		add_filter( 'option_rss_use_excerpt', array( $this, 'option_rss_use_excerpt' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );

		add_action( 'yahoo_feed_item_' . $this->feed_name, array( $this, 'yahoo_feed_item' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );

		register_activation_hook( __FILE__, array( $this, 'register_activation_hook' ) );
	}

	public function register_activation_hook()
	{
		$this->init();
		flush_rewrite_rules();
	}

	public function init()
	{
		add_feed( $this->feed_name, array( $this, 'do_feed' ) );
	}

	public function do_feed()
	{
		load_template( apply_filters( 'yahoo_feed_template_' . $this->feed_name, ABSPATH . WPINC . '/feed-rss2.php' ) );
	}

	public function template_redirect()
	{
		if ( $this->is_yahoo_feed() ) {
			$this->do_feed();
		}
	}

	public function rss2_item()
	{
		if ( ! $this->is_yahoo_feed() ) {
			return;
		}

		do_action( 'yahoo_feed_item_' . $this->feed_name, $this->feed_name );
	}

	public function guid( $guid )
	{
		if ( ! $this->is_yahoo_feed() ) {
			return $guid;
		}

		return get_the_ID();
	}

	public function the_title_rss( $title )
	{
		if ( ! $this->is_yahoo_feed() ) {
			return $title;
		}

		return mb_strimwidth( $title, 0, apply_filters( 'yahoo_feed_item_title_width_' . $this->feed_name, 28 ) );
	}

	public function the_category_rss( $the_list, $type )
	{
		if ( ! $this->is_yahoo_feed() ) {
			return $the_list;
		}

		return apply_filters( 'yahoo_feed_item_category_' . $this->feed_name, $the_list );
	}

	public function yahoo_feed_item()
	{
		if ( ! $this->is_yahoo_feed() ) {
			return;
		}

		if ( has_post_thumbnail( get_the_ID() ) ) {
			$post_thumbnail_id = get_post_thumbnail_id( get_the_ID() );
			$attachment_image_src = wp_get_attachment_image_src( $post_thumbnail_id, apply_filters( 'yahoo_feed_item_enclosure_image_size_' . $this->feed_name, 'full' ), false );
			$enclosure = $attachment_image_src[0];
		} else {
			$enclosure = apply_filters( 'yahoo_feed_item_default_enclosure_' . $this->feed_name, '' );
		}

		echo '<enclosure>' . esc_url( $enclosure ) . '</enclosure>';
	}

	public function the_excerpt_rss( $excerpt )
	{
		if ( ! $this->is_yahoo_feed() ) {
			return $excerpt;
		}

		$content = get_the_content();
		$excerpt = wp_kses(
			$content,
			apply_filters( 'yahoo_feed_item_allowed_html_' . $this->feed_name, array(
				'h2' => array(),
				'p' => array(),
				'blockquote' => array('style' => array()),
				'img' => array(),
				'img' => array('width' => array(), 'height' => array(), 'src' => array(), 'alt' => array()),
				'strong' => array(),
				'a' => array(
			        'href' => array(),
			        'title' => array()
			    ),
			) ),
			array('http', 'https')
		);
		return apply_filters( 'yahoo_feed_item_excerpt_' . $this->feed_name, $excerpt, $content );
	}

	public function option_rss_use_excerpt( $option )
	{
		if ( ! $this->is_yahoo_feed() ) {
			return $option;
		}

		return true;
	}

	public function query_vars( $vars )
	{
		$vars[] = "type";
		return $vars;
	}

	public function is_yahoo_feed()
	{
		if ( is_feed( 'rss2' ) && $this->feed_name === get_query_var( 'type' ) ) {
			return true;
		} else {
			return is_feed( $this->feed_name );
		}
	}
}
