<?php

class AwsRekognition {

    private $client;
    private $region = "us-east-1";

    public function __construct() {
        require_once __DIR__ . "/../../../app/includes/aws/aws-autoloader.php";
        $this->load();
    }

    public function setRegion($region) {
        $this->region = $region;
        $this->load(true);
        return true;
    }

    public function load($force = false) {
        if (!$this->client && !$force) {
            $this->client = new Aws\Rekognition\RekognitionClient([
                'credentials' => [
                    'key' => AWS_PUBLIC_KEY,
                    'secret' => AWS_SECRET_KEY,
                ],
                'region' => $this->region,
                'version' => '2016-06-27',
            ]);
        }
    }

    public function getLabels($file_src) {
        $result = $this->client->detectLabels([
            'Image' => [
                'Bytes' => file_get_contents($file_src),
            ],
            'MaxLabels' => 10,
            'MinConfidence' => 50,
        ]);
        return $result;
    }

}
