<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\InstansiController;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class GoogleMapsCoordinateParserTest extends TestCase
{
    #[Test]
    public function it_prefers_the_place_pin_over_the_map_viewport(): void
    {
        $coordinates = $this->extract('https://www.google.com/maps/place/Bank/@-5.3734094,105.2226158,15z/data=!4m7!8m2!3d-5.3734094!4d105.2406402');

        $this->assertSame(['lat' => '-5.3734094', 'lng' => '105.2406402'], $coordinates);
    }

    #[Test]
    public function it_reads_url_encoded_pin_coordinates(): void
    {
        $coordinates = $this->extract('https://www.google.com/maps/data=%213d-5.4291839%214d105.2618658');

        $this->assertSame(['lat' => '-5.4291839', 'lng' => '105.2618658'], $coordinates);
    }

    #[Test]
    public function it_reads_viewport_and_query_coordinate_links(): void
    {
        $this->assertSame(['lat' => '-5.4291839', 'lng' => '105.2618658'], $this->extract('https://www.google.com/maps/@-5.4291839,105.2618658,17z'));
        $this->assertSame(['lat' => '-5.4291839', 'lng' => '105.2618658'], $this->extract('https://www.google.com/maps?q=-5.4291839,105.2618658'));
    }

    private function extract(string $link): ?array
    {
        $method = new ReflectionMethod(InstansiController::class, 'extractLatLng');

        return $method->invoke(new InstansiController, $link);
    }
}
