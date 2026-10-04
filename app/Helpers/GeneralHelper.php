<?php

namespace App\Helpers;

use Illuminate\Http\Request;

trait GeneralHelper
{

    public function listMainSeasonCategories()
    {
        return [
            ['name' => 'جولد', 'is_gold' => 1],
            ['name' => 'سيلفر', 'is_gold' => 0]
        ];
    }
    public function listMainTournamentRounds()
    {
        return [
            '256' => 'دور 256',
            '128' => 'دور 128',
            '64' => 'دور 64',
            '32Q' => 'دور 32 رئيسي',
            '32' => 'دور 32 تمهيدي',
            '16' => 'دور 16',
            '8' => 'دور 8',
            '4' => 'المركز الرابع',
            '3' => 'المركز الثالث',
            '2' => 'المركز الثاني',
            '1' => 'المركز الأول'
        ];
    }

    public function listMainSeasonAgeStages()
    {
        return [
            ['name' => 'BU11', 'gender' => 'male', 'age_from' => 8, 'age_to' => 10],
            ['name' => 'BU13', 'gender' => 'male', 'age_from' => 11, 'age_to' => 12],
            ['name' => 'BU15', 'gender' => 'male', 'age_from' => 13, 'age_to' => 14],
            ['name' => 'BU17', 'gender' => 'male', 'age_from' => 15, 'age_to' => 16],
            ['name' => 'BU19', 'gender' => 'male', 'age_from' => 17, 'age_to' => 18],
            ['name' => 'BU23', 'gender' => 'male', 'age_from' => 19, 'age_to' => 22],
            ['name' => 'Men', 'gender' => 'male', 'age_from' => 0, 'age_to' => 0],

            ['name' => 'GU11', 'gender' => 'female', 'age_from' => 8, 'age_to' => 10],
            ['name' => 'GU13', 'gender' => 'female', 'age_from' => 11, 'age_to' => 12],
            ['name' => 'GU15', 'gender' => 'female', 'age_from' => 13, 'age_to' => 14],
            ['name' => 'GU17', 'gender' => 'female', 'age_from' => 15, 'age_to' => 16],
            ['name' => 'GU19', 'gender' => 'female', 'age_from' => 17, 'age_to' => 18],
            ['name' => 'GU23', 'gender' => 'female', 'age_from' => 19, 'age_to' => 22],
            ['name' => 'Women', 'gender' => 'female', 'age_from' => 0, 'age_to' => 0],

            ['name' => 'رواد 35', 'gender' => 'general', 'age_from' => 0, 'age_to' => 0],
            ['name' => 'رواد 45', 'gender' => 'general', 'age_from' => 0, 'age_to' => 0],
            ['name' => 'رواد 55', 'gender' => 'general', 'age_from' => 0, 'age_to' => 0]
        ];
    }

    public function listMonths(){
        return [
            ['name' => 'يناير', 'number' => 1],
            ['name' => 'فبراير', 'number' => 2],
            ['name' => 'مارس', 'number' => 3],
            ['name' => 'أبريل', 'number' => 4],
            ['name' => 'مايو', 'number' => 5],
            ['name' => 'يونيو', 'number' => 6],
            ['name' => 'يوليو', 'number' => 7],
            ['name' => 'أغسطس', 'number' => 8],
            ['name' => 'سبتمبر', 'number' => 9],
            ['name' => 'أكتوبر', 'number' => 10],
            ['name' => 'نوفمبر', 'number' => 11],
            ['name' => 'ديسمبر', 'number' => 12]
        ];
    }
}
