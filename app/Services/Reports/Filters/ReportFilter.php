<?php

namespace App\Services\Reports\Filters;

use Illuminate\Http\Request;

class ReportFilter
{
    public ?string $date_from;
    public ?string $date_to;
    public ?array $organizations;
    public ?array $services;
    public ?int $adviser_id;
    public ?string $status;

    public function __construct(
        ?string $date_from = null,
        ?string $date_to = null,
        ?array $organizations = null,
        ?array $services = null,
        ?int $adviser_id = null,
        ?string $status = null,
    ) {
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->organizations = $organizations ? array_values($organizations) : null;
        $this->services = $services ? array_values($services) : null;
        $this->adviser_id = $adviser_id;
        $this->status = $status;
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->string('date_from')->toString(),
            $request->string('date_to')->toString(),
            self::normalizeArray($request->input('organizations')),
            self::normalizeArray($request->input('services')),
            $request->integer('adviser_id'),
            $request->string('status')->toString(),
        );
    }

    public function toArray(): array
    {
        return [
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'organizations' => $this->organizations,
            'services' => $this->services,
            'adviser_id' => $this->adviser_id,
            'status' => $this->status,
        ];
    }

    private static function normalizeArray($input): ?array
    {
        if ($input === null) {
            return null;
        }
        if (is_string($input)) {
            // comma-separated string
            return array_values(array_filter(array_map('trim', explode(',', $input)), fn($v) => $v !== ''));
        }
        if (is_array($input)) {
            return array_values($input);
        }
        return null;
    }
}
