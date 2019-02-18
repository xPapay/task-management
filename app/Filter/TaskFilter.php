<?php

namespace App\Filter;

use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskFilter extends UrlQueryFilter
{
    protected $allowedFilters = ['status', 'sinceDate', 'untilDate'];

    public function status($value)
    {
        if ($value == 'unfinished')
        {
            return $this->queryBuilder->unfinished();
        }

        if ($value == 'finished')
        {
            return $this->queryBuilder->finished();
        }

        return $this->queryBuilder;
    }

    public function sinceDate($value)
    {
        $value = Carbon::parse($value);
        return $this->queryBuilder
            ->whereDate('tasks.due_date', '>=', $value);
    }

    public function untilDate($value)
    {
        $value = Carbon::parse($value);
        return $this->queryBuilder
            ->whereDate('tasks.start_date', '<=', $value);
    }
}
