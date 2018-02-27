<?php

namespace Chelout\OffsetPagination;

class Offset
{
    protected $prev = null;
    protected $next = null;

    /**
     * Offset constructor.
     *
     * @param null $prev
     * @param null $next
     */
    public function __construct($prev = null, $next = null)
    {
        $this->prev = $prev;
        $this->next = $next;
    }

    /**
     * @return bool
     */
    public function isPresent()
    {
        return $this->isNext() || $this->isPrev();
    }

    /**
     * @return bool
     */
    public function isNext()
    {
        return !is_null($this->next);
    }

    /**
     * @return bool
     */
    public function isPrev()
    {
        return !is_null($this->prev);
    }

    /**
     * @return mixed
     */
    public function getPrevOffset()
    {
        return $this->prev;
    }

    /**
     * @return mixed
     */
    public function getNextOffset()
    {
        return $this->next;
    }
}
