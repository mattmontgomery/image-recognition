<?php

namespace ImageRecognition;

class Recognizer
{
    protected $client;
    public function __construct(ClientInterface $client = null)
    {
        $this->client = $client;
    }
    public function detect(string $filename)
    {
        $result = $this->client->upload($filename);
        return $this->client->analyze($result);
    }
    public function debugOutput(Response $response)
    {
        foreach ($response->labels as $label) {
            echo sprintf("Label: %s (%f)\n", $label->name, $label->confidence);
        }
    }
}