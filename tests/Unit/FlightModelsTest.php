<?php

namespace Tests\Feature;

use App\Console\Commands\FlightModels;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Goutte\Client  as GoutteClient;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class FlightModelsTest extends TestCase
{
    /**
     *  test Client object
     *
     * @return void
     */
    public function testGoutteClient()
    {
        $flightModel = new FlightModels();
        $this->assertInstanceOf(GoutteClient::class,  $flightModel->getClient());
    }
    /**
     * test crawler object using Goutte Client
     *
     * @return void
     */
    public function testCrawler()
    {
        $flightModel = new FlightModels();
        $objclient = new GoutteClient();
        $objcrawler = $objclient->request('GET', Config::get('constants.TEST_URL'));
        $this->assertInstanceOf(get_class($objcrawler),  $flightModel->getCrawler());
    }

    /**
     * test Parser result count with HTML tag and HTML class
     *
     * @return void
     */
    public  function testHTMLTagCSSCount(){
        $objclient = new GoutteClient();
        $objcrawler = $objclient->request('GET', Config::get('constants.TEST_URL'));
        $res =  $objcrawler->filter('table > tr > td');
        $this->assertSame( 6,count($res));

        $res =  $objcrawler->filter('.row');
        $this->assertSame( 3,count($res));
    }
    /**
     * test result of DOM Parser result as array
     *
     * @return void
     */
    public function testHTMLDomParser(){
        $flightModel = new FlightModels(Config::get('constants.TEST_URL'));
        $parseddata = $flightModel->parseHTMLDom();
        $testArrayVal = "John";
        $this->assertIsArray($parseddata);
    }
}
