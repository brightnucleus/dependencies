<?php
/**
 * DependencyManagerInterface Interface.
 *
 * @package   BrightNucleus\Dependency
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.brightnucleus.com/
 * @copyright 2015 Alain Schlesser, Bright NucleusInterface
 */

namespace BrightNucleus\Dependency;

use BrightNucleus\Contract\Enqueueable;
use BrightNucleus\Contract\Registerable;

/**
 * Register and enqueue dependencies that are listed in the config file.
 *
 * @since   0.1.0
 *
 * @package BrightNucleus\Dependency
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
interface DependencyManagerInterface extends Registerable, Enqueueable {

}
