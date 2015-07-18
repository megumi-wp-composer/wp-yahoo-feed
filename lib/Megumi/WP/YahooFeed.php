<?php

namespace Megumi\WP;

class YahooFeed
{
	private $feed_name;
	private $allowed_html = array(
		'h2' => array(),
		'p' => array(),
		'blockquote' => array('style' => array()),
		'img' => array(
			'width' => array(),
			'height' => array(),
			'src' => array(),
			'alt' => array(),
			'caption' => array(),
		),
		'strong' => array(),
		'a' => array(
			'href' => array(),
			'title' => array()
		),
	);
	private $categories = array();

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
		add_filter( 'img_caption_shortcode', array( $this, 'img_caption_shortcode' ), 10, 3 );
		add_filter( 'yahoo_feed_item_excerpt_' . $this->feed_name, array( $this, 'yahoo_feed_item_excerpt' ), 10, 1 );
		add_filter( 'yahoo_feed_item_category_' . $this->feed_name, array( $this, 'yahoo_feed_item_category' ), 10, 1 );

		add_action( 'yahoo_feed_item_' . $this->feed_name, array( $this, 'yahoo_feed_item' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );

		if ( $this->get_categories() ) {
			add_action( 'add_meta_boxes', function(){
				add_meta_box(
					'yahoo_feed' . $this->feed_name,
					'Yahoo Category',
					array( $this, 'add_meta_boxes' ),
					'post',
					'side'
				);
			} );
			add_action( 'save_post', array( $this, 'save_post' ) );
		}

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
		/**
		 * Filters the feed template.
		 *
		 * @since initial-release
		 * @param string Path to the feed template.
		 */
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

		/**
		 * Fires at item node in the feed.
		 *
		 * @since initial-release
		 */
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

		/**
		 * Filters the width of item's title.
		 *
		 * @since initial-release
		 * @param string Path to the feed template.
		 */
		return mb_substr( $title, 0, apply_filters( 'yahoo_feed_item_title_width_' . $this->feed_name, 28 ), 'UTF-8' );
	}

	public function the_category_rss( $the_list, $type )
	{
		if ( ! $this->is_yahoo_feed() ) {
			return $the_list;
		}

		/**
		 * Filters the category of the item.
		 *
		 * @since initial-release
		 * @param string $the_list <category> node of the item
		 */
		return apply_filters( 'yahoo_feed_item_category_' . $this->feed_name, $the_list );
	}

	public function yahoo_feed_item()
	{
		if ( ! $this->is_yahoo_feed() ) {
			return;
		}

		if ( has_post_thumbnail( get_the_ID() ) ) {
			$post_thumbnail_id = get_post_thumbnail_id( get_the_ID() );

			/**
			 * Filters the image size of post-thumbnail.
			 *
			 * @since initial-release
			 * @param string 'full' or 'large' or ...
			 */
			$attachment_image_src = wp_get_attachment_image_src( $post_thumbnail_id, apply_filters( 'yahoo_feed_item_enclosure_image_size_' . $this->feed_name, 'full' ), false );
			$enclosure = $attachment_image_src[0];
		} else {
			/**
			 * Filters the default post-thumbnail.
			 *
			 * @since initial-release
			 * @param string URL to the default post-thumbnail
			 */
			$enclosure = apply_filters( 'yahoo_feed_item_default_enclosure_' . $this->feed_name, '' );
		}

		echo '<enclosure>' . esc_url( $enclosure ) . '</enclosure>';
	}

	public function the_excerpt_rss( $excerpt )
	{
		if ( ! $this->is_yahoo_feed() ) {
			return $excerpt;
		}

		$content = apply_filters( 'the_content', get_the_content() );

		/**
		 * Filters the descrption of the item.
		 *
		 * @since initial-release
		 * @param string $content The content
		 */
		return apply_filters( 'yahoo_feed_item_excerpt_' . $this->feed_name, $content );
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

	public function img_caption_shortcode( $empty, $attr, $content )
	{
		if ( ! $this->is_yahoo_feed() ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'width'	  => '',
			'caption' => '',
		), $attr, 'caption' );

		$atts['width'] = (int) $atts['width'];
		if ( $atts['width'] < 1 || empty( $atts['caption'] ) )
			return $content;

		$content = do_shortcode( $content );
		if ( empty( $atts['caption'] ) ||  preg_match( "/<img.*caption=.*?>/i", $content ) ) {
			return '<p>' . $content . '</p>'; // 最初からcaptionがある場合
		} else {
			return '<p>' . str_replace( "<img", '<img caption="' . esc_attr( $atts['caption'] ) . '"', $content ) . '</p>';
		}
	}

	public function yahoo_feed_item_excerpt( $content )
	{
		$allowed_html = $this->get_allowed_html();

		return wp_kses(
			$content,
			$allowed_html,
			array('http', 'https')
		);
	}

	public function yahoo_feed_item_category( $category_list )
	{
		return '<category>'.intval( get_post_meta( get_the_ID(), '_yahoo_feed_category_' . $this->feed_name, true ) ).'</category>';
	}

	public function add_meta_boxes( $post )
	{
			wp_nonce_field( 'yahoo_feed_category_' . $this->feed_name, 'yahoo_feed_category_nonce_' . $this->feed_name );
			$value = get_post_meta( $post->ID, '_yahoo_feed_category_' . $this->feed_name, true );
			if ( empty( $value ) ) {
				$value = '0';
			}

			echo '<ul>';

			foreach ( $this->get_categories() as $key => $cat ) {
				printf(
					'<li><label><input type="radio" name="%1$s" value="%2$s" %4$s /> %3$s</label></li>',
					esc_attr('yahoo_feed_category_' . $this->feed_name),
					esc_attr($key),
					esc_html($cat),
					( intval( $value ) === intval( $key ) ) ? 'checked="checked"' : ''
				);
			}

			echo '</ul>';
	}

	public function save_post( $post_id )
	{
		if ( ! isset( $_POST['yahoo_feed_category_nonce_' . $this->feed_name] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['yahoo_feed_category_nonce_' . $this->feed_name], 'yahoo_feed_category_' . $this->feed_name ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['yahoo_feed_category_' . $this->feed_name] ) ) {
			return;
		}

		update_post_meta( $post_id, '_yahoo_feed_category_' . $this->feed_name, intval( $_POST['yahoo_feed_category_' . $this->feed_name] ) );
	}

	public function is_yahoo_feed()
	{
		if ( is_feed( 'rss2' ) && $this->feed_name === get_query_var( 'type' ) ) {
			return true;
		} else {
			return is_feed( $this->feed_name );
		}
	}

	public function set_categories( $categories )
	{
		$this->categories = $categories;
	}

	public function get_categories()
	{
		return $this->categories;
	}

	private function get_allowed_html()
	{
		/**
		 * Filters the allowed html
		 *
		 * @since initial-release
		 * @param array Allowed htmls and attributes
		 */
		return apply_filters( 'yahoo_feed_item_allowed_html_' . $this->feed_name, $this->allowed_html );
	}
}
