<?php declare(strict_types = 1);

namespace WebChemistry\EnvConfig\Adapter;

use Nette\Neon\Neon;
use Override;

final class NeonAdapter implements Adapter
{

	#[Override]
	public function generate(array $records): string
	{
		$struct = [];

		foreach ($records as [$key, $value]) {
			$path = explode('.', $key);

			$this->push($struct, $path, $value);
		}

		return Neon::encode($struct, Neon::BLOCK);
	}

	/**
	 * @param mixed[] $struct
	 * @param string[] $path
	 */
	private function push(array &$struct, array $path, string $value): void
	{
		$last = array_pop($path);

		foreach ($path as $key) {
			if (!isset($struct[$key])) {
				$struct[$key] = [];
			}

			if (!is_array($struct[$key])) {
				$type = get_debug_type($struct[$key]);
				echo "Key $key already exists, $type already assigned.\n";

				exit(1);
			}

			$struct = &$struct[$key];
		}

		if (isset($struct[$last])) {
			$type = get_debug_type($struct[$last]);

			echo "Key $last already exists, $type already assigned.\n";

			exit(1);
		}

		$struct[$last] = $value;
	}

}
