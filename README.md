# megumi/yahoo-feed

[![Build Status](https://travis-ci.org/megumi-wp-composer/yahoo-feed.svg?branch=master)](https://travis-ci.org/megumi-wp-composer/yahoo-feed) [![Latest Stable Version](https://poser.pugx.org/megumi/yahoo-feed/v/stable.svg)](https://packagist.org/packages/megumi/yahoo-feed) [![Total Downloads](https://poser.pugx.org/megumi/yahoo-feed/downloads.svg)](https://packagist.org/packages/megumi/yahoo-feed) [![Latest Unstable Version](https://poser.pugx.org/megumi/yahoo-feed/v/unstable.svg)](https://packagist.org/packages/megumi/yahoo-feed) [![License](https://poser.pugx.org/megumi/yahoo-feed/license.svg)](https://packagist.org/packages/megumi/yahoo-feed)

Helper class for the custom feed of Yahoo Japan.

## Installation

Create a composer.json in your project root.

```
{
    "require": {
        "megumi/yahoo-feed": "*"
    }
}
```

## Documentation

## Contributing

Clone this project.

```
$ git clone git@github.com:megumi-wp-composer/yahoo-feed.git
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

[https://github.com/megumi-wp-composer/yahoo-feed/issues](https://github.com/megumi-wp-composer/yahoo-feed/issues)
