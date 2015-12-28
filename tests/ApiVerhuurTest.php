<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiVerhuurTest extends TestCase
{
    use WithoutMiddleware, DatabaseTransactions, DatabaseMigrations;

    /**
     * @group all
     * @group api
     */
    public function testApiRentalIndex()
    {
        factory(App\Verhuring::class, 1)->create([
            'id' => 1,
            'Email' => 'test@domain.be',
        ]);

        $response = $this->call('GET', '/api/v1/verhuring');
        $array = json_decode($response->getContent(), true);

        $this->assertNotEmpty($array);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(sizeof($array) > 0);

        $dataInfo = $array['data'][0];
        $dataDate = $array['data'][0]['datum'];

        $this->assertArrayHasKey('id', $dataInfo);
        $this->assertArrayhasKey('status', $dataInfo);

        $this->assertArrayHasKey('start', $dataDate);
        $this->assertArrayHasKey('eind', $dataDate);
    }

    /**
     * @group all
     * @group api
     */
    public function testApiRentalSpecific()
    {
        $rental = factory(App\Verhuring::class, 1)->create([
            'id' => 1,
            'Email' => 'test@domain.be',
        ]);

        $response = $this->call('GET', '/api/v1/verhuring/'. $rental->id);
        $array = json_decode($response->getContent(), true);

        $this->assertNotEmpty($array);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(sizeof($array) > 0);

        $dataInfo = $array['data'][0];
        $dataDate = $array['data'][0]['datum'];

        $this->assertArrayHasKey('id', $dataInfo);
        $this->assertArrayhasKey('status', $dataInfo);

        $this->assertArrayHasKey('start', $dataDate);
        $this->assertArrayHasKey('eind', $dataDate);

        // Test when no data will be returned.
        $responseTwo = $this->call('GET', '/api/v1/verhuring/5');
        $arrayTwo = json_decode($responseTwo->getContent(), true);

        $this->assertNotEmpty($arrayTwo);
        $this->assertArrayHasKey('errors', $arrayTwo);
        $this->assertArrayHasKey('message', $arrayTwo['errors'][0]);

    }

    /**
     * @group all
     * @group api
     */
    public function testApiRentalinsert()
    {
        // Database seed.
        $rental = factory(App\Verhuring::class, 1)->create([
            'id' => 1,
            'Email' => 'test@domain.be',
        ]);

        // Request data.
        $data['StartDatum'] = '11-12-2016';
        $data['EindDatum']  = '10-10-2016';
        $data['Groep']      = 'ScoutsGroep';
        $data['Email']      = 'domain@example.net';
        $data['Gsm']        = '0474853880';

        // Database Validation.
        $Database['Start_Datum'] = $data['StartDatum'];
        $Database['Eind_datum']  = $data['EindDatum'];
        $Database['Groep']       = $data['Groep'];
        $Database['Email']       = $data['Email'];
        $Database['Status']      = 0;
        $Database['GSM']         = $data['Gsm'];

        // Make the request.
        $this->put('/api/v1/verhuring/'. $rental->id, $data)
            ->seeJson($data)
            ->seeInDatabase('Verhuur', $Database)
            ->seeStatusCode(200);
    }
}
