<?php

namespace nkkollaw\Multitenancy\Validators;

class Subdomain
{
	public static function getReservedSubdomains($reserved_lists, $ignore_default_list)
	{
		if (is_array($reserved_lists)) {
			if (!$ignore_default_list) {
				$reserved_suddomains[] = __DIR__ . '/../../../../../reserved-subdomains.yaml';
			}
			$reserved_subdomains = [[]];
			foreach ($reserved_lists as $reserved_list) {
				if (is_array($reserved_list)) {
					$reserved_subdomains[] = $reserved_list;
				} else {
					$reserved_subdomains[] = self::readYAML($reserved_lists);
				}
			}

			return array_merge(...$reserved_subdomains);
		}

		return self::readYAML($reserved_lists);
	}

	public static function readYAML($yaml_file)
	{
		$yaml = file_get_contents($yaml_file);
		if (!$yaml) {
			throw new \Exception('unable to find YAML file');
		}

		$reserved_subdomains = \Symfony\Component\Yaml\Yaml::parse($yaml);
		if (!$reserved_subdomains) {
			throw new \Exception('unable to parse YAML');
		}

		return $reserved_subdomains;
	}

	public static function isRegex($str)
	{
		return strpos($str, '/') !== false;
	}

	public static function isReserved($subdomain, $reserved_lists = [], $ignore_default_list = false)
	{
		$reserved_subdomains = self::getReservedSubdomains($reserved_lists);

		$is_reserved = false;
		foreach ($reserved_subdomains as $reserved_subdomain) {
			if (self::isRegex($reserved_subdomain)) {
				if (preg_match($reserved_subdomain, $subdomain)) {
					$is_reserved = true;

					break;
				}
			} else {
				if ($reserved_subdomain == $subdomain) {
					$is_reserved = true;

					break;
				}
			}
		}

		return $is_reserved;
	}
}
