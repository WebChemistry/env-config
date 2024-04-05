<?php declare(strict_types = 1);

namespace WebChemistry\EnvConfig;

use WebChemistry\EnvConfig\Adapter\Adapter;
use WebChemistry\EnvConfig\Adapter\EnvAdapter;
use WebChemistry\EnvConfig\Adapter\NeonAdapter;

final class Generator
{

	private const Adapters = [
		'env' => EnvAdapter::class,
		'neon' => NeonAdapter::class,
	];

	/**
	 * @param non-empty-list<string> $arguments
	 */
	public function generate(array $arguments): void
	{
		$script = $arguments[0];
		$adapter = $arguments[1] ?? null;
		$input = $arguments[2] ?? null;
		$output = $arguments[3] ?? null;

		if (!$adapter || !$input || !$output) {
			$adapters = implode('|', array_keys(self::Adapters));
			echo "Usage: $script <adapter=$adapters> <input {file}> <output {file}>\n";

			exit(1);
		}

		/** @var class-string<Adapter> $adapterClass */
		$adapterClass = self::Adapters[$adapter] ?? null;

		if (!$adapterClass) {
			echo "Adapter $adapter not found.\n";

			exit(1);
		}

		if (!file_exists($input)) {
			echo "Input file $input not found.\n";

			exit(1);
		}

		$outputDir = dirname($output);

		if (!is_dir($outputDir)) {
			echo "Output directory $outputDir not found.\n";

			exit(1);
		}

		$records = [];
		$env = getenv();

		foreach ($this->parseInput($input) as $target => $source) {
			if (!isset($env[$source])) {
				echo "Env $source not found.\n";

				exit(1);
			}

			$records[] = [$target, $env[$source]];
		}

		$content = (new $adapterClass)->generate($records);

		if (file_put_contents($output, $content) === false) {
			echo "Cannot write to file $output.\n";

			exit(1);
		}
	}

	/**
	 * @return array<string, string>
	 */
	private function parseInput(string $file): array
	{
		$contents = file_get_contents($file);

		if ($contents === false) {
			echo "Cannot read file $file.\n";

			exit(1);
		}

		$lines = explode("\n", $contents);
		$return = [];

		foreach ($lines as $i => $line) {
			$line = trim($line);

			if ($line === '') {
				continue;
			}

			$parts = explode('=', $line);

			if (count($parts) !== 2) {
				$i++;
				echo "Invalid line $i: $line.\n";

				exit(1);
			}

			[$key, $value] = $parts;

			$key = trim($key);
			$value = trim($value);

			if ($key === '' || $value === '') {
				$i++;
				echo "Invalid line $i: $line.\n";

				exit(1);
			}

			if (isset($return[$key])) {
				echo "Key $key is duplicated.\n";

				exit(1);
			}

			$return[$key] = $value;
		}

		return $return;
	}

}
