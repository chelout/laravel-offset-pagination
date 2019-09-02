<?php

namespace Chelout\OffsetPagination;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use IteratorAggregate;
use JsonSerializable;

class OffsetPaginator extends AbstractPaginator implements Arrayable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Jsonable, PaginatorContract
{
    /**
     * Determine if there are more items in the data source.
     *
     * @return bool
     */
    protected $hasMore;

    /**
     * @var Request
     */
    protected $request = null;

    protected $offset = null;

    protected $total;

    protected $perPage;

    /**
     * Create a new paginator instance.
     *
     * @param mixed $items
     * @param int   $perPage
     * @param array $options
     */
    public function __construct($items, $perPage, $total = null, array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }

        $this->perPage = $perPage;

        if (is_null($this->request)) {
            $this->request = request();
        }

        $this->query = $this->request->query();
        $this->path = $this->request->root() . '/' . ($this->path !== '/' ? rtrim($this->path, '/') : rtrim($this->request->path(), '/'));

        $this->setItems($items);

        $this->total = $total ?? $this->items->count();
        $this->offset = $this->request->get('offset');
        $this->lastPage = max((int) ceil(($this->total - $this->offset) / $this->perPage), 1);
        $this->currentPage = (int) ($this->request->get('page') ?? 1);
    }

    /**
     * Set the items for the paginator.
     *
     * @param mixed $items
     *
     * @return void
     */
    protected function setItems($items)
    {
        $this->items = $items instanceof Collection ? $items : Collection::make($items);

        $this->hasMore = $this->items->count() > $this->perPage;

        $this->items = $this->items->slice(0, $this->perPage);
    }

    public function getOffset()
    {
        return intval($this->offset);
    }


    /**
     * Get the URL for the next page.
     *
     * @return string|null
     */
    public function nextPageUrl()
    {
        if ($this->lastPage > $this->currentPage) {
            return $this->url($this->currentPage + 1);
        }
    }

    /**
     * Get the URL for the previous page.
     *
     * @return string|null
     */
    public function previousPageUrl()
    {
        if ($this->currentPage > 1) {
            return $this->url($this->currentPage - 1);
        }
    }

    /**
     * Returns the request query without the offset parameters.
     *
     * @return array
     */
    protected function getRawQuery()
    {
        return collect($this->request->query())
            ->diffKeys([
                'offset' => true,
            ])->all();
    }


    public function getTotal()
    {
        return $this->total;
    }

    public function perPage()
    {
        return intval($this->perPage);
    }

    /**
     * Determine if there is more items in the data store.
     *
     * @return bool
     */
    public function hasMorePages()
    {
        return $this->hasMore;
    }

    /**
     * Render the paginator using a given view.
     *
     * @param string|null $view
     * @param array       $data
     *
     * @return string
     */
    public function render($view = null, $data = [])
    {
        // No render method
        return '';
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'data' => $this->items->toArray(),
            'total' => $this->getTotal(),
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'first_page_url' => $this->url(1),
            'last_page_url' => $this->url($this->lastPage),
            'next_page_url' => $this->nextPageUrl(),
            'prev_page_url' => $this->previousPageUrl(),
            'from' => $this->firstItem(),
            'to' => $this->lastItem(),
            'offset' => $this->getOffset(),
            'limit' => $this->perPage(),
        ];
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
