<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\utils;

use function sin;
use const M_PI;

class MathHelper{

	const TABLE_N = 0xffff;
	const DEG_RAD = M_PI / 180;
	const RAD_DEG = 180 / M_PI;
	const COS_OFFSET = (self::TABLE_N + 1) / 4;
	/** @var float[] */
	private static ?array $table = null;

	public static function sin(float $f) : float{
		if(self::$table === null) self::initTable();
		return self::$table[((int) ($f * 10430.378)) & self::TABLE_N];
	}

	public static function initTable() : void{
		for($i = 0; $i <= self::TABLE_N; $i++){
			self::$table[$i] = sin($i * M_PI * 2 / self::TABLE_N);
		}
	}

	public static function cos(float $f) : float{
		if(self::$table === null) self::initTable();
		return self::$table[((int) ($f * 10430.378 + self::COS_OFFSET)) & self::TABLE_N];
	}
}
