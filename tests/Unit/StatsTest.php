<?php

namespace Tests\Unit;

use App\Models\Redirect;
use App\Models\RedirectLog;
use PHPUnit\Framework\TestCase;

class StatsTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_unique_count()
    {
        $redirect = Redirect::factory()->make(['id' => 1, 'url' => 'teste']);

        $ip = '121.121.1.2';

        $redirectLog1 = RedirectLog::factory()->make(['redirect_id' => $redirect->id, 'ip' => $ip]);
        $redirectLog2 = RedirectLog::factory()->make(['redirect_id' => $redirect->id, 'ip' => $ip]);
        $redirectLog3 = RedirectLog::factory()->make(['redirect_id' => $redirect->id, 'ip' => $ip]);

        $uniqueHits = RedirectLog::where('redirect_id', $redirect->id)->distinct('ip')->count();
        
        $this->assertEquals(1, $uniqueHits);
    }
}
