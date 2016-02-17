<?php
/**
 * DependencyHandlerInterface Interface.
 *
 * @package   BrightNucleus\Dependency
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.brightnucleus.com/
 * @copyright 2015-2016 Alain Schlesser, Bright Nucleus
 */

namespace BrightNucleus\Dependency;

use BrightNucleus\Contract\Enqueueable;
use BrightNucleus\Contract\Registerable;

/**
 * Dependency interface.
 *
 * @since   0.1.0
 *
 * @package BrightNucleus\Dependency
 */
interface DependencyHandlerInterface extends Registerable, Enqueueable {

}