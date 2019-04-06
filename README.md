# Laravel Csv Export

[![Latest Stable Version](https://poser.pugx.org/richpeers/laravel-csv-export/v/stable)](https://packagist.org/packages/richpeers/laravel-csv-export)
[![Total Downloads](https://poser.pugx.org/richpeers/laravel-csv-export/downloads)](https://packagist.org/packages/richpeers/laravel-csv-export)
[![Latest Unstable Version](https://poser.pugx.org/richpeers/laravel-csv-export/v/unstable)](https://packagist.org/packages/richpeers/laravel-csv-export)
[![License](https://poser.pugx.org/richpeers/laravel-csv-export/license)](https://packagist.org/packages/richpeers/laravel-csv-export)

Export to csv via Symfony StreamedResponse or saved file. Pass in eloquent query builder to chunk the query.

## Usage
Install with composer:
```
composer require richpeers/laravel-csv-export
```

Create a class extending `ExportAbstract` and implementing `ExportInterface`. Example below:
```
<?php

namespace App\Services\CsvExports;

use RichPeers\LaravelCsvExport\ExportAbstract;
use RichPeers\LaravelCsvExport\ExportInterface;

class ExampleExport extends ExportAbstract implements ExportInterface
{
    /**
     * Array of csv column headers.
     * @return array
     */
    public function headers()
    {
        return [
            'Id',
            'Author name',
            'Title',
            'Created'
        ];
    }

    /**
     * Transform value to array of column row values.
     * @param $value
     * @return array
     */
    public function values($value)
    {
        return [
            $value->id
            optional($value->author)->name ?? ' ',
            $value->title ?? ' ',
            $value->created_at->toDateString()
        ];
    }
}
```

Controller example:
```
<?php

namespace App\Http\Controllers;

use App\Models\Posts;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CsvExports\ExampleExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * @param Request $request
     * @param ExampleExport $exportCsv
     * @return StreamedResponse
     */
    public function __invoke(Request $request, ExampleExport $exportCsv): StreamedResponse
    {
        // your eloquent query builder - best optimized
        $builder = Post::select('id', 'author_id', 'title', 'created_at')->with([
            'author' => function($author) {
                     $author->select('id', 'name')
                 }
            ])->filter($request);

        // return streamed csv file
        return $exportCsv->stream($builder);
    }
}
```

To save the csv to file, instead use `$exportCsv->file('path/to/file', '$builder);`
