<?php

namespace App\Console\Commands;

use App\Support\BangladeshGazetteer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SplitBangladeshGeojson extends Command
{
    protected $signature = 'map:split-geo
                            {--url=https://cdn.jsdelivr.net/npm/bd-geojson@1.0.5/src/data/bangladesh.geojson : Source GeoJSON URL}';

    protected $description = 'Split Bangladesh GeoJSON into a light all.json overview plus per-division files';

    public function handle(): int
    {
        $url = (string) $this->option('url');
        $this->info('Downloading '.$url);

        $response = Http::timeout(120)->get($url);
        if (! $response->successful()) {
            $this->error('Download failed: HTTP '.$response->status());

            return self::FAILURE;
        }

        $geo = $response->json();
        $features = collect($geo['features'] ?? [])->filter(fn ($feature) => ! empty($feature['geometry']));
        if ($features->isEmpty()) {
            $this->error('No features in source GeoJSON.');

            return self::FAILURE;
        }

        $groups = $features->groupBy(function (array $feature) {
            return $this->divisionName($feature);
        })->filter(fn ($rows, $division) => $division !== '');

        $base = public_path('geo');
        $divDir = $base.DIRECTORY_SEPARATOR.'divisions';
        if (! is_dir($divDir) && ! mkdir($divDir, 0775, true) && ! is_dir($divDir)) {
            $this->error('Could not create '.$divDir);

            return self::FAILURE;
        }

        $index = ['all' => 'all.json', 'divisions' => []];
        $allFeatures = [];

        foreach ($groups as $division => $rows) {
            $slug = $this->slug((string) $division);
            if ($slug === '') {
                continue;
            }
            $detail = [
                'type' => 'FeatureCollection',
                'name' => $division,
                'features' => $rows->values()->map(function (array $feature) use ($division) {
                    $feature['properties'] = $feature['properties'] ?? [];
                    $feature['properties']['country'] = 'Bangladesh';
                    $feature['properties']['division_name'] = $feature['properties']['division_name'] ?? $division;

                    return $feature;
                })->all(),
            ];

            $detailPath = 'divisions/'.$slug.'.json';
            $detail['features'] = array_map(function (array $feature) {
                if (! empty($feature['geometry'])) {
                    $feature['geometry'] = $this->simplifyGeometry($feature['geometry'], 0.004);
                }

                return $feature;
            }, $detail['features']);
            file_put_contents($base.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $detailPath), json_encode($detail, JSON_UNESCAPED_UNICODE));

            $allFeatures[] = [
                'type' => 'Feature',
                'properties' => [
                    'country' => 'Bangladesh',
                    'division_name' => $division,
                    'name' => $division,
                    'layer' => 'division',
                ],
                'geometry' => $this->simplifyGeometry($this->toMultiPolygon($rows->all()), 0.012),
            ];

            $index['divisions'][$division] = $detailPath;
            $this->line('  '.$division.' → geo/'.$detailPath.' ('.count($detail['features']).' shapes)');
        }

        ksort($index['divisions']);
        file_put_contents($base.DIRECTORY_SEPARATOR.'all.json', json_encode([
            'type' => 'FeatureCollection',
            'name' => 'Bangladesh',
            'features' => $allFeatures,
        ], JSON_UNESCAPED_UNICODE));
        file_put_contents($base.DIRECTORY_SEPARATOR.'index.json', json_encode($index, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $this->info('Wrote geo/all.json and '.count($index['divisions']).' division files.');

        return self::SUCCESS;
    }

    private function divisionName(array $feature): string
    {
        $properties = $feature['properties'] ?? [];
        $name = trim((string) ($properties['division_name'] ?? $properties['DIVISION'] ?? $properties['division'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $place = trim((string) ($properties['name'] ?? $properties['district_name'] ?? $properties['source_name'] ?? ''));
        if ($place === '') {
            return '';
        }

        $point = BangladeshGazetteer::locate($place);

        return trim((string) ($point['division'] ?? ''));
    }

    private function slug(string $name): string
    {
        $slug = Str::slug($name);
        $aliases = [
            'chittagong' => 'chattogram',
            'barisal' => 'barishal',
            'jessore' => 'jashore',
        ];

        return $aliases[$slug] ?? $slug;
    }

    /**
     * @param  array<int, array<string, mixed>>  $features
     * @return array{type: string, coordinates: array<int, mixed>}
     */
    private function toMultiPolygon(array $features): array
    {
        $parts = [];
        foreach ($features as $feature) {
            $geometry = $feature['geometry'] ?? [];
            $type = $geometry['type'] ?? '';
            $coordinates = $geometry['coordinates'] ?? [];
            if ($type === 'Polygon') {
                $parts[] = $coordinates;
            } elseif ($type === 'MultiPolygon') {
                foreach ($coordinates as $polygon) {
                    $parts[] = $polygon;
                }
            }
        }

        return [
            'type' => 'MultiPolygon',
            'coordinates' => $parts,
        ];
    }

    /**
     * @param  array{type?: string, coordinates?: mixed}  $geometry
     * @return array{type: string, coordinates: mixed}
     */
    private function simplifyGeometry(array $geometry, float $epsilon): array
    {
        $type = $geometry['type'] ?? 'MultiPolygon';
        $coordinates = $geometry['coordinates'] ?? [];

        if ($type === 'Polygon') {
            return [
                'type' => 'Polygon',
                'coordinates' => $this->simplifyPolygon($coordinates, $epsilon),
            ];
        }

        $parts = [];
        foreach ($coordinates as $polygon) {
            $simplified = $this->simplifyPolygon($polygon, $epsilon);
            if (count($simplified[0] ?? []) >= 4) {
                $parts[] = $simplified;
            }
        }

        return [
            'type' => 'MultiPolygon',
            'coordinates' => $parts,
        ];
    }

    /**
     * @param  array<int, mixed>  $polygon
     * @return array<int, mixed>
     */
    private function simplifyPolygon(array $polygon, float $epsilon): array
    {
        return array_map(fn ($ring) => $this->simplifyRing(is_array($ring) ? $ring : [], $epsilon), $polygon);
    }

    /**
     * @param  array<int, mixed>  $ring
     * @return array<int, array{0: float, 1: float}>
     */
    private function simplifyRing(array $ring, float $epsilon): array
    {
        $points = [];
        foreach ($ring as $point) {
            if (! isset($point[0], $point[1]) || ! is_numeric($point[0]) || ! is_numeric($point[1])) {
                continue;
            }
            $points[] = [(float) $point[0], (float) $point[1]];
        }

        $count = count($points);
        if ($count <= 4) {
            return array_map(fn ($point) => [round($point[0], 4), round($point[1], 4)], $points);
        }

        $keep = array_fill(0, $count, false);
        $keep[0] = true;
        $keep[$count - 1] = true;
        $stack = [[0, $count - 1]];

        while ($stack) {
            [$start, $end] = array_pop($stack);
            $maxDist = 0.0;
            $index = $start;
            for ($i = $start + 1; $i < $end; $i++) {
                $dist = $this->pointLineDistance($points[$i], $points[$start], $points[$end]);
                if ($dist > $maxDist) {
                    $maxDist = $dist;
                    $index = $i;
                }
            }
            if ($maxDist > $epsilon) {
                $keep[$index] = true;
                $stack[] = [$start, $index];
                $stack[] = [$index, $end];
            }
        }

        $out = [];
        foreach ($points as $i => $point) {
            if ($keep[$i]) {
                $out[] = [round($point[0], 4), round($point[1], 4)];
            }
        }

        if ($out && $out[0] !== $out[count($out) - 1]) {
            $out[] = $out[0];
        }

        return $out;
    }

    /**
     * @param  array{0: float, 1: float}  $point
     * @param  array{0: float, 1: float}  $start
     * @param  array{0: float, 1: float}  $end
     */
    private function pointLineDistance(array $point, array $start, array $end): float
    {
        $dx = $end[0] - $start[0];
        $dy = $end[1] - $start[1];
        if ($dx === 0.0 && $dy === 0.0) {
            return hypot($point[0] - $start[0], $point[1] - $start[1]);
        }
        $t = (($point[0] - $start[0]) * $dx + ($point[1] - $start[1]) * $dy) / ($dx * $dx + $dy * $dy);
        $t = max(0, min(1, $t));

        return hypot($point[0] - ($start[0] + $t * $dx), $point[1] - ($start[1] + $t * $dy));
    }
}
