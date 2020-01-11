<?php
/**
 * Bright Nucleus Dependency Component.
 *
 * @package   BrightNucleus\Dependency
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 * @license   MIT
 * @link      http://www.brightnucleus.com/
 * @copyright 2015-2016 Alain Schlesser, Bright Nucleus
 */

namespace BrightNucleus\Dependency;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Exception\InvalidArgumentException;
use BrightNucleus\Exception\RuntimeException;

/**
 * Class DependencyManager.
 *
 * Register and enqueue dependencies that are listed in the config file.
 *
 * @since   0.1.0
 *
 * @package BrightNucleus\Dependency
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class DependencyManager implements DependencyManagerInterface {

	use ConfigTrait;

	/*
	 * Default dependency handler implementations.
	 */
	const DEFAULT_SCRIPT_HANDLER = '\BrightNucleus\Dependency\ScriptHandler';
	const DEFAULT_STYLE_HANDLER  = '\BrightNucleus\Dependency\StyleHandler';

	/*
	 * Names of the configuration keys.
	 */
	const KEY_HANDLERS = 'handlers';
	const KEY_SCRIPTS  = 'scripts';
	const KEY_STYLES   = 'styles';

	/**
	 * Hold the dependencies, grouped by type.
	 *
	 * @since 0.1.0
	 *
	 * @var array;
	 */
	protected $dependencies = [ ];

	/**
	 * Hold the handlers.
	 *
	 * @since 0.1.0
	 *
	 * @var DependencyHandlerInterface[]
	 */
	protected $handlers = [ ];

	/**
	 * Whether to enqueue immediately upon registration.
	 *
	 * @since 0.2.2
	 *
	 * @var bool
	 */
	protected $enqueue_immediately;

	/**
	 * Instantiate DependencyManager object.
	 *
	 * @since 0.1.0
	 *
	 * @param ConfigInterface $config   ConfigInterface object that contains
	 *                                  dependency settings.
	 * @param bool            $enqueue  Optional. Whether to enqueue
	 *                                  immediately. Defaults to true.
	 * @throws RuntimeException If the config could not be processed.
	 * @throws InvalidArgumentException If no dependency handlers were
	 *                                  specified.
	 */
	public function __construct( ConfigInterface $config, $enqueue = true ) {
		$this->processConfig( $config );
		$this->enqueue_immediately = $enqueue;
		$this->init_handlers();
		$this->init_dependencies();
	}

	/**
	 * Initialize the dependency handlers.
	 *
	 * @since 0.1.0
	 */
	protected function init_handlers() {
		$keys = [ self::KEY_SCRIPTS, self::KEY_STYLES ];
		foreach ( $keys as $key ) {
			if ( $this->hasConfigKey( $key ) ) {
				$this->add_handler( $key );
			}
		}
	}

	/**
	 * Add a single dependency handler.
	 *
	 * @since 0.1.0
	 *
	 * @param string $dependency The dependency type for which to add a handler.
	 */
	protected function add_handler( $dependency ) {
		if ( $this->hasConfigKey( $dependency ) ) {
			$handler = $this->hasConfigKey( self::KEY_HANDLERS, $dependency )
				? $this->getConfigKey( self::KEY_HANDLERS, $dependency )
				: $this->get_default_handler( $dependency );
			if ( $handler ) {
				$this->handlers[ $dependency ] = new $handler;
			}
		}
	}

	/**
	 * Get the default handler class for a given type of dependency.
	 *
	 * @since 0.1.0
	 *
	 * @param string $dependency The dependency that needs a handler.
	 * @return string|null Class name of the handler. Null if none.
	 */
	protected function get_default_handler( $dependency ) {
		switch ( $dependency ) {
			case self::KEY_STYLES:
				return self::DEFAULT_STYLE_HANDLER;
			case self::KEY_SCRIPTS:
				return self::DEFAULT_SCRIPT_HANDLER;
			default:
				return null;
		}
	}

	/**
	 * Initialize the actual dependencies.
	 *
	 * @since 0.1.0
	 */
	protected function init_dependencies() {
		array_walk( $this->handlers,
			function ( $handler, $dependency_type ) {
				if ( $this->hasConfigKey( $dependency_type ) ) {
					$this->dependencies[ $dependency_type ] = $this->init_dependency_type( $dependency_type );
				}
			} );
	}

	/**
	 * Initialize the dependencies of a given type.
	 *
	 * @since 0.2.2
	 *
	 * @param string $type The type of dependency to initialize.
	 * @return array Array of dependency configurations.
	 */
	protected function init_dependency_type( $type ) {
		$array = [ ];
		$data  = $this->getConfigKey( $type );
		foreach ( $data as $dependency ) {
			$handle           = array_key_exists( 'handle',
				$dependency ) ? $dependency['handle'] : '';
			$array[ $handle ] = $dependency;
		}
		return $array;
	}

	/**
	 * Register all dependencies.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $context Optional. The context to pass to the dependencies.
	 */
	public function register( $context = null ) {
		$context = $this->validate_context( $context );
		array_walk( $this->dependencies,
			[ $this, 'register_dependency_type' ], $context );
	}

	/**
	 * Validate the context to make sure it is an array.
	 *
	 * @since 0.2.1
	 *
	 * @param mixed $context The context as passed in by WordPress.
	 * @return array Validated context.
	 */
	protected function validate_context( $context ) {
		if ( is_string( $context ) ) {
			return [ 'wp_context' => $context ];
		}
		return (array) $context;
	}

	/**
	 * Enqueue all dependencies.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $context  Optional. The context to pass to the
	 *                        dependencies.
	 */
	public function enqueue( $context = null ) {
		$context = $this->validate_context( $context );

		array_walk( $this->dependencies,
			[ $this, 'enqueue_dependency_type' ], $context );
	}

	/**
	 * Enqueue a single dependency retrieved by its handle.
	 *
	 * @since 0.2.2
	 *
	 * @param string $handle   The dependency handle to enqueue.
	 * @param mixed  $context  Optional. The context to pass to the
	 *                         dependencies.
	 * @param bool   $fallback Whether to fall back to dependencies registered
	 *                         outside of DependencyManager. Defaults to false.
	 * @return bool Returns whether the handle was found or not.
	 */
	public function enqueue_handle( $handle, $context = null, $fallback = false ) {
		if ( ! $this->enqueue_internal_handle( $handle, $context ) ) {
			return $this->enqueue_fallback_handle( $handle );
		}
		return true;
	}

	/**
	 * Enqueue a single dependency from the internal dependencies, retrieved by
	 * its handle.
	 *
	 * @since 0.2.4
	 *
	 * @param string $handle   The dependency handle to enqueue.
	 * @param mixed  $context  Optional. The context to pass to the
	 *                         dependencies.
	 * @return bool Returns whether the handle was found or not.
	 */
	protected function enqueue_internal_handle( $handle, $context = null ) {
		list( $dependency_type, $dependency ) = $this->get_dependency_array( $handle );
		$context['dependency_type'] = $dependency_type;

		if ( ! $dependency ) {
			return false;
		}

		$handler = array_key_exists( $dependency_type, $this->handlers )
			? $this->handlers[ $dependency_type ]
			: null;
		if ( $handler && $handler->is_enqueued( $handle ) ) {
			return true;
		}

		$this->enqueue_dependency(
			$dependency,
			$handle,
			$context
		);

		$this->maybe_localize( $dependency, $context );
		$this->maybe_add_inline_script( $dependency, $context );

		return true;
	}

	/**
	 * Get the matching dependency for a given handle.
	 *
	 * @since 0.2.2
	 *
	 * @param string $handle The dependency handle to search for.
	 * @return array Array containing the dependency key as well as the
	 *                       dependency array itself.
	 */
	protected function get_dependency_array( $handle ) {
		foreach ( $this->dependencies as $type => $dependencies ) {
			if ( array_key_exists( $handle, $dependencies ) ) {
				return [ $type, $dependencies[ $handle ] ];
			}
		}
		// Handle not found, return an empty array.
		return [ '', null ];
	}

	/**
	 * Enqueue a single dependency.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $dependency     Configuration data of the dependency.
	 * @param string $dependency_key Config key of the dependency.
	 * @param mixed  $context        Optional. Context to pass to the
	 *                               dependencies. Contains the type of the
	 *                               dependency at key
	 *                               'dependency_type'.
	 */
	protected function enqueue_dependency( $dependency, $dependency_key, $context = null ) {
		$handler = $this->handlers[ $context['dependency_type'] ];
		$handle  = array_key_exists( 'handle', $dependency ) ? $dependency['handle'] : '';

		if ( $handle && $handler->is_enqueued( $handle ) ) {
			return;
		}

		if ( ! $this->is_needed( $dependency, $context ) ) {
			return;
		}

		$handler->enqueue( $dependency );
	}

	/**
	 * Check whether a specific dependency is needed.
	 *
	 * @since 0.1.0
	 *
	 * @param array $dependency Configuration of the dependency to check.
	 * @param mixed $context    Context to pass to the dependencies.
	 *                          Contains the type of the dependency at key
	 *                          'dependency_type'.
	 * @return bool Whether it is needed or not.
	 */
	protected function is_needed( $dependency, $context ) {
		$is_needed = array_key_exists( 'is_needed', $dependency )
			? $dependency['is_needed']
			: null;

		if ( null === $is_needed ) {
			return true;
		}

		return is_callable( $is_needed ) && $is_needed( $context );
	}

	/**
	 * Localize the script of a given dependency.
	 *
	 * @since 0.1.0
	 *
	 * @param array $dependency The dependency to localize the script of.
	 * @param mixed $context    Contextual data to pass to the callback.
	 *                          Contains the type of the dependency at key
	 *                          'dependency_type'.
	 */
	protected function maybe_localize( $dependency, $context ) {
		static $already_localized = [];
		if ( ! array_key_exists( 'localize', $dependency )
		     || array_key_exists( $dependency['handle'], $already_localized ) ) {
			return;
		}

		$localize = $dependency['localize'];
		$data     = $localize['data'];
		if ( is_callable( $data ) ) {
			$data = $data( $context );
		}

		wp_localize_script( $dependency['handle'], $localize['name'], $data );
		$already_localized[ $dependency['handle'] ] = true;
	}

	/**
	 * Add an inline script snippet to a given dependency.
	 *
	 * @since 0.1.0
	 *
	 * @param array $dependency The dependency to add the inline script to.
	 * @param mixed $context    Contextual data to pass to the callback.
	 *                          Contains the type of the dependency at key
	 *                          'dependency_type'.
	 */
	protected function maybe_add_inline_script( $dependency, $context ) {
		if ( ! array_key_exists( 'add_inline', $dependency ) ) {
			return;
		}

		$inline_script = $dependency['add_inline'];

		if ( is_callable( $inline_script ) ) {
			$inline_script = $inline_script( $context );
		}

		wp_add_inline_script( $dependency['handle'], $inline_script );
	}

	/**
	 * Enqueue a single dependency from the WP-registered dependencies,
	 * retrieved by its handle.
	 *
	 * @since 0.2.4
	 *
	 * @param string $handle The dependency handle to enqueue.
	 * @return bool Returns whether the handle was found or not.
	 */
	protected function enqueue_fallback_handle( $handle ) {
		$result = false;
		foreach ( $this->handlers as $handler ) {
			$result = $result || $handler->maybe_enqueue( $handle );
		}
		return $result;
	}

	/**
	 * Enqueue all dependencies of a specific type.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $dependencies    The dependencies to enqueue.
	 * @param string $dependency_type The type of the dependencies.
	 * @param mixed  $context         Optional. The context to pass to the
	 *                                dependencies.
	 */
	protected function enqueue_dependency_type( $dependencies, $dependency_type, $context = null ) {
		$context['dependency_type'] = $dependency_type;
		array_walk( $dependencies, [ $this, 'enqueue_dependency' ], $context );
	}

	/**
	 * Register all dependencies of a specific type.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $dependencies    The dependencies to register.
	 * @param string $dependency_type The type of the dependencies.
	 * @param mixed  $context         Optional. The context to pass to the
	 *                                dependencies.
	 */
	protected function register_dependency_type( $dependencies, $dependency_type, $context = null ) {
		$context['dependency_type'] = $dependency_type;
		array_walk( $dependencies, [ $this, 'register_dependency' ], $context );
	}

	/**
	 * Register a single dependency.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $dependency     Configuration data of the dependency.
	 * @param string $dependency_key Config key of the dependency.
	 * @param mixed  $context        Optional. Context to pass to the
	 *                               dependencies. Contains the type of the
	 *                               dependency at key
	 *                               'dependency_type'.
	 */
	protected function register_dependency( $dependency, $dependency_key, $context = null ) {
		$handler = $this->handlers[ $context['dependency_type'] ];
		$handler->register( $dependency );

		if ( $this->enqueue_immediately ) {
			$this->register_enqueue_hooks( $dependency, $context );
		}
	}

	/**
	 * Register the enqueueing to WordPress hooks.
	 *
	 * @since 0.2.2
	 *
	 * @param array $dependency Configuration data of the dependency.
	 * @param mixed $context    Optional. Context to pass to the dependencies.
	 *                          Contains the type of the dependency at key
	 *                          'dependency_type'.
	 */
	protected function register_enqueue_hooks( $dependency, $context = null ) {
		$priority = $this->get_priority( $dependency );

		foreach ( [ 'wp_enqueue_scripts', 'admin_enqueue_scripts' ] as $hook ) {
			add_action( $hook, [ $this, 'enqueue' ], $priority, 1 );
		}

		$this->maybe_localize( $dependency, $context );
		$this->maybe_add_inline_script( $dependency, $context );
	}

	/**
	 * Get the priority of a dependency.
	 *
	 * @since 0.2.2
	 *
	 * @param array $dependency Configuration data of the dependency.
	 * @return int Priority to use.
	 */
	protected function get_priority( $dependency ) {
		if ( array_key_exists( 'priority', $dependency ) ) {
			return intval( $dependency['priority'] );
		}
		return 10;
	}
}
