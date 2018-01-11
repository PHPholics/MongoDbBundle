<?php

namespace PhpHolics\MongoDbBundle\Document;

use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;
use MongoDB\Model\BSONDocument;

abstract class BaseDocument implements Unserializable, Serializable
{

    protected $_bsonBaseState;
    protected $_collection;

    /**
     * Serialize the document to BSON.
     *
     * @see http://php.net/mongodb-bson-serializable.bsonserialize
     * @return object
     */
    public function bsonSerialize()
    {
        return (object)$this->_collection->getMongoDb()->unloadObject($this);
    }


    public function getBsonStateData(): array
    {
        return $this->_bsonBaseState->getArrayCopy();
    }

    /**
     * Unserialize the document to BSON.
     *
     * @see http://php.net/mongodb-bson-unserializable.bsonunserialize
     * @param array $data Array data
     */
    public function bsonUnserialize(array $data)
    {
        $this->_bsonBaseState = new BSONDocument($data, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * @return mixed
     */
    protected function getBsonBaseState()
    {
        return $this->_bsonBaseState;
    }

    /**
     * @param mixed $bsonBaseState
     * @return $this
     */
    protected function setBsonBaseState($bsonBaseState)
    {
        $this->_bsonBaseState = $bsonBaseState;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getCollection()
    {
        return $this->_collection;
    }

    /**
     * @param mixed $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    public function getChangeSet()
    {
        $changes = [];
        $dataFromObject = $this->bsonSerialize();
        $dataFromDb = $this->getBsonStateData();
        foreach ($dataFromObject as $field => $value) {
            if (!isset($dataFromDb[$field]) || $dataFromDb[$field] != $value) {
                if ($value instanceof BaseDocument) {
                    $value = $value->getChangeSet();
                }
                $changes[$field] = $value;
            }
        }
        return $changes;
    }
}