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

/**
 * Class ScriptHandler.
 *
 * @since   0.1.0
 *
 * @package BrightNucleus\Dependency
 */
class ScriptHandler extends AbstractDependencyHandler {

	/**
	 * Get the name of the function that is used for registering the dependency.
	 *
	 * @since 0.1.0
	 *
	 * @return string Function name.
	 */
	protected function get_register_function() {
		return 'wp_register_script';
	}

	/**
	 * Get the name of the function that is used for enqueueing the dependency.
	 *
	 * @since 0.1.0
	 *
	 * @return string Function name.
	 */
	protected function get_enqueue_function() {
		return 'wp_enqueue_script';
	}

	/**
	 * Check whether a specific handle has been registered.
	 *
	 * @since 0.2.3
	 * @since 0.3.3 Publicly accessible.
	 *
	 * @param string $handle The handle to check.
	 * @return bool Whether it is registered or not.
	 */
	public function is_registered( $handle ) {
		return \wp_script_is( $handle, 'registered' );
	}

	/**
	 * Check whether a specific handle has been enqueued.
	 *
	 * @since 0.3.3
	 *
	 * @param string $handle The handle to check.
	 * @return bool Whether it is enqueued or not.
	 */
	public function is_enqueued( $handle ) {
		return \wp_script_is( $handle, 'enqueued' );
	}
}
