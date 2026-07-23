<?php

namespace App\Domains\Search;

use Illuminate\Support\Arr;

/**
 * Generates Elasticsearch Queries.
 * 
 * (c) Jino Antony <jinoantony99@gmail.com>
 */
class SearchProduct
{
    /**
     * The keyword to search for.
     *
     * @var string
     */
    protected $keyword;

    /**
     * The search filters.
     *
     * @var string
     */
    protected $filters;

    /**
     * Filterable properties
     *
     * @var array
     */
    protected $filterable = [
        'product_name', 'category_name', 'category_id', 'brand_name', 'brand_id'
    ];

    /**
     * Pagination from index
     *
     * @var int
     */
    protected $from;

    /**
     * Pagination size
     *
     * @var int
     */
    protected $size;

    /**
     * The elasticsearch query
     *
     * @var array
     */
    protected $query = [];

    /**
     * Current page
     *
     * @var int
     */
    protected $currentPage;

    /**
     * Range filter
     *
     * @var array
     */
    protected $range = [];

    /**
     * Must query
     *
     * @var array
     */
    protected $must = [];

    /**
     * Should query
     *
     * @var array
     */
    protected $should = [];

    /**
     * Has pagination enabled
     *
     * @var boolean
     */
    protected $hasPagination = false;

    /**
     * Aggregate query
     *
     * @var array
     */
    protected $aggregate = [];

    /**
     * Sort query
     *
     * @var array
     */
    protected $sort = [];

    /**
     * Static interface to add search keyword
     *
     * @param string $keyword
     * @return self
     */
    public static function with($keyword)
    {
        return (new static)->addKeyword($keyword);
    }

    /**
     * Add keyword for search.
     *
     * @param string $keyword
     * @return self
     */
    public function addKeyword($keyword)
    {
        $this->keyword = $keyword;
        return $this;
    }

    /**
     * Static interface to apply search filters.
     *
     * @param string $keyword
     * @return self
     */
    public static function apply($filters)
    {
        return (new static)->applyFilters($filters);
    }

    /**
     * Add keyword for search.
     *
     * @param string $keyword
     * @return self
     */
    public function applyFilters($filters)
    {
        $this->filters = Arr::only($filters, $this->filterable);
        return $this;
    }

    /**
     * Static interface to retrieve an object
     *
     * @return self
     */
    public static function query()
    {
        return (new static);
    }

    /**
     * Add a must clause to the query
     *
     * @param string $attribute
     * @param string|integer $value
     * @return self
     */
    public function must($attribute, $value)
    {
        $this->must[] = [
            'match' => [ $attribute => $value ]
        ];
        return $this;
    }

    /**
     * Add a should clause inside must clause
     *
     * @param string $attribute
     * @param array $values
     * @return self
     */
    public function mustShould($attribute, $values)
    {
        $mustArr = [];
        foreach ($values as $value) {
            $mustArr[] = [
                'match' => [ $attribute => $value ]
            ];
        }
        $this->must[] = [
            'bool' => [
                'should' => $mustArr
            ]
        ];
        return $this;
    }

    /**
     * Add a should clause to the query
     *
     * @param string $attribute
     * @param string|integer $value
     * @return self
     */
    public function should($attribute, $value)
    {
        $this->should[] = [
            'match' => [$attribute => $value]
        ];
        return $this;
    }

    /**
     * Add an aggregate clause to the query
     *
     * @param string $name
     * @param string $type
     * @param string $field
     * @return self
     */
    public function aggregate($name, $type, $field)
    {
        $this->aggregate[$name] = [
            $type => [
                'field' => $field
            ]
        ];
        return $this;
    }

    /**
     * Add a sort clause to the query
     *
     * @param string $attribute
     * @param string $order
     * @return self
     */
    public function sort($attribute, $order = 'asc')
    {
        $this->sort[] = [
            $attribute => [ 'order' => $order ]
        ];
        return $this;
    }

    /**
     * Build the elasticsearch query.
     *
     * @return void
     */
    public function buildQuery()
    {
        $this->query = [
            'index' => get_es_index(),
            'type' => 'product',
            'body' => [
                'query' => $this->determineQuery()
            ]
        ];

        $this->addSort();
        $this->addAggregations();
        $this->addPagination();
    }

    /**
     * Add sort to the query builder
     *
     * @return void
     */
    protected function addSort()
    {
        if (!empty($this->sort)) {
            $this->query['body']['sort'] = $this->sort;
        }
    }

    /**
     * Add aggregations to the query builder
     *
     * @return void
     */
    protected function addAggregations()
    {
        if (!empty($this->aggregate)) {
            $this->query['body']['aggs'] = $this->aggregate;
        }
    }

    /**
     * Append paginate query to the query property.
     *
     * @return void
     */
    public function addPagination()
    {
        if ($this->hasPagination) {
            $this->query['body']['from'] = $this->from;
            $this->query['body']['size'] = $this->size;
        }
    }

    /**
     * Determine the type of query
     *
     * @return array
     */
    public function determineQuery()
    {
        return $this->keyword ?
            [ 
                'multi_match' => [
                    'query' => $this->keyword,
                    'fields' => ['product_name', 'category_name']
                ]
            ] : 
            [
                'bool' => $this->getBoolQuery()
            ];
    }
    
    /**
     * The 'bool' type query builder
     *
     * @return array
     */
    public function getBoolQuery()
    {
        $query = [];
        if (count($this->must)) {
            $query['must'] =  $this->must;
        }

        if (count($this->should)) {
            $query['should'] =  $this->should;
        }
        return $query;
    }

    /**
     * Format the filter bool query
     * Not used (replaced by getBoolQuery)
     * 
     * @return array
     */
    public function getFormattedFilters()
    {
        $filters = [];
        foreach ($this->filters as $filter => $value) {
            $filters[]['match'] = [
                $filter => $value
            ];
        }
        return count($this->range) ? 
            array_merge($filters, $this->getRangeQuery()) :
            $filters;
    }

    /**
     * Get the query
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the results
     *
     * @return array
     */
    public function get()
    {
        $filtered = [];
        $filtered['data'] = [];

        $this->buildQuery();
        // return $this->getQuery();
        $data = \Elasticsearch::search($this->getQuery());
        // return $data;
        foreach ($data['hits']['hits'] as $result) {
            $filtered['data'][] = [
                'score' => $result['_score'],
                'product' => $result['_source'],
            ];
        }

        $filtered['total'] = $data['hits']['total'];
        
        if ($this->hasPagination) {
            $filtered['current_page'] = $this->currentPage;
            $filtered['last_page'] = $this->calculateLastPage($data['hits']['total']);
        }

        if (count($this->aggregate)) {
            $filtered['aggregations'] = $data['aggregations'];
        }
        
        return $filtered;
    }

    /**
     * Apply Pagination
     *
     * @param integer $size
     * @return self
     */
    public function paginate($size = 10)
    {
        $this->currentPage = request('page', 1);
        $this->from = ($this->currentPage - 1) * $size;
        $this->size = $size;
        $this->hasPagination = true;
        return $this;
    }

    /**
     * Add range clause to the query
     *
     * @param string $attribute
     * @param integer $min
     * @param integer $max
     * @return self
     */
    public function range($attribute, $min, $max)
    {
        $this->must[] = [
            'range' => [
                $attribute => [
                    'gte' => $min,
                    'lte' => $max
                ]
            ]
        ];
        return $this;
    }

    /**
     * Get the query for range.
     *
     * @return array
     */
    public function getRangeQuery()
    {
        $range = [];
        foreach ($this->range as $value) {
            $range[]['range'] = $value;
        }
        return $range;
    }

    /**
     * Calculate the last page for pagination
     *
     * @param int $total
     * @return int
     */
    public function calculateLastPage($total)
    {
        return (int) ceil($total / $this->size);
    }
}
