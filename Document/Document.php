<?php

namespace PhpHolics\MongoDbBundle\Document;

use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;
use MongoDB\Model\BSONDocument;
use PhpHolics\MongoDbBundle\Service\MongoDb;

abstract class Document extends BaseDocument
{

    abstract public function getId();
}