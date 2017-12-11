<?php

namespace PhpHolics\MongoDbBundle\Debug;

use PhpHolics\MongoDbBundle\Service\MongoDb;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector as BaseCollector;


class DataCollector extends BaseCollector
{
    /**
     * @var MongoDb
     */
    protected $mongoDb;

    public function collect(Request $request, Response $response, \Exception $exception = null): void
    {
        $this->data['operations'] = $this->mongoDb->getProfilingData();
        $this->data['summary_time'] = 0;
        $this->data['summary_queries'] = 0;
        foreach($this->data['operations'] as $operation){
            $this->data['summary_queries'] += $operation['called'];
            $this->data['summary_time'] += $operation['time'];
        }
    }

    public function getName(): string
    {
        return 'app.mongodbbundle_query_collector';
    }

    public function setMongoDb(MongoDb $mongoDb): void
    {
        $this->mongoDb = $mongoDb;
    }


    public function reset(): void
    {
        $this->data = [];
        $this->mongoDb->cleanProfilingData();
    }

    public function getData(): array
    {
        return $this->data;
    }
}