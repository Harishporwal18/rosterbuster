<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Psr7\Request;
use Goutte\Client  as GoutteClient;

class FlightModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flight:models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show Flight Models based on flight duties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $url;

    protected $client;

    protected $crawler;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($url = 'https://trogodigital.com/Roster.html')
    {
        $this->url = $url;
        parent::__construct();
    }

    /**
     * Client object For Goutte Library
     *
     * @return Client Object
     */

    public function getClient()
    {
        if (! $this->client) {
            $this->client = new GoutteClient();
        }

        return $this->client;
    }

    public function setClient(GoutteClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get Crawler object to parse HTML
     *
     * @return Crawler Object
     */

    public function getCrawler()
    {
        if (! $this->crawler) {
            $this->crawler = $this->getClient()->request('GET', $this->url);
        }
        return $this->crawler;
    }
    /**
     * Default Command Function to handle Parsed HTML DOM and transalte to JSON Output
     *
     * @return Flight Model JsonArray
     */
    public function handle()
    {
        $htmlParse =  $this->parseHTMLDom();
        $headerData = $this->prepareFlightModellHeader($htmlParse);
        $Flightdata = $this->prepareFlightModels($headerData);
        $flightmodels =  json_encode($Flightdata,JSON_PRETTY_PRINT);
        echo "<pre>";print_r($flightmodels)."</pre>";
        return $flightmodels;
    }
    /**
     * Parse the HTML DOM
     *
     * @return Array
     */
   public function parseHTMLDom(){
       $table = $this->getCrawler()->filter('table')->filter('tr')->each(function ($tr, $i) {
           return  $tr->filter('td')->each(function ($td, $i) {
               return trim($td->text());
           });
       });
       return $table;
   }
    /**
     * Prepare Flight Model Data Header where Date is parent node
     *
     * @return Array
     */
    protected function prepareFlightModellHeader($table){
        $header = $fdata = array();
        $header = $table[5];
        unset($table[0],$table[1],$table[2],$table[3],$table[4], $table[5]);
        $table = array_values($table);
        $ndata  = array();
        // echo "<pre>"; print_r($table);
        foreach($header as $h => $head){
            foreach($table as $row => $rowdata){
                if(count($rowdata) <= 1)
                    break;
                else
                    $ndata[$head][] = isset($rowdata[$h]) ? $rowdata[$h] : null;
            }
        }
        return $ndata;
    }
    /**
     * Prepare Flight Model Data with Departure time, Arrival time etc datewise
     *
     * @return \Array
     */
    protected  function prepareFlightModels($flightmodeldata){
        foreach($flightmodeldata as $k => $v){
            $finaldata = array();
            // echo "<pre>";print_r($v);
            foreach($v as $i=>$j){
                $flightData = array();
                if($j == 'D/O'){
                    $flightData['Day Off'] = 'No work';
                }

                else if($j == 'ESBY')
                {
                    $flightData['Early Standby']['startime'] = $v[++$i];
                    $flightData['Early Standby']['endtime'] = $v[++$i];
                }
                else if($j == 'CBSE')
                {
                    $flightData['Crewing Standby Early']['startime'] = $v[++$i];
                    $flightData['Crewing Standby Early']['endtime'] = $v[++$i];
                }
                else if($j == 'ADTY')
                {
                    $flightData['Airport Duty on Standby']['startime'] = $v[++$i];
                    $flightData['Airport Duty on Standby']['endtime'] = $v[++$i];
                }
                else if($j == 'INTV')
                {
                    $flightData['Interviews / Interviewing']['startime'] = $v[++$i];
                    $flightData['Interviews / Interviewing']['endtime'] = $v[++$i];
                }
                else if(preg_match('/^\d{3,4}$/', $j))
                {
                    $flightData['Flight Number'] = $v[$i++];
                    if(str_contains($v[$i],':')){
                        $flightData['Reporting time'] =$v[$i++];
                    }
                    if(str_contains($v[$i],':')){
                        $flightData['Start time'] =$v[$i++];
                    }
                    if(preg_match('/[A-Z]{3}$/', $v[$i])){
                        $flightData['Departure Airport'] =$v[$i++];
                    }
                    if(preg_match('/[A-Z]{3}$/', $v[$i])){
                        $flightData['Arrival Airport'] =$v[$i++];
                    }
                    if(str_contains($v[$i],':')){
                        $flightData['Arrival time'] =$v[$i++];
                    }
                    if(str_contains($v[$i],':')){
                        $flightData['Duty Off time'] =$v[$i++];
                    }
                }
                if(!empty($flightData))
                    $finaldata[] =$flightData;
                if(!empty($finaldata))
                    $arr[$k] = $finaldata;
            }
        }
        return $arr;
    }
}
