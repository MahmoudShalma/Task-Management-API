<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class TaskFilter
{
    protected Builder $query;
    protected array $filters;

    public function __construct(Builder $query, array $filters)
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    public function apply(): void
    {
        foreach ($this->filters as $filter => $value) {
            if (method_exists($this, $filter) && !is_null($value)) {
                $this->$filter($value);
            }
        }
    }

    protected function status($value): void
    {
        $this->query->where('status', $value);
    }

    protected function assigned_to($value): void
    {
        $this->query->where('assigned_to', $value);
    }

    protected function due_date_from($value): void
    {
        $this->query->whereDate('due_date', '>=', $value);
    }

    protected function due_date_to($value): void
    {
        $this->query->whereDate('due_date', '<=', $value);
    }

    protected function user_id($value): void
    {
        $this->query->where('assigned_to', $value);
    }
}
