<?php

namespace App\Imports;

use App\Models\ServiciosImportados;
use App\Models\User;
use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class ImportacionDesmontes implements ToModel, WithHeadingRow, WithUpserts
{
    public function uniqueBy()
    {
        return 'placa_serie';
    }


    public function model(array $row)
    {
        //dd($row);        
        // Buscar certificador en tabla users
        $user = User::whereRaw("LOWER(TRIM(name)) = LOWER(TRIM(?))", [trim($row['certificador'])])->first();
        // externo: si no existe o es null → 0
        $externo = $user ? ($user->externo ?? 0) : 0;

        return new ServiciosImportados([
            "idImportado" => $row['id'],
            "placa" => $row['placavehiculo'],
            "serie" => $row['seriecilindro'],
            //"certificador" => $row['certificador'],
            "certificador" => trim($row['certificador']),
            //"taller" => $row['nombretaller'],
            "taller" => trim($row['nombretaller']),
            "fecha" => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fechadesmonte']),
            "precio" => null,
            "tipoServicio" => 6,
            "estado" => 1,
            "pagado" => false,
            "externo" => $externo,
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
