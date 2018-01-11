<?php

namespace PhpHolics\MongoDbBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Document
 * @package PhpHolics\MongoDbBundle\Annotation
 * @Annotation
 * @Annotation\Target("PROPERTY")
 */
abstract class BaseProperty
{


    /**
     * @Annotation\Required()
     * @var string
     */
    public $name;


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): Property
    {
        $this->name = $name;
        return $this;
    }
}