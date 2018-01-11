<?php

namespace PhpHolics\MongoDbBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use PhpHolics\MongoDbBundle\Document\DocumentCollection;

/**
 * Class Document
 * @package PhpHolics\MongoDbBundle\Annotation
 * @Annotation
 */
class EmbeddedDocument extends BaseProperty
{

    /**
     * @Annotation\Required()
     * @var string
     */
    public $class;

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass(string $class)
    {
        $this->class = $class;
    }
}