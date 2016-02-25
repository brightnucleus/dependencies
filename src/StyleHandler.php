<?php
/**
 * StyleHandler Class.
 *
 * @package   BrightNucleus\Dependency
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.brightnucleus.com/
 * @copyright 2015 Alain Schlesser, Bright Nucleus
 */

namespace BrightNucleus\Dependency;

/**
 * StyleHandler dependency.
 *
 * @since   0.1.0
 *
 * @package BrightNucleus\Dependency
 */
class StyleHandler extends AbstractDependencyHandler {

	/**
	 * Get the name of the function that is used for registering the dependency.
	 *
	 * @since 0.1.0
	 *
	 * @return string Function name.
	 */
	protected function get_register_function() {
		return 'wp_register_style';
	}

	/**
	 * Check whether a specific handle has been registered.
	 *
	 * @since 0.2.3
	 *
	 * @param string $handle The handle to check
	 * @return bool Whether it is registered or not.
	 */
	protected function is_registered( $handle ) {
		return \wp_style_is( $handle, 'registered' );
	}

	/**
	 * Get the name of the function that is used for enqueueing the dependency.
	 *
	 * @since 0.1.0
	 *
	 * @return string Function name.
	 */
	protected function get_enqueue_function() {
		return 'wp_enqueue_style';
	}
}
