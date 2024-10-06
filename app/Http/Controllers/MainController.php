<?php

declare(strict_types=1);

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

    public function generateExercises(Request $request): View
    {
        // form validation 
        $request->validate([
            'check_sum'            => 'required_without_all:check_subtraction,check_multiplication,check_division',
            'check_subtraction'    => 'required_without_all:check_sum,check_multiplication,check_division',
            'check_multiplication' => 'required_without_all:check_subtraction,check_sum,check_division',
            'check_division'       => 'required_without_all:check_subtraction,check_multiplication,check_sum',
            'number_one'           => 'required|integer|min:0|max:999|lt:number_two',
            'number_two'           => 'required|integer|min:0|max:999',
            'number_exercises'     => 'required|integer|min:5|max:50',
        ]);
        
        // get selected operation
        $operations = [];
        if($request->check_sum) { $operations[] = 'sum'; }
        if($request->check_subtraction) { $operations[] = 'subtraction'; }
        if($request->check_multiplication) { $operations[] = 'multiplication'; }
        if($request->check_division) { $operations[] = 'division'; }
       
        //get numbers (min and max)
        $min = (int) $request->number_one;
        $max = (int) $request->number_two;

        // get number of exercises
        $numberExercises = $request->number_exercises;

        // generate exercises
        $exercises = [];

        for($index = 1; $index <= $numberExercises; $index++)
        {
            $exercises[] = $this->generateExercise($index, $operations, $min, $max);
        }
        
        // place exercises in session
        session(['exercises' => $exercises]);

        return view('operations', ['exercises' => $exercises]);
    }

    public function printExercises(): void
    {
        echo 'imprimir exercícios no navegador';
    }

    public function exportExercises(): void
    {
        echo 'exportar exercícios para um arquivo de texto';
    }

    private function generateExercise(int $index, array $operations, int $min, int $max): array
    {
        $operation = $operations[array_rand($operations)];
            $number1 = rand($min, $max);
            $number2 = rand($min, $max);

            $exercise = '';
            $solution = '';

            switch ($operation) {
                case 'sum':
                    $exercise = "$number1 + $number2 =";
                    $solution = $number1 + $number2;
                    break;
                case 'subtraction':
                    $exercise = "$number1 - $number2 =";
                    $solution = $number1 - $number2;
                    break;
                case 'multiplication':
                    $exercise = "$number1 x $number2 =";
                    $solution = $number1 * $number2;
                    break;
                case 'division':
                    // avoid division by zero
                    $number2 == 0 ? $number2 = 1 : $number2;
                    $exercise = "$number1 : $number2 =";
                    $solution = $number1 / $number2;
            }

            // if solution is a float, round it to 2 decimal places
            is_float($solution) ? $solution = round($solution, 2) : $solution;

            return [
                'operation' => $operation,
                'exercise_number' => $index,
                'exercise' => $exercise,
                'solution' => "$exercise $solution",
            ];
    }
}