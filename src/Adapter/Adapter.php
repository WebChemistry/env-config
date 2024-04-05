<?php declare(strict_types = 1);

namespace WebChemistry\EnvConfig\Adapter;

interface Adapter
{

	/**
	 * @param array{0: string, 1: string}[] $records
	 */
	public function generate(array $records): string;

}
