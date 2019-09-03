<?php

namespace ImageRecognition;

use Aws\Rekognition\RekognitionClient;
use Aws\S3\S3Client;
use Aws\Result;

class AwsClient implements ClientInterface
{
    /**
     * @var S3Client
     */
    protected $s3Client;
    /**
     * @var RekognitionClient
     */
    protected $rekognitionClient;
    /**
     * @var string
     */
    protected $bucket;
    public function __construct(string $bucket, RekognitionClient $rekognitionClient = null, S3Client $s3Client = null) {
        $this->s3Client = $s3Client ?: new S3Client([
            'region' => $config['region'] ?? 'us-west-2',
            'version' => $config['version'] ?? 'latest',
            'profile' => 'default'
        ]);
        $this->rekognitionClient = $rekognitionClient ?: new RekognitionClient([
            'region' => $config['region'] ?? 'us-west-2',
            'version' => $config['version'] ?? 'latest',
            'profile' => 'default'
        ]);
        $this->bucket = $bucket;
    }
    public function upload(string $filename): Result
    {
        return $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key' => md5_file($filename),
            'SourceFile' => $filename
        ]);
    }
    public function analyze(Result $result): Response
    {
        /**
         * @var Result $result
         */
        $result = $this->rekognitionClient->detectLabels([
            'Image' => [
                'S3Object' => [
                    'Bucket' => $this->bucket,
                    'Name' => str_replace('"', "", $result->get('ETag'))
                ]
            ],
            'MaxLabels' => 10,
            'MinConfidence' => 0.5
        ]);
        return $this->parseResults($result);
    }
    protected function parseResults(Result $result): Response
    {
        $response = new Response();
        foreach($result->get('Labels') as $label) {
            $resLabel = new ResponseLabel();
            $resLabel->name = $label['Name'];
            $resLabel->confidence = $label['Confidence'];
            array_push($response->labels, $resLabel);
        }
        return $response;
    }
}