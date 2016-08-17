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
 * Interface DependencyManagerInterface.
 *
 * Register and enqueue dependencies that are listed in the config file.
 *
 * @since   0.1.0
 *
 * @package BrightNucleus\Dependency
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
interface DependencyManagerInterface extends Registerable, Enqueueable {

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
	public function enqueue_handle( $handle, $context = null, $fallback = false );
}
