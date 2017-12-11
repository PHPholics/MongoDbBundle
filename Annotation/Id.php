<?php

namespace PhpHolics\MongoDbBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use PhpHolics\MongoDbBundle\Document\DocumentCollection;

/**
 * Class Document
 * @package PhpHolics\MongoDbBundle\Annotation
 * @Annotation
 * @Annotation\Target("PROPERTY")
 */
class Id
{
    public function getName(): string
    {
        return '_id';
    }

    public function getType()
    {
        return new \MongoId();
    }
}