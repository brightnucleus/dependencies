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

use BrightNucleus\Contract\Enqueueable;
use BrightNucleus\Contract\Registerable;

/**
 * Interface DependencyHandlerInterface.
 *
 * @since   0.1.0
 *
 * @package BrightNucleus\Dependency
 */
interface DependencyHandlerInterface extends Registerable, Enqueueable {

	/**
	 * Maybe enqueue a dependency that has been registered outside of the
	 * Dependency Manager.
	 *
	 * @since 0.2.3
	 *
	 * @param string $handle Handle of the dependency to enqueue.
	 * @return bool Whether the handle was found and enqueued.
	 */
	public function maybe_enqueue( $handle );

	/**
	 * Check whether a specific handle has been registered.
	 *
	 * @since 0.3.3
	 *
	 * @param string $handle The handle to check
	 * @return bool Whether it is registered or not.
	 */
	public function is_registered( $handle );

	/**
	 * Check whether a specific handle has been enqueued.
	 *
	 * @since 0.3.3
	 *
	 * @param string $handle The handle to check
	 * @return bool Whether it is enqueued or not.
	 */
	public function is_enqueued( $handle );
}
