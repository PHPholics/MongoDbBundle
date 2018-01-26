<?php

namespace PhpHolics\MongoDbBundle\Document;

use MongoDB\Collection;
use MongoDB\Exception\InvalidArgumentException;
use PhpHolics\MongoDbBundle\Service\MongoDb;

class DocumentCollection extends Collection
{
    private $collectionName;
    private $databaseName;
    private $manager;
    private $readConcern;
    private $readPreference;
    private $typeMap;
    private $writeConcern;
    private $mongoDb;
    private $documentReflection;

    /**
     * Get a clone of this collection with different options.
     *
     * @see Collection::__construct() for supported options
     * @param array $options Collection constructor options
     * @return Collection
     * @throws InvalidArgumentException for parameter/option parsing errors
     */
    public function withOptions(array $options = [])
    {
        $options += [
            'readConcern' => $this->readConcern,
            'readPreference' => $this->readPreference,
            'typeMap' => $this->typeMap,
            'writeConcern' => $this->writeConcern,
        ];

        $me = get_class($this);
        return new $me($this->manager, $this->databaseName, $this->collectionName, $options);
    }

    /**
     * @return MongoDb
     */
    public function getMongoDb()
    {
        return $this->mongoDb;
    }

    /**
     * @param MongoDb $mongoDb
     */
    public function setMongoDb($mongoDb)
    {
        $this->mongoDb = $mongoDb;
    }


    public static function fromBaseCollection(Collection $collection, $targetClass = DocumentCollection::class)
    {
        $options = [
            'readConcern' => $collection->getReadConcern(),
            'readPreference' => $collection->getReadPreference(),
            'typeMap' => $collection->getTypeMap(),
            'writeConcern' => $collection->getWriteConcern(),
        ];

        return new $targetClass($collection->getManager(), $collection->getDatabaseName(), $collection->getCollectionName(), $options);
    }

    public function updateObject(Document $object)
    {
        $object->setCollection($this);
        $changes = $this->getChangedValues($object);
        if ($changes) {
            $this->updateOne(['_id' => $object->getId()], ['$set' => $changes]);
        }
    }

    public function getChangedValues(Document $document): array
    {
        return $document->getChangeSet();
    }

    public function deleteMany($filter, array $options = [])
    {
        $filter = $this->getMongoDb()->mappingToFields($filter, $this->getTypeMap()['document']);
        return parent::deleteMany($filter, $options); 
    }

    public function deleteOne($filter, array $options = [])
    {
        $filter = $this->getMongoDb()->mappingToFields($filter, $this->getTypeMap()['document']);
        return parent::deleteOne($filter, $options); 
    }

    public function distinct($fieldName, $filter = [], array $options = [])
    {
        $fieldName = array_keys($this->getMongoDb()->mappingToFields([$fieldName => $fieldName], $this->getTypeMap()['document']))[0];
        $filter = $this->getMongoDb()->mappingToFields($filter, $this->getTypeMap()['document']);
        return parent::distinct($fieldName, $filter, $options); 
    }

    public function find($filter = [], array $options = [])
    {
        $key = __METHOD__ . '(' . json_encode($filter) . ', ' . json_encode($options) . ')';
        $this->getMongoDb()->startProfiling($key);
        $filter = $this->getMongoDb()->mappingToFields($filter, $this->getTypeMap()['document']);
        $found = parent::find($filter, $options);
        $objects = [];
        foreach ($found as $object) {
            $this->getMongoDb()->loadObject($object, $object->getBsonStateData());
            $objects[] = $object;
        }

        $this->getMongoDb()->stopProfiling($key);
        return $objects;
    }

    public function findOne($filter = [], array $options = [])
    {
        $key = __METHOD__ . '(' . json_encode($filter) . ', ' . json_encode($options) . ')';
        $this->getMongoDb()->startProfiling($key);
        $filter = $this->getMongoDb()->mappingToFields($filter, $this->getTypeMap()['document']);


        $object = parent::findOne($filter, $options);
        $this->getMongoDb()->loadObject($object, $object->getBsonStateData());

        $this->getMongoDb()->stopProfiling($key);
        return $object;
    }

    public function findOneAndDelete($filter, array $options = [])
    {
        $key = __METHOD__ . '(' . json_encode($filter) . ', ' . json_encode($options) . ')';
        $this->getMongoDb()->startProfiling($key);
        $filter = $this->getMongoDb()->mappingToFields($filter, $this->getTypeMap()['document']);


        $result = parent::findOneAndDelete($filter, $options);
        if ($result) {
            $this->getMongoDb()->loadObject($result, $result->getBsonStateData());
        }
        $this->getMongoDb()->stopProfiling($key);
        return $result;
    }

    public function findOneAndReplace($filter, $replacement, array $options = [])
    {
        $key = __METHOD__ . '(' . json_encode($filter) . ',' . json_encode($replacement) . ', ' . json_encode($options) . ')';
        $this->getMongoDb()->startProfiling($key);
        $filter = $this->getMongoDb()->mappingToFields($filter, $this->getTypeMap()['document']);

        $result = parent::findOneAndReplace($filter, $replacement, $options);
        if ($result) {
            $this->getMongoDb()->loadObject($result, $result->getBsonStateData());
        }

        $this->getMongoDb()->stopProfiling($key);

        return $result;
    }

    public function findOneAndUpdate($filter, $update, array $options = [])
    {
        $key = __METHOD__ . '(' . json_encode($filter) . ',' . json_encode($update) . ', ' . json_encode($options) . ')';
        $this->getMongoDb()->startProfiling($key);
        $filter = $this->getMongoDb()->mappingToFields($filter, $this->getTypeMap()['document']);


        $result = parent::findOneAndUpdate($filter, $update, $options);

        if ($result) {
            $this->getMongoDb()->loadObject($result, $result->getBsonStateData());
        }

        $this->getMongoDb()->stopProfiling($key);
        return $result;
    }

    public function insertMany(array $documents, array $options = [])
    {
        return parent::insertMany($documents, $options); 
    }

    public function insertOne($document, array $options = [])
    {
        $document->setCollection($this);
        return parent::insertOne($document, $options); 
    }

    public function replaceOne($filter, $replacement, array $options = [])
    {
        $filter = $this->getMongoDb()->mappingToFields($filter, $this->getTypeMap()['document']);
        return parent::replaceOne($filter, $replacement, $options); 
    }

    public function updateMany($filter, $update, array $options = [])
    {
        $filter = $this->getMongoDb()->mappingToFields($filter, $this->getTypeMap()['document']);
        return parent::updateMany($filter, $update, $options); 
    }

    public function updateOne($filter, $update, array $options = [])
    {
        $filter = $this->getMongoDb()->mappingToFields($filter, $this->getTypeMap()['document']);
        return parent::updateOne($filter, $update, $options); 
    }
}