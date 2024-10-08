<?php

namespace App\Http\Controllers\Web;

use App\Exports\Phonebook\EmptyPhonebookExport;
use App\Helpers\MethodsHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IndexController extends Controller
{
    public function read_file($path)
    {
        return MethodsHelper::getFileFromPrivateStorage(MethodsHelper::readFileUrl("decrypt", $path));
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(new EmptyPhonebookExport, 'phonebook.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
