<?php
/**
 * AbstractDependencyHandler Class
 *
 * @package   BrightNucleus\Dependency
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 * @license   MIT
 * @link      http://www.brightnucleus.com/
 * @copyright 2015-2016 Alain Schlesser, Bright Nucleus
 */

namespace BrightNucleus\Dependency;

use BrightNucleus\Exception\InvalidArgumentException;
use BrightNucleus\Invoker\FunctionInvokerTrait;

/**
 * Abstract dependency.
 *
 * @since   0.1.0
 *
 * @package BrightNucleus\Dependency
 */
abstract class AbstractDependencyHandler implements DependencyHandlerInterface {

	use FunctionInvokerTrait;

	/**
	 * Register the dependency's assets.
	 *
	 * @since 0.1.0
	 *
	 * @param array|null $args          Optional. Array of arguments that is
	 *                                  passed to the registration function.
	 * @throws InvalidArgumentException If the register function could not be
	 *                                  called.
	 */
	public function register( $args = null ) {
		$this->invokeFunction( $this->get_register_function(), (array) $args );
	}

	/**
	 * Get the name of the function that is used for registering the dependency.
	 *
	 * @since 0.1.0
	 *
	 * @return string Function name.
	 */
	abstract protected function get_register_function();

	/**
	 * Enqueue the dependency's assets.
	 *
	 * @since 0.1.0
	 *
	 * @param array|null $args          Optional. Array of arguments that is
	 *                                  passed to the enqueueing function.
	 * @throws InvalidArgumentException If the register function could not be
	 *                                  called.
	 */
	public function enqueue( $args = null ) {
		$this->invokeFunction( $this->get_enqueue_function(), (array) $args );
	}

	/**
	 * Get the name of the function that is used for enqueueing the dependency.
	 *
	 * @since 0.1.0
	 *
	 * @return string Function name.
	 */
	abstract protected function get_enqueue_function();

	/**
	 * Maybe enqueue a dependency that has been registered outside of the
	 * Dependency Manager.
	 *
	 * @since 0.2.3
	 *
	 * @param string $handle Handle of the dependency to enqueue.
	 * @return bool Whether the handle was found or not.
	 */
	public function maybe_enqueue( $handle ) {
		global $wp_scripts;
		if ( $this->is_registered( $handle ) ) {
			$enqueue = $this->get_enqueue_function();
			$enqueue( $handle );
			return true;
		}
		return false;
	}

	/**
	 * Check whether a specific handle has been registered.
	 *
	 * @since 0.2.3
	 *
	 * @param string $handle The handle to check
	 * @return bool Whether it is registered or not.
	 */
	abstract protected function is_registered( $handle );
}
