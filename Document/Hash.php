<?php

namespace PhpHolics\MongoDbBundle\Document;

use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;
use MongoDB\Model\BSONDocument;
use PhpHolics\MongoDbBundle\Service\MongoDb;

class Hash implements \ArrayAccess, Unserializable, Serializable
{
    protected $data = [];
    protected $_bsonBaseState;
    protected $_collection;

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public static function fromArray($array): Hash
    {
        $hash = new self();
        foreach ($array as $k => $v) {
            $hash[$k] = $v;
        }
        return $hash;
    }

    /**
     * Serialize the document to BSON.
     *
     * @see http://php.net/mongodb-bson-serializable.bsonserialize
     * @return object
     */
    public function bsonSerialize()
    {
        return (object)$this->data;
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
     * @return Document
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
     * @return Document
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this;
    }

}