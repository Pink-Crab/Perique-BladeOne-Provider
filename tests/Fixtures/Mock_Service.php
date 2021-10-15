<?php

declare(strict_types=1);

/**
 * Blade Config Mock
 *
 * @since 1.1.2
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\BladeOne
 */

namespace PinkCrab\BladeOne\Tests\Fixtures;

class Mock_Service {
	public function get_cache_file_extension(): string {
		return '.mock-cache';
	}
}
