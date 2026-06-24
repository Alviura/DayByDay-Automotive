<?php

namespace App\Services\Reports;

use App\Contracts\ReportQuery;
use Illuminate\Support\Collection;

abstract class AbstractReportQuery implements ReportQuery
{
    protected int $csvLimit;

    public function __construct()
    {
        $this->csvLimit = (int) config('reports.export.max_rows', 50000);
    }

    abstract public function run(ReportFilters $filters): array;

    abstract public function csvRows(ReportFilters $filters): Collection;

    abstract public function csvHeaders(): array;

    protected function truncateIfNeeded(Collection $rows): Collection
    {
        if ($rows->count() > $this->csvLimit) {
            return $rows->take($this->csvLimit);
        }

        return $rows;
    }

    public function csvTruncated(ReportFilters $filters): bool
    {
        return $this->csvRows($filters)->count() > $this->csvLimit;
    }
}
