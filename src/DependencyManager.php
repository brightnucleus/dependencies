<?php
/**
 * DependencyManager Class.
 *
 * @package   BrightNucleus\Dependency
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.brightnucleus.com/
 * @copyright 2015 Alain Schlesser, Bright NucleusInterface
 */

namespace BrightNucleus\Dependency;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Exception\InvalidArgumentException;
use BrightNucleus\Exception\RuntimeException;

/**
 * Register and enqueue dependencies that are listed in the config file.
 *
 * @since   0.1.0
 *
 * @package BrightNucleus\Dependency
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class DependencyManager {

	use ConfigTrait;

	/*
	 * Default dependency handler implementations.
	 */
	const DEFAULT_SCRIPT_HANDLER = '\BrightNucleus\Dependency\Script';
	const DEFAULT_STYLE_HANDLER  = '\BrightNucleus\Dependency\Style';

	/*
	 * Names of the configuration keys.
	 */
	const KEY_DEPENDENCY_SUBTREE = 'BrightNucleus\Dependencies';
	const KEY_HANDLERS           = 'handlers';
	const KEY_SCRIPTS            = 'scripts';
	const KEY_STYLES             = 'styles';

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
	 * @var array
	 */
	protected $handlers = [ ];

	/**
	 * Instantiate DependencyManager object.
	 *
	 * @since 0.1.0
	 *
	 * @param ConfigInterface $config         ConfigInterface object that
	 *                                        contains dependency settings.
	 * @param string          $config_key     Optional. Key of a configuration
	 *                                        subtree.
	 * @throws RuntimeException If the config could not be processed.
	 * @throws InvalidArgumentException If no dependency handlers were
	 *                                        specified.
	 */
	public function __construct( ConfigInterface $config, $config_key = null ) {
		$this->processConfig(
			$config,
			$config_key ?: self::KEY_DEPENDENCY_SUBTREE
		);
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
				$this->handlers[] = $handler;
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
			function ( $handler, $dependency_type ) use ( $config ) {
				if ( ! empty( $config[ $dependency_type ] ) ) {
					$this->dependencies[ $dependency_type ] = $config[ $dependency_type ];
				}
			} );
	}

	/**
	 * Register all dependencies.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $context Optional. The context to pass to the dependencies.
	 */
	public function register( $context = null ) {
		array_walk( $this->dependencies,
			[ $this, 'register_dependency_type' ], $context );
	}

	/**
	 * Enqueue all dependencies.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $context Optional. The context to pass to the dependencies.
	 */
	public function enqueue( $context = null ) {
		array_walk( $this->dependencies,
			[ $this, 'enqueue_dependency_type' ], $context );
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
	public function enqueue_dependency_type( $dependencies, $dependency_type, $context = null ) {
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
	public function register_dependency_type( $dependencies, $dependency_type, $context = null ) {
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
	 * @param mixed  $context        Context to pass to the dependencies.
	 *                               Contains the type of the dependency at key
	 *                               'dependency_type'.
	 */
	public function register_dependency( $dependency, $dependency_key, $context ) {
		/** @var \BrightNucleus\Contract\Registerable $handler */
		$handler = new $this->handlers[$context['dependency_type']];
		$handler->register( $dependency );
		\add_action( 'wp_enqueue_scripts',
			[ $this, 'enqueue' ] );
		\add_action( 'admin_enqueue_scripts',
			[ $this, 'enqueue' ] );
		if ( array_key_exists( 'localize', $dependency ) ) {
			$this->localize( $dependency, $context );
		}
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
	public function localize( $dependency, $context ) {
		$localize = $dependency['localize'];
		$data     = $localize['data'];
		if ( is_callable( $data ) ) {
			$data = $data( $context );
		}
		\wp_localize_script( $dependency['handle'], $localize['name'], $data );
	}

	/**
	 * Enqueue a single dependency.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $dependency     Configuration data of the dependency.
	 * @param string $dependency_key Config key of the dependency.
	 * @param mixed  $context        Context to pass to the dependencies.
	 *                               Contains the type of the dependency at key
	 *                               'dependency_type'.
	 */
	public function enqueue_dependency( $dependency, $dependency_key, $context ) {
		if ( ! $this->is_needed( $dependency, $context ) ) {
			return;
		}
		/** @var \BrightNucleus\Contract\Enqueueable $handler */
		$handler = new $this->handlers[$dependency_type];
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
}
