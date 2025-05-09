<?php

declare(strict_types=1);

namespace Bitrix\Main\Cli\Command\Dev\Templates;

use Bitrix\Main\Cli\Helper\Renderer\Template;

final class LocatorCodesTemplate implements Template
{
	public function __construct(
		private readonly string $code,
		private readonly string $module,
		private readonly array $services,
		private readonly bool $withTag = false,
	)
	{

	}

	public function getContent(): string
	{
		$names = $this->getNamesString();

		$codes = $this->getCodesString();

		$startTag = $this->withTag ? "<?php\n\nnamespace PHPSTORM_META\n{" : '';
		$endTag = $this->withTag ?'}' : '';

		$startRegion = $this->withTag ? "#region autogenerated services for module {$this->module}\n\n" : '';
		$endRegion = $this->withTag ? "\n\n#endregion\n" : '';

		return <<<PHP
{$startTag}
{$startRegion}
	registerArgumentsSet(
		'{$this->code}',
	{$names}
	);

	expectedArguments(\Bitrix\Main\DI\ServiceLocator::get(), 0, argumentsSet('{$this->code}'));

	override(
		\Bitrix\Main\DI\ServiceLocator::get(0),
		map([
		{$codes}
		]),
	);
{$endRegion}
{$endTag}
PHP;
	}

	private function getNamesString(): string
	{
		$separator = $this->withTag ? "',\n\t\t\t'" : "',\n\t\t'";

		return "\t'" . implode($separator, array_keys($this->services)) . "',";
	}

	private function getCodesString(): string
	{
		$map = $this->services;
		array_walk($map, static function(string &$value, string $key): void {
			$value = "'{$key}' => {$value},";
		});
		$map = array_values($map);

		$separator = $this->withTag ? "\n\t\t\t\t" : "\n\t\t\t";

		return "\t" . implode($separator, $map);
	}
}