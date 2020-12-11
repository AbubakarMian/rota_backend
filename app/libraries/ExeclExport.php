<?php
namespace App\Libraries;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithBatchInserts;
// use Maatwebsite\Excel\Concerns\WithChunkReading;
// use Maatwebsite\Excel\Concerns\FromArray;


class ExcelExport  //FromArray
{
    use Exportable;
    public $myArray;
    public $myHeadings;

    // public function __construct(){
    //     $this->myArray = $myArray;
    //     $this->myHeadings = $myHeadings;
    // }

    // public function array(): array
    // {
    //     return [
    //         [1, 2, 3],
    //         [4, 5, 6]
    //     ];
    // }

    // public function collection()
    // {
        // dd($this->myArray);
        // return collect($this->myArray);
        // return collect([
        //     [
        //         'name' => 'Povilas',
        //         'surname' => 'Korop',
        //         'email' => 'povilas@laraveldaily.com',
        //         'twitter' => '@povilaskorop'
        //     ],
        //     [
        //         'name' => 'Taylor',
        //         'surname' => 'Otwell',
        //         'email' => 'taylor@laravel.com',
        //         'twitter' => '@taylorotwell'
        //     ]
        // ]);
    // }

    // public function headings(): array
    // {

        // return $this->myHeadings;
        // return [
        //     'Name',
        //     'Surname',
        //     'Email',
        //     'Twitter',
        // ];
    // }
    // public function array(): array{
    //     return $this->myArray;
    // }

    // public function headings(): array{
    //     return $this->myHeadings;
    // }
}
