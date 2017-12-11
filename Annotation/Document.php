<?php

namespace PhpHolics\MongoDbBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use PhpHolics\MongoDbBundle\Document\DocumentCollection;

/**
 * Class Document
 * @package PhpHolics\MongoDbBundle\Annotation
 * @Annotation
 * @Annotation\Target("CLASS")
 */
class Document
{

    /**
     * @Annotation\Required()
     * @var string
     */
    public $collectionName;

    /**
     * @var string
     */
    public $collectionClassName = DocumentCollection::class;

    /**
     * @return string
     */
    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    /**
     * @param string $collectionName
     * @return Document
     */
    public function setCollectionName(string $collectionName): Document
    {
        $this->collectionName = $collectionName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollectionClassName(): string
    {
        return $this->collectionClassName;
    }

    /**
     * @param string $collectionClassName
     * @return Document
     */
    public function setCollectionClassName(string $collectionClassName): Document
    {
        $this->collectionClassName = $collectionClassName;
        return $this;
    }
}