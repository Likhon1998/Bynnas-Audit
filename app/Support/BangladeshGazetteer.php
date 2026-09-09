<?php

namespace App\Support;

class BangladeshGazetteer
{
    public const CENTER = [23.6850, 90.3563];

    /**
     * @return array<string, array{lat:float,lng:float}>
     */
    public static function divisions(): array
    {
        return [
            'barishal' => ['lat' => 22.7010, 'lng' => 90.3535],
            'chattogram' => ['lat' => 22.3569, 'lng' => 91.7832],
            'dhaka' => ['lat' => 23.8103, 'lng' => 90.4125],
            'khulna' => ['lat' => 22.8456, 'lng' => 89.5403],
            'mymensingh' => ['lat' => 24.7471, 'lng' => 90.4203],
            'rajshahi' => ['lat' => 24.3745, 'lng' => 88.6042],
            'rangpur' => ['lat' => 25.7439, 'lng' => 89.2752],
            'sylhet' => ['lat' => 24.8949, 'lng' => 91.8687],
        ];
    }

    /**
     * @return array<string, array{lat:float,lng:float,division:string,name:string}>
     */
    public static function districts(): array
    {
        $rows = [
            // Dhaka
            ['Dhaka', 'Dhaka', 23.8103, 90.4125],
            ['Faridpur', 'Dhaka', 23.6071, 89.8429],
            ['Gazipur', 'Dhaka', 24.0023, 90.4264],
            ['Gopalganj', 'Dhaka', 23.0052, 89.8266],
            ['Kishoreganj', 'Dhaka', 24.4449, 90.7766],
            ['Madaripur', 'Dhaka', 23.1641, 90.1897],
            ['Manikganj', 'Dhaka', 23.8617, 90.0003],
            ['Munshiganj', 'Dhaka', 23.5422, 90.5305],
            ['Narayanganj', 'Dhaka', 23.6238, 90.5000],
            ['Narsingdi', 'Dhaka', 23.9322, 90.7151],
            ['Rajbari', 'Dhaka', 23.7574, 89.6445],
            ['Shariatpur', 'Dhaka', 23.2423, 90.4348],
            ['Tangail', 'Dhaka', 24.2513, 89.9167],
            // Chattogram
            ['Bandarban', 'Chattogram', 22.1953, 92.2184],
            ['Brahmanbaria', 'Chattogram', 23.9608, 91.1115],
            ['Chandpur', 'Chattogram', 23.2333, 90.6500],
            ['Chattogram', 'Chattogram', 22.3569, 91.7832],
            ['Cumilla', 'Chattogram', 23.4607, 91.1809],
            ["Cox's Bazar", 'Chattogram', 21.4272, 92.0058],
            ['Feni', 'Chattogram', 23.0159, 91.3976],
            ['Khagrachhari', 'Chattogram', 23.1193, 91.9847],
            ['Lakshmipur', 'Chattogram', 22.9447, 90.8282],
            ['Noakhali', 'Chattogram', 22.8696, 91.0994],
            ['Rangamati', 'Chattogram', 22.7324, 92.2985],
            // Rajshahi
            ['Bogura', 'Rajshahi', 24.8465, 89.3770],
            ['Chapai Nawabganj', 'Rajshahi', 24.5965, 88.2775],
            ['Joypurhat', 'Rajshahi', 25.0947, 89.0227],
            ['Naogaon', 'Rajshahi', 24.8017, 88.9488],
            ['Natore', 'Rajshahi', 24.4206, 89.0000],
            ['Pabna', 'Rajshahi', 24.0064, 89.2372],
            ['Rajshahi', 'Rajshahi', 24.3745, 88.6042],
            ['Sirajganj', 'Rajshahi', 24.4534, 89.7007],
            // Khulna
            ['Bagerhat', 'Khulna', 22.6602, 89.7895],
            ['Chuadanga', 'Khulna', 23.6400, 88.8410],
            ['Jashore', 'Khulna', 23.1664, 89.2081],
            ['Jhenaidah', 'Khulna', 23.5448, 89.1539],
            ['Khulna', 'Khulna', 22.8456, 89.5403],
            ['Kushtia', 'Khulna', 23.9013, 89.1200],
            ['Magura', 'Khulna', 23.4873, 89.4199],
            ['Meherpur', 'Khulna', 23.7622, 88.6318],
            ['Narail', 'Khulna', 23.1725, 89.5122],
            ['Satkhira', 'Khulna', 22.7185, 89.0705],
            // Barishal
            ['Barguna', 'Barishal', 22.1590, 90.1264],
            ['Barishal', 'Barishal', 22.7010, 90.3535],
            ['Bhola', 'Barishal', 22.6859, 90.6482],
            ['Jhalokati', 'Barishal', 22.6406, 90.1987],
            ['Patuakhali', 'Barishal', 22.3596, 90.3299],
            ['Pirojpur', 'Barishal', 22.5841, 89.9720],
            // Sylhet
            ['Habiganj', 'Sylhet', 24.3740, 91.4155],
            ['Moulvibazar', 'Sylhet', 24.4829, 91.7774],
            ['Sunamganj', 'Sylhet', 25.0658, 91.3950],
            ['Sylhet', 'Sylhet', 24.8949, 91.8687],
            // Rangpur
            ['Dinajpur', 'Rangpur', 25.6279, 88.6332],
            ['Gaibandha', 'Rangpur', 25.3290, 89.5440],
            ['Kurigram', 'Rangpur', 25.8054, 89.6362],
            ['Lalmonirhat', 'Rangpur', 25.9923, 89.2847],
            ['Nilphamari', 'Rangpur', 25.9310, 88.8560],
            ['Panchagarh', 'Rangpur', 26.3411, 88.5542],
            ['Rangpur', 'Rangpur', 25.7439, 89.2752],
            ['Thakurgaon', 'Rangpur', 26.0336, 88.4616],
            // Mymensingh
            ['Jamalpur', 'Mymensingh', 24.9375, 89.9370],
            ['Mymensingh', 'Mymensingh', 24.7471, 90.4203],
            ['Netrokona', 'Mymensingh', 24.8700, 90.7271],
            ['Sherpur', 'Mymensingh', 25.0205, 90.0153],
        ];

        $out = [];
        foreach ($rows as [$name, $division, $lat, $lng]) {
            $out[self::normalize($name)] = [
                'name' => $name,
                'division' => $division,
                'lat' => $lat,
                'lng' => $lng,
            ];
        }

        return $out;
    }

    /**
     * Common thanas / upazilas used by Shakha and Area names.
     *
     * @return array<string, array{lat:float,lng:float,division:string,district:string,name:string}>
     */
    public static function thanas(): array
    {
        $rows = [
            ['Mirpur', 'Dhaka', 'Dhaka', 23.8223, 90.3654],
            ['Uttara', 'Dhaka', 'Dhaka', 23.8750, 90.3793],
            ['Gulshan', 'Dhaka', 'Dhaka', 23.7925, 90.4078],
            ['Banani', 'Dhaka', 'Dhaka', 23.7937, 90.4066],
            ['Mohakhali', 'Dhaka', 'Dhaka', 23.7778, 90.4055],
            ['Tejgaon', 'Dhaka', 'Dhaka', 23.7598, 90.3910],
            ['Pallabi', 'Dhaka', 'Dhaka', 23.8283, 90.3647],
            ['Kafrul', 'Dhaka', 'Dhaka', 23.7906, 90.3910],
            ['Dhanmondi', 'Dhaka', 'Dhaka', 23.7461, 90.3742],
            ['Motijheel', 'Dhaka', 'Dhaka', 23.7295, 90.4172],
            ['Wari', 'Dhaka', 'Dhaka', 23.7167, 90.4190],
            ['Lalbagh', 'Dhaka', 'Dhaka', 23.7190, 90.3880],
            ['Ramna', 'Dhaka', 'Dhaka', 23.7410, 90.4020],
            ['Cantonment', 'Dhaka', 'Dhaka', 23.8150, 90.4100],
            ['Savar', 'Dhaka', 'Dhaka', 23.8583, 90.2667],
            ['Teknaf', 'Chattogram', "Cox's Bazar", 20.8620, 92.2980],
            ['Ukhiya', 'Chattogram', "Cox's Bazar", 21.2500, 92.1000],
            ['Patiya', 'Chattogram', 'Chattogram', 22.2950, 91.9790],
            ['Sitakunda', 'Chattogram', 'Chattogram', 22.6167, 91.6611],
            ['Hathazari', 'Chattogram', 'Chattogram', 22.5050, 91.8070],
        ];

        $out = [];
        foreach ($rows as [$name, $division, $district, $lat, $lng]) {
            $out[self::normalize($name)] = [
                'name' => $name,
                'division' => $division,
                'district' => $district,
                'lat' => $lat,
                'lng' => $lng,
            ];
        }

        return $out;
    }

    /**
     * City corporations and pourashavas used to isolate municipal areas.
     *
     * @return array<string, array{lat:float,lng:float,division:string,district:string,name:string}>
     */
    public static function pourashavas(): array
    {
        $rows = [
            ['Dhaka North', 'Dhaka', 'Dhaka', 23.8750, 90.3793],
            ['Dhaka South', 'Dhaka', 'Dhaka', 23.7295, 90.4172],
            ['Gazipur', 'Dhaka', 'Gazipur', 24.0023, 90.4264],
            ['Narayanganj', 'Dhaka', 'Narayanganj', 23.6238, 90.5000],
            ['Savar', 'Dhaka', 'Dhaka', 23.8583, 90.2667],
            ['Tangail', 'Dhaka', 'Tangail', 24.2513, 89.9167],
            ['Faridpur', 'Dhaka', 'Faridpur', 23.6071, 89.8429],
            ['Chattogram', 'Chattogram', 'Chattogram', 22.3569, 91.7832],
            ["Cox's Bazar", 'Chattogram', "Cox's Bazar", 21.4272, 92.0058],
            ['Cumilla', 'Chattogram', 'Cumilla', 23.4607, 91.1809],
            ['Feni', 'Chattogram', 'Feni', 23.0159, 91.3976],
            ['Noakhali', 'Chattogram', 'Noakhali', 22.8696, 91.0994],
            ['Rajshahi', 'Rajshahi', 'Rajshahi', 24.3745, 88.6042],
            ['Bogura', 'Rajshahi', 'Bogura', 24.8465, 89.3770],
            ['Pabna', 'Rajshahi', 'Pabna', 24.0064, 89.2372],
            ['Sirajganj', 'Rajshahi', 'Sirajganj', 24.4534, 89.7007],
            ['Khulna', 'Khulna', 'Khulna', 22.8456, 89.5403],
            ['Jashore', 'Khulna', 'Jashore', 23.1664, 89.2081],
            ['Kushtia', 'Khulna', 'Kushtia', 23.9013, 89.1200],
            ['Satkhira', 'Khulna', 'Satkhira', 22.7185, 89.0705],
            ['Barishal', 'Barishal', 'Barishal', 22.7010, 90.3535],
            ['Patuakhali', 'Barishal', 'Patuakhali', 22.3596, 90.3299],
            ['Bhola', 'Barishal', 'Bhola', 22.6859, 90.6482],
            ['Sylhet', 'Sylhet', 'Sylhet', 24.8949, 91.8687],
            ['Moulvibazar', 'Sylhet', 'Moulvibazar', 24.4829, 91.7774],
            ['Habiganj', 'Sylhet', 'Habiganj', 24.3740, 91.4155],
            ['Rangpur', 'Rangpur', 'Rangpur', 25.7439, 89.2752],
            ['Dinajpur', 'Rangpur', 'Dinajpur', 25.6279, 88.6332],
            ['Kurigram', 'Rangpur', 'Kurigram', 25.8054, 89.6362],
            ['Mymensingh', 'Mymensingh', 'Mymensingh', 24.7471, 90.4203],
            ['Jamalpur', 'Mymensingh', 'Jamalpur', 24.9375, 89.9370],
            ['Netrokona', 'Mymensingh', 'Netrokona', 24.8700, 90.7271],
        ];

        $out = [];
        foreach ($rows as [$name, $division, $district, $lat, $lng]) {
            $out[self::normalize($name)] = [
                'name' => $name,
                'division' => $division,
                'district' => $district,
                'lat' => $lat,
                'lng' => $lng,
            ];
        }

        foreach (self::thanas() as $key => $row) {
            $out[$key] = $row;
        }

        return $out;
    }

    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, self::aliases());
        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\b(shakha|branch|sadar|thana|upazila|upojela|jela|zila|metro|area|office)\b/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    /**
     * @return array{lat:float,lng:float,division:?string,district:?string,upazila:?string,pourashava:?string,match:string}
     */
    public static function locate(string $place, ?string $area = null, ?string $division = null, int $jitterSeed = 0): array
    {
        $haystacks = array_filter([$place, $area, $division]);
        $thanas = self::thanas();
        $pourashavas = self::pourashavas();
        $districts = self::districts();
        $divisions = self::divisions();

        foreach ($haystacks as $text) {
            $normalized = self::normalize($text);
            if ($normalized === '') {
                continue;
            }

            foreach ($thanas as $key => $row) {
                if ($normalized === $key || (mb_strlen($key) >= 4 && str_contains($normalized, $key))) {
                    return self::point($row['lat'], $row['lng'], $row['division'], $row['district'], $row['name'], $row['name'], 'upazila', $jitterSeed);
                }
            }
        }

        foreach ($haystacks as $text) {
            $normalized = self::normalize($text);
            if ($normalized === '') {
                continue;
            }
            $keys = array_keys($pourashavas);
            usort($keys, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));
            foreach ($keys as $key) {
                if ($normalized === $key || (mb_strlen($key) >= 4 && str_contains($normalized, $key))) {
                    $row = $pourashavas[$key];

                    return self::point($row['lat'], $row['lng'], $row['division'], $row['district'], null, $row['name'], 'pouroshova', $jitterSeed);
                }
            }
        }

        foreach ($haystacks as $text) {
            $normalized = self::normalize($text);
            $keys = array_keys($districts);
            usort($keys, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));
            foreach ($keys as $key) {
                if ($normalized === $key || str_contains($normalized, $key)) {
                    $row = $districts[$key];

                    return self::point($row['lat'], $row['lng'], $row['division'], $row['name'], null, null, 'jela', $jitterSeed);
                }
            }
        }

        $divisionKey = self::normalize((string) $division);
        if ($divisionKey !== '' && isset($divisions[$divisionKey])) {
            $row = $divisions[$divisionKey];

            return self::point($row['lat'], $row['lng'], $division, null, null, null, 'bivag', $jitterSeed);
        }

        return self::point(self::CENTER[0], self::CENTER[1], $division, null, null, null, 'country', $jitterSeed);
    }

    /**
     * Search catalog for the map finder.
     *
     * @return list<array{type:string,label:string,division:?string,district:?string,lat:float,lng:float}>
     */
    public static function catalog(): array
    {
        $items = [];

        foreach (Divisions::all() as $name) {
            $point = self::divisions()[self::normalize($name)] ?? null;
            if (! $point) {
                continue;
            }
            $items[] = [
                'type' => 'bivag',
                'label' => $name,
                'division' => $name,
                'district' => null,
                'lat' => $point['lat'],
                'lng' => $point['lng'],
            ];
        }

        foreach (self::districts() as $row) {
            $items[] = [
                'type' => 'jela',
                'label' => $row['name'],
                'division' => $row['division'],
                'district' => $row['name'],
                'lat' => $row['lat'],
                'lng' => $row['lng'],
            ];
        }

        foreach (self::pourashavas() as $row) {
            $items[] = [
                'type' => 'pouroshova',
                'label' => $row['name'],
                'division' => $row['division'],
                'district' => $row['district'],
                'lat' => $row['lat'],
                'lng' => $row['lng'],
            ];
        }

        return $items;
    }

    /**
     * @return array<string, string>
     */
    private static function aliases(): array
    {
        return [
            'chittagong' => 'chattogram',
            'barisal' => 'barishal',
            'jessore' => 'jashore',
            'comilla' => 'cumilla',
            'bogra' => 'bogura',
            'cox s bazar' => 'coxs bazar',
            "cox's bazar" => 'coxs bazar',
            'nawabganj' => 'chapai nawabganj',
            'netrakona' => 'netrokona',
        ];
    }

    /**
     * @return array{lat:float,lng:float,division:?string,district:?string,upazila:?string,pourashava:?string,match:string}
     */
    private static function point(
        float $lat,
        float $lng,
        ?string $division,
        ?string $district,
        ?string $upazila,
        ?string $pourashava,
        string $match,
        int $jitterSeed,
    ): array {
        if ($jitterSeed > 0) {
            $lat += ((($jitterSeed * 37) % 80) - 40) / 1200;
            $lng += ((($jitterSeed * 53) % 80) - 40) / 1200;
        }

        return [
            'lat' => round($lat, 6),
            'lng' => round($lng, 6),
            'division' => $division,
            'district' => $district,
            'upazila' => $upazila,
            'pourashava' => $pourashava,
            'match' => $match,
        ];
    }
}
