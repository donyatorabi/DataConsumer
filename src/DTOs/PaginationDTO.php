<?php

namespace App\DTOs;

class PaginationDTO
{
    public function __construct(
        public int $page,
        public int $limit,
        public int $total,
        public array $data
    ) {}

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->total / $this->limit);
    }
}
