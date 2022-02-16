<?php

namespace Glhd\ConveyorBelt\Tests\Concerns;

trait ProvidesData
{
	protected function getDataProvider(array ...$data): array
	{
		if (empty($data)) {
			return [];
		}
		
		$labels = [[]];
		$results = [[]];
		
		while (count($data)) {
			$set = array_shift($data);
			
			$next_labels = [];
			$next_results = [];
			
			foreach ($set as $label => $value) {
				if (is_numeric($label)) {
					$label = $value;
				}
				
				foreach ($results as $index => $result) {
					$next_labels[] = [...$labels[$index], $label];
					$next_results[] = [...$result, $value];
				}
			}
			
			$labels = $next_labels;
			$results = $next_results;
		}
		
		$labels = array_map(function($labels) {
			return implode(', ', array_filter($labels));
		}, $labels);
		
		return array_combine($labels, $results);
	}
}
