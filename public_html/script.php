<?php



$request = ['FITNESS_ACTIVITY_READ', 'FITNESS_BLOOD_GLUCOSE_READ', 'FITNESS_BLOOD_PRESSURE_READ', 'FITNESS_BODY_TEMPERATURE_READ', 

			'FITNESS_NUTRITION_READ', 'FITNESS_OXIGEN_SATURATION_READ', 'FITNESS_SLEEP_READ'];



$type = ['com.google.step_count.delta', 'com.google.blood_glucose', 'com.google.blood_pressure', 'com.google.body.temperature.summary', 

			'com.google.nutrition', 'com.google.oxygen_saturation.summary', 'com.example.sleep_tracker'];



//Start your session.

session_start();



for ($i = 0; $i < 10; $i++) {



$client = new Google_Client();

$client->setApplicationName('google-fit');

$client->setAccessType('online');

$client->setApprovalPrompt("auto");

$client->setClientId($client_id);

$client->setClientSecret($client_secret);

$client->setRedirectUri($redirect_uri);





$client->addScope(Google_Service_Fitness::$request[i]);



$service = new Google_Service_Fitness($client);





    // Same code as yours

    $dataSources = $service->users_dataSources;

    $dataSets = $service->users_dataSources_datasets;



    $listDataSources = $dataSources->listUsersDataSources("me");



    $timezone = "GMT+0100";

    $today = date("Y-m-d");

    $endTime = strtotime($today.' 00:00:00 '.$timezone);

    $startTime = strtotime('-1 day', $endTime);



    while($listDataSources->valid()) {

        $dataSourceItem = $listDataSources->next();

        if ($dataSourceItem['dataType']['name'] == $type[0]) {

            $dataStreamId = $dataSourceItem['dataStreamId'];

            $listDatasets = $dataSets->get("me", $dataStreamId, $startTime.'000000000'.'-'.$endTime.'000000000');



            $step_count = 0;

            while($listDatasets->valid()) {

                $dataSet = $listDatasets->next();

                $dataSetValues = $dataSet['value'];



                if ($dataSetValues && is_array($dataSetValues)) {

                    foreach($dataSetValues as $dataSetValue) {

                        $step_count += $dataSetValue['intVal'];

                    }

                }

            }

            print("STEP: ".$step_count."<br />");

        };

    }

    echo "</pre>";

 else {

    $authUrl = $client->createAuthUrl();

}

}

/*


https://www.googleapis.com/fitness/v1/users/me/dataSources

{
  "dataStreamName": "MyDataSource",
  "type": "derived",
  "application": {
    "detailsUrl": "http://example.com",
    "name": "Foo Example App",
    "version": "1"
  },
  "dataType": {
    "field": [
      {
        "name": "steps",
        "format": "integer"
      }
    ],
    "name": "com.google.step_count.delta"
  },
  "device": {
    "manufacturer": "Example Manufacturer",
    "model": "ExampleTablet",
    "type": "tablet",
    "uid": "1000001",
    "version": "1"
  }
}



https://www.googleapis.com/fitness/v1/users/me/dataset:aggregate
{
  "aggregateBy": [{
    "dataTypeName": "com.google.step_count.delta",
    "dataSourceId": "derived:com.google.step_count.delta:com.google.android.gms:estimated_steps"
  }],
  "bucketByTime": { "durationMillis": 86400000 },
  "startTimeMillis": 1438705622000,
  "endTimeMillis": 1439310422000
}
*/

?>




