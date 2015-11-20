<?php

/**
 * Created by PhpStorm.
 * User: luke.brown
 * Date: 29/09/2015
 * Time: 16:04
 */
class Weather extends siteFunctions
{

public function getWeather()
{
    $ipswich = json_decode(file_get_contents("http://datapoint.metoffice.gov.uk/public/data/val/wxfcs/all/json/310094?res=daily&key=749db971-0b6b-447f-8f6c-9b1e0646ba3f"));
    $this->debug($ipswich);
}
}