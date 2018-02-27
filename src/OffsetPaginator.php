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

    /**
     * @var Offset
     */
    protected $offset = null;

    protected $total;

    /**
     * Create a new paginator instance.
     *
     * @param mixed $items
     * @param int   $perPage
     * @param array $options
     */
    public function __construct($items, $perPage, $total, array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }

        $this->perPage = $perPage;

        $this->total = $total;

        if (is_null($this->request)) {
            $this->request = request();
        }

        // $this->offset = self::resolveCurrentOffset($this->request);
        $this->offset = $this->request->offset;

        $this->query = $this->getRawQuery();
        $this->path = $this->path !== '/' ? rtrim($this->path, '/') : rtrim($this->request->path(), '/');

        $this->setItems($items);
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

    /**
     * @param Request|null $request
     *
     * @return Offset
     */
    // public static function resolveCurrentOffset(Request $request = null)
    // {
    //     $request = $request ?? request();

    //     return new Offset($request->offset);
    // }

    public function nextOffset()
    {
        return $this->total - $this->perPage > 0 ? $this->offset + $this->perPage : null;
        // return $this->hasMorePages() ? $this->lastItem() : null;
    }

    /**
     * The URL for the next page, or null.
     *
     * @return string|null
     */
    public function nextPageUrl()
    {
        if ($this->nextOffset()) {
            $query = [
                'offset' => $this->nextOffset(),
            ];

            return $this->url($query);
        }
    }

    /**
     * @return string|null
     */
    public function prevOffset()
    {
        if ($this->offset > $this->perPage) {
            return $this->offset - $this->perPage;
        }
    }

    /**
     * @return null|string
     */
    public function previousPageUrl()
    {
        if ($this->prevOffset()) {
            $query = [
                'offset' => $this->prevOffset(),
            ];

            return $this->url($query);
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

    /**
     * @param array $offset
     *
     * @return string
     */
    public function url($offset = [])
    {
        $query = array_merge($this->query, $offset);

        return $this->path
            . (str_contains($this->path, '?') ? '&' : '?')
            . http_build_query($query, '', '&')
            . $this->buildFragment();
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
     * @return bool
     */
    public function isFirstPage()
    {
        // return ! $this->offset->isNext();
        return;
    }

    /**
     * Return the first identifier of the results.
     *
     * @return mixed
     */
    public function firstItem()
    {
        // return optional($this->items->first())->{$this->identifier};
        return;
    }

    /**
     * Return the last identifier of the results.
     *
     * @return mixed
     */
    public function lastItem()
    {
        // return optional($this->items->last())->{$this->identifier};
        return;
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
            'path' => $this->url(),
            'prev' => $this->prevOffset(),
            'next' => $this->nextOffset(),
            'per_page' => (int) $this->perPage(),
            'next_page_url' => $this->nextPageUrl(),
            'prev_page_url' => $this->previousPageUrl(),
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
