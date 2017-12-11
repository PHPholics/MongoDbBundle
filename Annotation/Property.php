<?php

namespace PhpHolics\MongoDbBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Document
 * @package PhpHolics\MongoDbBundle\Annotation
 * @Annotation
 * @Annotation\Target("PROPERTY")
 */
class Property
{

    /**
     * @Annotation\Required()
     * @var string
     */
    public $type;

    /**
     * @Annotation\Required()
     * @var string
     */
    public $name;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Property
     */
    public function setType(string $type): Property
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Property
     */
    public function setName(string $name): Property
    {
        $this->name = $name;
        return $this;
    }
}