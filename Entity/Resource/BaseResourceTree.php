<?php

namespace TechPromux\BaseBundle\Entity\Resource;

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
     * @var bool
     *
     * @ORM\Column(name="is_root", type="boolean", nullable=false)
     */
    protected $isRoot;

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
     * // TODO ORM\ManyToOne(targetEntity="BaseResourceTree", inversedBy="children")
     * // TODO ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    //private $parent;


    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * // TODO ORM\OrderBy({"position" = "ASC"})
     * // TODO ORM\OneToMany(targetEntity="BaseResourceTree", mappedBy="parent", cascade={"all"}, orphanRemoval=true)
     */
    //private $children;

    //-------------------------------------------------------------------------------

    public function __toString()
    {
        return !empty($this->getTitle()) ? $this->getTitle() : ($this->getName() ? $this->getName() : '');
    }

    /**
     * Get Level and Title
     *
     * @return string
     */
    public function getLevelAndName()
    {
        return (($this->getLevel() >= 0) ? str_repeat(' |--- ', $this->getLevel()) : '') . $this->getName();
    }

    /**
     * Get Level and Title
     *
     * @return string
     */
    public function getLevelAndTitle()
    {
        return (($this->getLevel() >= 0) ? str_repeat(' |--- ', $this->getLevel()) : '') . $this->getTitle();
    }

    //------------------------------------------------------------------------------------

    /**
     * Set isRoot
     *
     * @param boolean $isRoot
     *
     * @return CatX
     */
    public function setIsRoot($isRoot)
    {
        $this->isRoot = $isRoot;

        return $this;
    }

    /**
     * Get isRoot
     *
     * @return boolean
     */
    public function getIsRoot()
    {
        return $this->isRoot;
    }

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return CatX
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer
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
     * @return CatX
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
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
     * @return CatX
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer
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
     * @return CatX
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer
     */
    public function getRgt()
    {
        return $this->rgt;
    }


}

