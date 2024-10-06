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

    public function printExercises(): mixed
    {
        // get exercises
        $exercises = $this->getSessionExercises();

        $text = '';

        //echo '<pre>';
        $text = '<h1>Exercícios de Matemática (' . env('APP_NAME') .  ')</h1>';
        $text .= '<hr>';

        foreach($exercises as $exercise){
            $text .= '<h2><small>' . $exercise['exercise_number']  . ' » </small>'. $exercise['exercise']  .' </h2>';
        }

        // solutions
        $text .= '<hr>';
        $text .= '<small>Soluções</small><br>';
        foreach($exercises as $exercise)
        {
            $text .= '<small>'. $exercise['exercise_number'] . ' » '. $exercise['solution'] . '</small><br>';
        }

        return $text;
    }

    public function exportExercises(): mixed
    {
         // get exercises
         $exercises = $this->getSessionExercises();

         // create file to download with exercises
         $filename = 'exercises_' . env('APP_NAME') . '_' . date('YmdHis') . '.txt';

         $content = 'Exercícios de Matemática ('. env('APP_NAME') .')' . "\n\n";
         foreach($exercises as $exercise){
            $content .= $exercise['exercise_number'] . ' > ' . $exercise['exercise'] . "\n";
         }

         // solutions
         $content .= "\n";
         $content .= "Soluções\n" . str_repeat('-', 20) . "\n";
         foreach($exercises as $exercise) {
            $content .= $exercise['exercise_number'] . ' > ' . $exercise['solution'] . "\n";
         }

         return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
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
                'exercise_number' => str_pad((string) $index, 2, "0", STR_PAD_LEFT),
                'exercise' => $exercise,
                'solution' => "$exercise $solution",
            ];
    }

    private function getSessionExercises(): array
    {
        // check if exercises are in session
        if(!session()->has('exercises')) return redirect()->route('home');

        $exercises = session('exercises');

        return $exercises;
    }
}