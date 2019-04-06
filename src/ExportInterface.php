<?php

namespace RichPeers\LaravelCsvExport;

interface ExportInterface
{
    /**
     * @return array
     */
    public function headers();

    /**
     * @param $value
     * @return array
     */
    public function values($value);
}
