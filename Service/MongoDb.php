<?php

namespace PhpHolics\MongoDbBundle\Service;

use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Model\BSONArray;
use PhpHolics\MongoDbBundle\Annotation\Document;
use PhpHolics\MongoDbBundle\Annotation\EmbeddedDocument;
use PhpHolics\MongoDbBundle\Annotation\Id;
use PhpHolics\MongoDbBundle\Document\DocumentCollection;

use Doctrine\Common\Annotations\Reader;

class MongoDb
{
    private $host, $port, $dbName, $client, $annotationReader, $credentials = [], $arrayType = null;
    private $microcacheCollections = [];
    private $microcachePropertiesAnnotations = [];

    private $profiling = [];

    public function __construct(string $host, string $port, string $dbName, array $credentials, Reader $annotationReader)
    {
        $this->host = $host;
        $this->port = $port;
        $this->dbName = $dbName;
        $this->credentials = $credentials;
        $this->annotationReader = $annotationReader;
    }

    public function startProfiling($key): void
    {
        if (!isset($this->profiling[$key])) {
            $this->profiling[$key] = ['called' => 0, 'time' => 0, 'current_accumulator' => 0];
        }
        $this->profiling[$key]['called'] += 1;
        $this->profiling[$key]['current_accumulator'] = microtime(true);
    }

    public function stopProfiling($key): void
    {
        $this->profiling[$key]['time'] += microtime(true) - $this->profiling[$key]['current_accumulator'];
        $this->profiling[$key]['current_accumulator'] = 0;
    }

    public function cleanProfilingData(): void
    {
        $this->profiling = [];
    }

    /**
     * @return array
     */
    public function getProfilingData(): array
    {
        return $this->profiling;
    }


    public function setArrayType($class): MongoDb
    {
        $this->arrayType = $class;
        return $this;
    }

    protected function getDatabase(): Database
    {
        if ($this->client === null) {
            $login = '';
            if (isset($this->credentials['username']) && isset($this->credentials['password'])) {
                $login = $this->credentials['username'] . ':' . $this->credentials['password'] . '@';
            }

            $this->client = new Client('mongodb://' . $login . $this->host . ':' . $this->port . '/');
        }
        return $this->client->{$this->dbName};
    }

    public function getCollection($objectOrClassName): DocumentCollection
    {
        $class = is_string($objectOrClassName) ? $objectOrClassName : get_class($objectOrClassName);
        if (isset($this->microcacheCollections[$class])) {
            return $this->microcacheCollections[$class];
        }

        $classAnnotations = $this->getClassAnnotations($class);
        $collection = $this->getDatabase()->{$classAnnotations->getCollectionName()}->withOptions(
            [
                'typeMap' => [
                    'root' => $class,
                    'document' => $class,
                    'array' => $this->arrayType ? $this->arrayType : BSONArray::class
                ]
            ]
        );


        $collectionClassName = $classAnnotations->getCollectionClassName();
        $collection = DocumentCollection::fromBaseCollection($collection, $collectionClassName);
        $collection->setMongoDb($this);

        $this->microcacheCollections[$class] = $collection;
        return $collection;
    }

    /**
     * @param $class
     * @return Document
     */
    protected function getClassAnnotations($class): Document
    {
        /**
         * @var $annotation Document
         */
        $annotation = $this->annotationReader->getClassAnnotation(new \ReflectionClass($class), 'PhpHolics\MongoDbBundle\Annotation\Document');
        return $annotation;
    }

    protected function getPropertiesAnnotations($class): array
    {
        if (isset($this->microcachePropertiesAnnotations[$class])) {
            return $this->microcachePropertiesAnnotations[$class];
        }
        $properties = [];
        $reflectionClass = new \ReflectionClass($class);
        foreach ($reflectionClass->getProperties() as $property) {
            $annotation = $this->annotationReader->getPropertyAnnotation($property, 'PhpHolics\MongoDbBundle\Annotation\Property');


            if (!$annotation) {
                $annotation = $this->annotationReader->getPropertyAnnotation($property, 'PhpHolics\MongoDbBundle\Annotation\EmbeddedDocument');
            }

            if (!$annotation) {
                $annotation = $this->annotationReader->getPropertyAnnotation($property, 'PhpHolics\MongoDbBundle\Annotation\Id');
            }
            if ($annotation) {
                $properties[$property->getName()] = $annotation;
            }
        }

        $this->microcachePropertiesAnnotations[$class] = $properties;
        return $properties;
    }

    /**
     * @param $object
     * @param $data
     */
    public function loadObject($object, $data): void
    {
        $class = get_class($object);
        $propertiesAnnotation = $this->getPropertiesAnnotations($class);
        $reflectionObject = new \ReflectionObject($object);

        foreach ($propertiesAnnotation as $property => $annotation) {
            $prop = $reflectionObject->getProperty($property);
            switch (true) {
                case $annotation instanceof Id:
                    $prop->setAccessible(true);
                    $prop->setValue($object, $data['_id']);
                    break;

                case $annotation instanceof EmbeddedDocument:
                    $prop->setAccessible(true);
                    if (isset($data[$annotation->getName()]) && !empty($data[$annotation->getName()])) {
                        $className = $annotation->getClass();
                        $value = new $className();
                        $objectData = is_array($data[$annotation->getName()]) ? $data[$annotation->getName()] : $data[$annotation->getName()]->getBsonStateData();
                        $value->bsonUnserialize($objectData);
                        $this->loadObject($value, $objectData);
                        $prop->setValue($object, $value);
                    }
                    break;

                default:
                    $prop->setAccessible(true);
                    $prop->setValue($object, $data[$annotation->getName()] ?? null);
                    break;
            }
        }
    }

    public function unloadObject($object): array
    {
        $data = [];
        $class = get_class($object);
        $propertiesAnnotation = $this->getPropertiesAnnotations($class);
        $reflectionObject = new \ReflectionObject($object);

        foreach ($propertiesAnnotation as $property => $annotation) {
            $prop = $reflectionObject->getProperty($property);
            $prop->setAccessible(true);
            $value = $prop->getValue($object);
            if ($value instanceof \PhpHolics\MongoDbBundle\Document\EmbeddedDocument) {
                $value = $this->unloadObject($value);
            }
            $data[$annotation->getName()] = $value;
        }


        if (!($data['_id'] ?? false)) {
            unset($data['_id']);
        }
        return $data;
    }


    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return MongoDb
     */
    public function setHost(string $host): MongoDb
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return integer
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param integer $port
     * @return MongoDb
     */
    public function setPort(int $port): MongoDb
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getDbName(): string
    {
        return $this->dbName;
    }

    /**
     * @param string $dbName
     * @return MongoDb
     */
    public function setDbName(string $dbName): MongoDb
    {
        $this->dbName = $dbName;
        return $this;
    }

    /**
     * @return Reader
     */
    public function getAnnotationReader(): Reader
    {
        return $this->annotationReader;
    }

    /**
     * @param Reader $annotationReader
     */
    public function setAnnotationReader(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }
}