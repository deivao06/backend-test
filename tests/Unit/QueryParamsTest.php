<?php

namespace Tests\Unit;

use App\Models\Redirect;
use App\Models\RedirectLog;
use PHPUnit\Framework\TestCase;

class QueryParamsTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_query_params_merge()
    {
        $redirect = Redirect::factory()->make(['url' => 'https://google.com?source=asd']);
        $redirectLog = RedirectLog::factory()->make(['redirect_id' => $redirect->id, 'query_params' => json_encode(['teste' => 'qwe'])]);
        
        $queryParams = array_merge((array) json_decode($redirectLog->query_params), $redirect->query_params);

        $expectedResult = ['teste' => 'qwe', 'source' => 'asd'];
        $this->assertEquals($expectedResult, $queryParams);
    }
}
