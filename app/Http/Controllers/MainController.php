<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MainController extends Controller
{
    //
    public function home(): View
    {
        return view('home');
    }

    public function generateExercises(Request $request): void
    {
        echo 'gerar exercícios';
    }

    public function printExercises(): void
    {
        echo 'imprimir exercícios no navegador';
    }

    public function exportExercises(): void
    {
        echo 'exportar exercícios para um arquivo de texto';
    }
}