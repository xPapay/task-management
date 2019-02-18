<?php

namespace App\Filter;

use Illuminate\Http\Request;


abstract class UrlQueryFilter
{
    protected $request;
    protected $queryBuilder;
    protected $allowedFilters = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function applyOn($query)
    {  
        $this->queryBuilder = $query;
        foreach ($this->getValidFilters() as $filter => $value)
        {
            if (! method_exists($this, $filter)) continue;
            $this->queryBuilder = $this->$filter($value);
        }

        return $this->queryBuilder;
    }

    protected function getValidFilters()
    {
        return $this->request->only($this->allowedFilters);
    }
}
