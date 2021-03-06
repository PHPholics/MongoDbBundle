<?php

namespace PhpHolics\MongoDbBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Document
 * @package PhpHolics\MongoDbBundle\Annotation
 * @Annotation
 */
class Property extends BaseProperty
{

    /**
     * @Annotation\Required()
     * @var string
     */
    public $type;


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

}