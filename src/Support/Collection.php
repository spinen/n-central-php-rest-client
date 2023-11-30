<?php

namespace Spinen\Ncentral\Support;

use Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection
{
    protected array $links = [];

    protected array $pagination = [];

    /**
     * First page
     */
    public function firstPage(): ?int
    {
        return $this->links['firstPage'] ?? null;
    }

    /**
     * Last page
     */
    public function lastPage(): ?int
    {
        return $this->links['lastPage'] ?? null;
    }

    /**
     * Next page
     */
    public function nextPage(): ?int
    {
        return $this->links['nextPage'] ?? null;
    }

    /**
     * Current page that the collection holds
     */
    public function page(): ?int
    {
        return $this->pagination['page'] ?? null;
    }

    /**
     * Number of available pages
     */
    public function pages(): ?int
    {
        return $this->pagination['pages'] ?? null;
    }

    /**
     * Records per page
     */
    public function pageSize(): ?int
    {
        return $this->pagination['pageSize'] ?? null;
    }

    /**
     * Previous page
     */
    public function previousPage(): ?int
    {
        return $this->links['previousPage'] ?? null;
    }

    /**
     * Count of records available in the the
     */
    public function recordCount(): ?int
    {
        return $this->pagination['count'] ?? null;
    }

    /**
     * Set links
     */
    public function setLinks(array $links = []): self
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Set pagination
     */
    public function setPagination(int $count = null, int $page = null, int $pages = null, int $pageSize = null): self
    {
        $this->pagination = array_merge($this->pagination, compact('count', 'page', 'pages', 'pageSize'));

        return $this;
    }
}
