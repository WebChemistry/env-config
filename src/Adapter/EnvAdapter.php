<?php declare(strict_types = 1);

namespace WebChemistry\EnvConfig\Adapter;

use Override;

final class EnvAdapter implements Adapter
{

	#[Override]
	public function generate(array $records): string
	{
		$content = '';

		foreach ($records as [$key, $value]) {
			if (!preg_match('#^[A-Z0-9_]+$#', $key)) {
				echo "Invalid name $key\n";

				exit(1);
			}

			$content .= "$key=$value\n";
		}

		return $content;
	}

}
