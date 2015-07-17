# megumi/wp-yahoo-feed

[![Build Status](https://travis-ci.org/megumi-wp-composer/wp-yahoo-feed.svg?branch=master)](https://travis-ci.org/megumi-wp-composer/wp-yahoo-feed) [![Latest Stable Version](https://poser.pugx.org/megumi/wp-yahoo-feed/v/stable.svg)](https://packagist.org/packages/megumi/wp-yahoo-feed) [![Total Downloads](https://poser.pugx.org/megumi/wp-yahoo-feed/downloads.svg)](https://packagist.org/packages/megumi/wp-yahoo-feed) [![Latest Unstable Version](https://poser.pugx.org/megumi/wp-yahoo-feed/v/unstable.svg)](https://packagist.org/packages/megumi/wp-yahoo-feed) [![License](https://poser.pugx.org/megumi/wp-yahoo-feed/license.svg)](https://packagist.org/packages/megumi/wp-yahoo-feed)

Helper class Generates the custom feed for Yahoo Japan for WordPress plugin.

* Cut the title in 28 chars.
* Replace the `guid` from uri to ID.
* Filter the HTML of `<description />` that is allowed by Yahoo.
* Set post-thumbnail to `<enclosure />` and add it to `<item />`.
* Add `caption` attribute to `<img />` in `<description />`;


## Installation

Create a composer.json in your plugin root or mu-plugins

```
{
    "require": {
        "megumi/wp-yahoo-feed": "*"
    }
}
```

Place the following code into your plugin.

```
require_once dirname( __FILE__ ) . '/vendor/autoload.php';
```

Then:

```
$ composer install
```

## How to use

```
require_once dirname( __FILE__ ) . '/vendor/autoload.php';

$yahoo_feed = new Megumi\WP\Yahoo_Feed( 'my-feed' );
$yahoo_feed->register();
```

Then access to:

`http://example.com/feed/my-feed` or `http://example.com/feed/?type=my-feed`

## Customization

### Filter Hooks

* `yahoo_feed_template_{$feed_name}` - Filters the feed template.
* `yahoo_feed_item_title_width_{$feed_name}` - Filters the width of item's title.
* `yahoo_feed_item_category_{$feed_name}` - Filters the category of the item.
* `yahoo_feed_item_enclosure_image_size_{$feed_name}` - Filters the image size of post-thumbnail.
* `yahoo_feed_item_default_enclosure_{$feed_name}` - Filters the default post-thumbnail.
* `yahoo_feed_item_excerpt_{$feed_name}` - Filters the descrption of the item.
* `yahoo_feed_item_allowed_html_{$feed_name}` - Filters the allowed html.

### Action Hooks

* `yahoo_feed_item_{$feed_name}` - Fires at item node in the feed.

### Action Hooks

## Contributing

Clone this project.

```
$ git clone git@github.com:megumi-wp-composer/wp-yahoo-feed.git
```

### Run testing

Initialize the testing environment locally:

(you'll need to already have mysql, svn and wget available)

```
$ bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

Install phpunit.

```
$ composer install
```

The unit test files are in the `tests/` directory.

To run the unit tests, just execute:

```
$ phpunit
```

### Issue

[https://github.com/megumi-wp-composer/wp-yahoo-feed/issues](https://github.com/megumi-wp-composer/wp-yahoo-feed/issues)
