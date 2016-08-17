# Bright Nucleus Dependencies Component

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/brightnucleus/dependencies/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/brightnucleus/dependencies/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/brightnucleus/dependencies/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/brightnucleus/dependencies/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/brightnucleus/dependencies/badges/build.png?b=master)](https://scrutinizer-ci.com/g/brightnucleus/dependencies/build-status/master)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/1f3ad4eb15584dfba0408c9de094035f)](https://www.codacy.com/app/BrightNucleus/dependencies)
[![Code Climate](https://codeclimate.com/github/brightnucleus/dependencies/badges/gpa.svg)](https://codeclimate.com/github/brightnucleus/dependencies)

[![Latest Stable Version](https://poser.pugx.org/brightnucleus/dependencies/v/stable)](https://packagist.org/packages/brightnucleus/dependencies)
[![Total Downloads](https://poser.pugx.org/brightnucleus/dependencies/downloads)](https://packagist.org/packages/brightnucleus/dependencies)
[![Latest Unstable Version](https://poser.pugx.org/brightnucleus/dependencies/v/unstable)](https://packagist.org/packages/brightnucleus/dependencies)
[![License](https://poser.pugx.org/brightnucleus/dependencies/license)](https://packagist.org/packages/brightnucleus/dependencies)

This is a WordPress dependencies component that lets you define dependencies through a config file. The dependencies you define in this way will then get registered and enqueued automatically.

## Table Of Contents

* [Installation](#installation)
* [Basic Usage](#basic-usage)
	* [Configuration File](#configuration-file)
	* [Initialization](#initialization)
* [Advanced Features](#advanced-features)
	* [Conditional Registration](#conditional-registration)
	* [Custom Context Data](#custom-context-data)
	* [Manual Enqueueing](#manual-enqueueing)
* [Contributing](#contributing)
* [License](#license)

## Installation

The best way to use this component is through Composer:

```BASH
composer require brightnucleus/dependencies
```

## Basic Usage

### Configuration File

To use the `DependencyManager`, you first need to create a config file ( see [`brightnucleus/config`](https://github.com/brightnucleus/config) ), in which you define your dependencies. At the root level of the config that is passed into the `DependencyManager`'s constructor, you'll have one key for each type of dependency, and a `handlers` key that defines the class that handles that specific type of dependency. As an example, here is the setup that the `DependencyManager` has out-of-the-box support for:
```PHP
<?php
$dependencies_config = [
	'styles'   => [ <individual style dependencies go here> ],
	'scripts'  => [ <individual script dependencies go here> ],
	'handlers' => [
		'scripts' => 'BrightNucleus\Dependency\ScriptHandler',
		'styles'  => 'BrightNucleus\Dependency\StyleHandler',
	],
];
```

You can define arbitrary types of dependencies, as long as you also provide the corresponding class that implements the `DependencyHandlerInterface` for that dependency type.

The arguments you need to provide for each dependency depend on the implementation of the class that implements the `DependencyHandlerInterface`, with one important requirement: __Each dependency needs a `handle` key to uniquely identify it.__

For the two dependency handlers that are provided out-of-the-box, the arguments are passed through to the corresponding `wp_register_*` and `wp_enqueue_*` functions, so they accept the same arguments as these functions.

__Scripts__

* __`handle`__: (string) (required) Unique name of the script.

* __`src`__: (string) (required) URL to the script resource.

* __`deps`__: (array) (optional) Array of the handles of all the registered scripts that this script depends on.

	Default: _`array()`_

* __`ver`__: (string) (optional) String specifying the script version number. If false, WordPress version gets used.

	Default: _`false`_

* __`in_footer`__: (boolean) (optional) Whether to enqueue the script into the bottom section of the `<body>` section or not. If not, it gets enqueued into the `<head>`.

	Default: _`false`_

__Styles__

* __`handle`__: (string) (required) Unique name of the stylesheet.

* __`src`__: (string) (required) URL to the stylesheet resource.

* __`deps`__: (array) (optional) Array of the handles of all the registered stylesheets that this stylesheet depends on.

	Default: _`array()`_

* __`ver`__: (string) (optional) String specifying the stylesheet version number. If false, WordPress version gets used.

	Default: _`false`_

* __`media`__: (boolean) (optional) String specifying the media for which this stylesheet has been defined.

	Default: _`'all'`_

### Initialization

Initializing the `DependencyManager` and hooking it up to WordPress is straight-forward. `DependencyManager`'s constructor takes a `ConfigInterface` implementation to do its work. In the following example, we are using the standard `Config` implementation and its `ConfigTrait` companion that come with the [`brightnucleus/config`](https://github.com/brightnucleus/config) package.

You can then hook the `DependencyManager::register()` method to a WordPress action so that it is executed at the right moment.

```PHP
<?php
namespace BrightNucleus\Example;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Dependency\DependencyManager;

class ExamplePlugin {

	use ConfigTrait;

	/**
	 * Instantiate a Plugin object.
	 *
	 * @param ConfigInterface $config Config to parametrize the object.
	 */
	public function __construct( ConfigInterface $config ) {
		$this->processConfig( $config );
	}

	/**
	 * Launch the initialization process.
	 */
	public function run() {
		add_action( 'init', [ $this, 'init_dependencies' ], 10 );
	}

	/**
	 * Initialize DependencyManager and hook it up to WordPress.
	 */
	public function init_dependencies() {

		// Initialize dependencies.
		$dependencies = new DependencyManager( $this->config );
		// Register dependencies.
		add_action( 'init', [ $dependencies, 'register' ], 20 );
	}
}
```

## Advanced Features

### Conditional Registration

For each dependency, you can add an `is_needed` key that will be checked to decide whether the dependency should be loaded or not. `is_needed` can be any expression that gets evaluated as a boolean, or a closure ( `callable` ).

If `is_needed` contains a closure, the closure will be executed and gets an argument `$context` that it can use to make an informed decision. The code that calls the initial `DependencyManager::register()` can pass any useful value into `$context` and can thus communicate with these `is_needed` closures.

Example of an `is_needed` key in a config:

```PHP
<?php
$dependencies_config = [
	'script' => [
		'handle' => 'test_script',
		'is_needed' => function ( $context ) {
			return array_key_exists( 'page_template', $context )
			&& 'test-template' === $context['page_template'];
		},
	],
];
```

### Custom Context Data

Custom data can be passed through the `DependencyManager` using the `$context` argument. This can be used in your `is_needed` closures to control registeration and enqueueing.

If you call the `DependencyManager::register()` method directly, you can just add the `$context` argument to that call.
```PHP
<?php
/**
 * Register all dependencies.
 *
 * @param mixed $context Optional. The context to pass to the dependencies.
 */
public function register( $context = null );
```

If you want to hook the `register()` method up to a WordPress action, however, you need to do this indirectly, like in the following example:

```PHP
<?php
class ExamplePlugin {

	// <...>

	/** @var BrightNucleus\Dependency\DependencyManager $dependencies */
	protected $dependencies;

	/**
	 * Initialize DependencyManager and hook it up to WordPress.
	 */
	public function init_dependencies() {

		// Initialize dependencies.
		$this->dependencies = new DependencyManager( $this->config );
		// Register dependencies.
		add_action( 'init', [ $this, 'register_with_context' ], 20 );
	}

	/**
	 * Register dependencies and pass collected context into them.
	 */
	public function register_with_context() {
		$context['example_key'] = 'example_value';
		$this->dependencies->register( $context );
	}
}
```

### Manual Enqueueing

Default behavior of the `DependencyManager` is to automatically enqueue all of the dependencies once you've registered them. In some cases, you might want to override this and take care of the enqueueing yourself.

To override the default behavior, you need to set the second argument `$enqueue` to the `DependencyManager` constructor to `false`.

You can then enqueue individual dependencies by their handle by using the `DependencyManager::enqueue_handle()` method:
```PHP
<?php
/**
 * Enqueue a single dependency retrieved by its handle.
 *
 * @param string $handle   The dependency handle to enqueue.
 * @param mixed  $context  Optional. The context to pass to the
 *                         dependencies.
 * @param bool   $fallback Whether to fall back to dependencies registered
 *                         outside of DependencyManager. Defaults to false.
 * @return bool Returns whether the handle was found or not.
 */
public function enqueue_handle( $handle, $context = null, $fallback = false );
```

As with the `register()` method, you can pass in a `$context` that can be checked in the `is_needed` closure.

If you set the third argument `$fallback` to `true`, any `$handle` that has not been found in the collection of dependencies registered through `DependencyManager` will also be searched in the dependencies registered outside of `DependencyManager`. This is a convenient way to enqueue dependencies that come built-in with a WordPress install.

## Contributing

All feedback / bug reports / pull requests are welcome.

Please use the provided `pre-commit` hook. To install it, run the following command from the project's root:
```BASH
ln -s ../../.pre-commit .git/hooks/pre-commit
```

## License

This code is released under the MIT license.

For the full copyright and license information, please view the [`LICENSE`](LICENSE) file distributed with this source code.
