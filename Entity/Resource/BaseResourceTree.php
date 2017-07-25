<?php

namespace  TechPromux\BaseBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * BaseResourceTree
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 *
 */
abstract class BaseResourceTree extends BaseResource
{
    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer")
     */
    protected $level;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer")
     */
    protected $position;

    /**
     * @var int
     *
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @var int
     *
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * @var BaseResourceTree
     *
     * TODO ORM\OneToMany(targetEntity="BaseResourceTree", mappedBy="parent", cascade={"all"}, orphanRemoval=true)
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * TODO ORM\ManyToOne(targetEntity="BaseResourceTree")
     * TODO ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $children;

    //-------------------------------------------------------------------------------

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return BaseResourceTree
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return BaseResourceTree
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set lft
     *
     * @param integer $lft
     *
     * @return BaseResourceTree
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return int
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     *
     * @return BaseResourceTree
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return int
     */
    public function getRgt()
    {
        return $this->rgt;
    }


    //-----------------------------------------------------------------------

    /**
     * Set parent
     *
     * @param BaseResourceTree $parent
     *
     * @return BaseResourceTree
     */
    public function setParent(BaseResourceTree $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent
     *
     * @return BaseResourceTree
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add child
     *
     * @param BaseResourceTree $child
     *
     * @return BaseResourceTree
     */
    public function addChild(BaseResourceTree $child)
    {
        $this->children[] = $child;
        return $this;
    }

    /**
     * Remove child
     *
     * @param BaseResourceTree $child
     *
     * @return BaseResourceTree
     */
    public function removeChild(BaseResourceTree $child)
    {
        $this->children->removeChild($child);
        return $this;
    }

    //-----------------------------------------------------------------------

    public function __toString()
    {
        return $this->getName() ? $this->getName() : '';
    }

    /**
     * Get Level and Name
     *
     * @return string
     */
    public function getLevelAndName()
    {
        return (($this->getLevel() >= 0) ? str_repeat(' |--- ', $this->getLevel()) : '') . $this->getName();
    }

}

