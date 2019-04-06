<?php

namespace RichPeers\LaravelCsvExport;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class ExportAbstract
{
    protected $request, $csv;

    /**
     * ExportAbstract constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     *  Export eloquent query builder to csv streamed response
     *
     * @param Builder $builder
     * @param int $chunkSize
     * @return StreamedResponse
     */
    public function stream(Builder $builder, int $chunkSize = 1000): StreamedResponse
    {
        $response = new StreamedResponse();

        $response->setCallback(function () use ($builder, $chunkSize) {

            $this->writeCsv('php://output', $builder, $chunkSize);
        });

        return $this->setResponseProperties($response);
    }

    /**
     * Save eloquent query builder to csv file.
     *
     * @param string $filePath
     * @param Builder $builder
     * @param int $chunkSize
     * @return self
     */
    public function file(string $filePath, Builder $builder, int $chunkSize = 1000): self
    {
        $this->writeCsv($filePath, $builder, $chunkSize);

        return $this;
    }

    /**
     * Write headers and rows to csv
     *
     * @param string $path
     * @param Builder $builder
     * @param int $chunkSize
     */
    protected function writeCsv(string $path, Builder $builder, int $chunkSize)
    {
        $this->csv = \fopen($path, 'w');

        \fputcsv($this->csv, $this->headers());

        $this->addRows($builder, $chunkSize);

        \fclose($this->csv);
    }

    /**
     * Iterate chunked eloquent query builder adding rows.
     *
     * @param Builder $builder
     * @param int $chunkSize
     */
    protected function addRows(Builder $builder, int $chunkSize)
    {
        $builder->chunk($chunkSize, function ($chunk) {

            $chunk->each(function ($item) {

                \fputcsv($this->csv, $this->values($item));

            });

        });
    }

    /**
     * Set streamed response properties.
     *
     * @param StreamedResponse $response
     * @return StreamedResponse $response
     */
    protected function setResponseProperties(StreamedResponse $response): StreamedResponse
    {
        $response->headers->set('Content-Type', 'text/csv');

        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }
}
