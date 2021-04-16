<?php

class S3Bucket {
	private $client;
	private $region     = "us-east-1";
	private $bucketName = null;
	private $key;
	private $secret;

	public function __construct() {
		require_once __DIR__ . "/../../../app/includes/aws/aws-autoloader.php";
	}

	public function setRegion($region) {
		$this->region = $region;
		return true;
	}

	public function setBucketName($bucketName) {
		$this->bucketName = $bucketName;
		return true;
	}

	public function setKey($key) {
		$this->key = $key;
		return true;
	}

	public function setSecret($secret) {
		$this->secret = $secret;
		return true;
	}

	public function load($force = false) {
		if (!empty($this->key) && !empty($this->secret)) {
			if (!$this->client || $force) {
				$this->client = new Aws\S3\S3Client([
					'credentials' => [
						'key'    => $this->key,
						'secret' => $this->secret,
					],
					'region'      => $this->region,
					'version'     => 'latest',
				]);
				return true;
			}
		}
		return false;
	}

	public function put($fileSrc, $fileName) {
		try {
			$result = $this->client->putObject([
				'ACL'        => 'public-read',
				'Bucket'     => $this->bucketName,
				'Key'        => $fileName,
				'SourceFile' => $fileSrc,
			]);
			return $result;
		} catch (AwsException $e) {
			return false;
		}
	}

	public function putBase64($base64, $fileName) {
		$tmp = "/tmp/".uniqid();
		file_put_contents($tmp, base64_decode($base64));
		$s3 = $this->put($tmp, $fileName);
		unlink($tmp);
		return $s3;
	}

	public function delete($fileName) {
		try {
			$result = $this->client->deleteObject([
				'Bucket' => $this->bucketName,
				'Key'    => $fileName,
			]);
			return $result;
		} catch (AwsException $e) {
			return false;
		}
	}

	public function get($fileName) {
		try {
			$result = $this->client->getObject([
				'Bucket' => $this->bucketName,
				'Key'    => $fileName,
			]);
			return $result;
		} catch (AwsException $e) {
			return false;
		}
	}

	public function exists($fileName) {
		try {
			$result = $this->client->doesObjectExist($this->bucketName, $fileName);
			return $result;
		} catch (AwsException $e) {
			return false;
		}
	}
}