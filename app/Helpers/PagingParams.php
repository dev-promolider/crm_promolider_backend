<?php


namespace App\Helpers;


class PagingParams
{
    private $maxPageSize = 50;
    public $PageSize = 5;

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->PageSize;
    }

    /**
     * @param mixed $PageSize
     */
    public function setPageSize($PageSize): void
    {
        if ($PageSize > $this->maxPageSize) {
            $this->PageSize = $this->maxPageSize;
        } else{
            $this->PageSize = $PageSize;
        }
    }

}