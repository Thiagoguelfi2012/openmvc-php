<?php

class AwsSQS {

    private $client;
    private $region = "sa-east-1";

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
            $this->client = new Aws\Sqs\SqsClient([
                'credentials' => [
                    'key' => AWS_PUBLIC_KEY,
                    'secret' => AWS_SECRET_KEY,
                ],
                'region' => $this->region,
                'version' => '2012-11-05',
            ]);
        }
    }

    public function createQueue($queue_name, $delay_seconds = 0, $retention_period_seconds = 345600, $fifo = false, $max_size = 4096) {
        try {
            $result = $this->client->createQueue([
                'QueueName' => $queue_name,
                'Attributes' => [
                    'DelaySeconds' => $delay_seconds,
                    'MaximumMessageSize' => $max_size,
                    'MessageRetentionPeriod' => $retention_period_seconds
                ],
                'FifoQueue' => ($fifo ? "true" : "false"),
            ]);
            return $result;
        } catch (AwsException $e) {
            return false;
        }
    }

    public function deleteQueue($queue_url) {
        try {
            $result = $this->client->deleteQueue([
                'QueueUrl' => $queue_url,
            ]);
            return $result;
        } catch (AwsException $e) {
            return false;
        }
    }

    public function getQueueUrl($queue_name) {
        try {
            $result = $this->client->getQueueUrl([
                'QueueName' => $queue_name,
            ]);
            return $result;
        } catch (AwsException $e) {
            return false;
        }
    }

    public function getQueues() {
        try {
            return $this->client->listQueues();
        } catch (AwsException $e) {
            return false;
        }
    }

    public function receiveMessage($queue_url, $max_messages = 1, $wait_time_seconds = 0) {
        try {
            $result = $this->client->receiveMessage([
                'AttributeNames' => ['SentTimestamp'],
                'MaxNumberOfMessages' => $max_messages,
                'MessageAttributeNames' => ['All'],
                'QueueUrl' => $queue_url,
                'WaitTimeSeconds' => $wait_time_seconds,
            ]);

            if (!empty($result->get('Messages'))) {
                return $result->get('Messages');
            } else {
                return [];
            }
        } catch (AwsException $e) {
            return false;
        }
    }

    public function sendMessage($queue_url, $body, $attributes = [], $delay_seconds = 0) {
        try {
            $params = [
                'DelaySeconds' => $delay_seconds,
                'MessageAttributes' => $attributes,
                'MessageBody' => $body,
                'QueueUrl' => $queue_url,
            ];
            return $this->client->sendMessage($params);
        } catch (AwsException $e) {
            return false;
        }
    }

    public function deleteMessage($queue_url, $receipt_handle) {
        try {
            $result = $this->client->deleteMessage([
                'QueueUrl' => $queue_url,
                'ReceiptHandle' => $receipt_handle,
            ]);
            return $result;
        } catch (AwsException $e) {
            return false;
        }
    }

}
