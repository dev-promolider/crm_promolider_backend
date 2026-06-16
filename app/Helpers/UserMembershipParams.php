<?php


namespace App\Helpers;


class UserMembershipParams extends PagingParams
{
    public $OrderBy = 'updated_at';
    public $search = '';

    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * @param string $search
     */
    public function setSearch(string $search): void
    {
        $this->search = $search;
    }


    /**
     * @return string
     */
    public function getOrderBy(): string
    {
        return $this->OrderBy;
    }

    /**
     * @param string $OrderBy
     */
    public function setOrderBy(string $OrderBy): void
    {
        $this->OrderBy = $OrderBy;
    }

}