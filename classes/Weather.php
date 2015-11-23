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
        // $json = file_get_contents("http://datapoint.metoffice.gov.uk/public/data/val/wxfcs/all/json/310094?res=daily&key=749db971-0b6b-447f-8f6c-9b1e0646ba3f");
       //  $obj = json_decode($json);
       //  echo $obj->access_token;
       //  $this->debug($obj);
        $url = "http://datapoint.metoffice.gov.uk/public/data/val/wxfcs/all/json/310094?res=daily&key=749db971-0b6b-447f-8f6c-9b1e0646ba3f";


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_PROXY, 'http://gts-mwg-vip:8080/');
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);

        if(curl_errno($curl)){
            throw new Exception(curl_error($curl));
        }

        curl_close($curl);
        $data = json_decode($data, true);
        $days = $data['SiteRep']['DV']['Location']['Period'];
        // $this->debug($days);

        $WeatherTypes = array(
            0 => 'Clear night',
            1 => 'Sunny day',
            2 => 'Partly cloudy', // nighttime
            3 => 'Partly cloudy', // daytime
            4 => 'Not used',
            5 => 'Mist',
            6 => 'Fog',
            7 => 'Cloudy',
            8 => 'Overcast',
            9 => 'Light rain', // nighttime
            10 => 'Light rain', // daytime
            11 => 'Drizzle',
            12 => 'Light rain',
            13 => 'Heavy rain', // nighttime
            14 => 'Heavy rain', // daytime
            15 => 'Heavy rain',
            16 => 'Sleet shower', // nighttime
            17 => 'Sleet shower', // daytime
            18 => 'Sleet',
            19 => 'Hail shower', // nighttime
            20 => 'Hail shower', // daytime
            21 => 'Hail',
            22 => 'Light snow shower', // nighttime
            23 => 'Light snow shower', // daytime
            24 => 'Light snow',
            25 => 'Heavy snow shower', // nighttime
            26 => 'Heavy snow shower', // daytime
            27 => 'Heavy snow',
            28 => 'Thunder shower', // nighttime
            29 => 'Thunder shower', // daytime
            30 => 'Thunder'
        );

        $WeatherIcons = array(
            0 => 'night-clear', // Clear night
            1 => 'day-sunny', // Sunny day
            2 => 'night-cloudy', // Partly cloudy (night)
            3 => 'day-cloudy', // Partly cloudy (day)
            4 => 'na', // Not used
            5 => 'fog', // Mist
            6 => 'fog', // Fog
            7 => 'cloudy', // Cloudy
            8 => 'cloud', // Overcast
            9 => 'night-showers', // Light rain shower (night)
            10 => 'day-showers', // Light rain shower (day)
            11 => 'sprinkle', // Drizzle
            12 => 'rain-mix', // Light rain
            13 => 'night-rain', // Heavy rain shower (night)
            14 => 'day-rain', // Heavy rain shower (day)
            15 => 'rain', // Heavy rain
            16 => 'night-sleet', // Sleet shower (night)
            17 => 'day-sleet', // Sleet shower (day)
            18 => 'sleet', // Sleet
            19 => 'night-hail', // Hail shower (night)
            20 => 'day-hail', // Hail shower (day)
            21 => 'hail', // Hail
            22 => 'night-snow', // Light snow shower (night)
            23 => 'day-snow', // Light snow shower (day)
            24 => 'snow', // Light snow
            25 => 'night-snow', // Heavy snow shower (night)
            26 => 'day-snow', // Heavy snow shower (day)
            27 => 'snow', // Heavy snow
            28 => 'night-thunderstorm', // Thunder shower (night)
            29 => 'day-thunderstorm', // Thunder shower (day)
            30 => 'thunderstorm', // Thunder
        );


        //$this->debug($WeatherTypes);



        echo '
            <div class="col-md-3 tile-weather">

          <!-- Nav tabs -->
          <ul>
          ';


        for ($i = 0; $i < 3; $i++) {
            $dayWeather = $days[$i]["Rep"][0]["W"];
            $nightWeather = $days[$i]["Rep"][1]["W"];

            if ($i == 0){
                $today = "Today";
                $tonight = "Tonight";
            } else {
                $today = "Day";
                $tonight = "Night";
            }

            if ($i == 0) {
                $tomorrow = "<h5>Today</h5>";
            } elseif ($i == 1) {
                $tomorrow = "<h5>Tomorrow</h5>";
            } else {
                $tomorrow = "";
            }
            echo '
            <li class="tile">
                <div class="weather-padding">

                    <div class="row">
                        <div class="col-md-12">
                           ' . $tomorrow . '
                            <h3>' . date("l jS M",strtotime(str_replace("Z", "", $days[$i]['value']))) . '</h3>
                            <h4>' . $days[$i]["Rep"][1]['Nm'] . '&deg;c-' . $days[$i]["Rep"][0]['Dm'] . '&deg;c
                                <i>Feels like: ' . $days[$i]["Rep"][1]['FNm'] . '&deg;c-' . $days[$i]["Rep"][0]['FDm'] . '&deg;c</i>
                            </h4>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4">
                            <i class="wi wi-' . $WeatherIcons[$dayWeather] . '"></i>

                        </div>
                        <div class="col-md-8">
                            ' . $today . ': ' . $WeatherTypes[$dayWeather] . '<br>
                            ' . $days[$i]["Rep"][0]["PPd"]. '% chance of rain
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4">
                            <i class="wi wi-' . $WeatherIcons[$nightWeather] . '"></i>
                        </div>
                        <div class="col-md-8">
                            ' . $tonight . ': ' . $WeatherTypes[$nightWeather] . '<br>
                            ' . $days[$i]['Rep'][1]['PPn']. '% chance of rain
                        </div>
                    </div>
                </div>
            </li>
            ';
        }


        echo '
          </ul>
        </div>
        ';
    }
}