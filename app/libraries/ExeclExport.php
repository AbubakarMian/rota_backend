<?php
namespace App\Libraries;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithBatchInserts;
// use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportToExcel  implements FromArray, WithHeadings, ShouldAutoSize,WithEvents,FromView//FromArray FromCollection
{
    use Exportable;
    private $myView;
    private $myHeadings;

    public function __construct($myHeadings,$myView){
        // $this->myArray = $myArray;
        $this->myView = $myView;
        $this->myHeadings = $myHeadings;
    }


    public function view(): View
    {
        return $this->myView;
        // return view('exports.invoices', [
        //     'invoices' => Invoice::all()
        // ]);
    }

    public function collection()
    {
        // dd($this->myArray);
        return collect($this->myArray);
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
    }


    public function headings(): array
    {

        return $this->myHeadings;
        // return [
        //     'Name',
        //     'Surname',
        //     'Email',
        //     'Twitter',
        // ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $styleArray = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ];



                $cellRange = 'A1:W1'; // All headers
                $color = new Color(null);
                $color::COLOR_RED;
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()
                ->setSize(15);
                // $event->sheet->getStyle('B2:G8')->applyFromArray($styleArray);
            },
        ];
    }
}
