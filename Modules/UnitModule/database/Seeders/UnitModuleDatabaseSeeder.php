<?php

namespace Modules\UnitModule\database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Modules\UnitModule\app\Models\Color;

class UnitModuleDatabaseSeeder extends Seeder
{
    // starter colors (the admin can edit them)
    public function run()
    {
        Model::unguard();

        // $this->call("OthersTableSeeder");

        DB::table("colors")->truncate();
		$color = array(
			// array("id"=>"1","name" => "red","value" => "#F44336"),
			// array("id"=>"2","name" => "blue","value" => "#2196F3"),
			// array("id"=>"3","name" => "green","value" => "#4CAF50"),
			// array("id"=>"4","name" => "grey","value" => "#9E9E9E"),
			// array("id"=>"5","name" => "light-green","value" => "#8BC34A"),
			// array("id"=>"7","name" => "deep-purple-lighten-2","value" => "#9575CD"),
			// array("id"=>"8","name" => "purple","value" => "#673AB7"),
			// array("id"=>"9","name" => "yellow","value" => "#FFEB3B"),
			// array("id"=>"10","name" => "teal","value" => "#009688"),

			array("id" => "1", "name" => "red", "value" => "#7C0902"),
			array("id" => "2", "name" => "blue", "value" => "#16166B"),
			array("id" => "3", "name" => "green", "value" => "#4CAF50"),
			array("id" => "4", "name" => "grey", "value" => "#9E9E9E"),
			array("id" => "5", "name" => "light-green", "value" => "#8BC34A"),
			array("id" => "7", "name" => "deep-purple-lighten-2", "value" => "#9575CD"),
			array("id" => "8", "name" => "purple", "value" => "#673AB7"),
			array("id" => "9", "name" => "yellow", "value" => "#FFEB3B"),
			array("id" => "10", "name" => "teal", "value" => "#009688"),
			array("id" => "11", "name" => "Oxford Blue", "value" => "#002147"),
			array("id" => "12", "name" => "Byzantium", "value" => "#702963"),
			array("id" => "13", "name" => "Murrey", "value" => "#8B004B"),
			array("id" => "14", "name" => "Cambridge Blue", "value" => "#85B09A"),
			array("id" => "15", "name" => "Dark Cyan", "value" => "#008B8B"),
			array("id" => "16", "name" => "Mikado Yellow", "value" => "#FFC40C"),
			array("id" => "17", "name" => "Jet", "value" => "#343434"),
			array("id" => "18", "name" => "Payne's Gray", "value" => "#536878"),
			array("id" => "19", "name" => "Quinacridone Magenta", "value" => "#8E3A59"),
			array("id" => "20", "name" => "Bondi Blue", "value" => "#0095B6"),
			array("id" => "21", "name" => "Sea Green", "value" => "#2E8B57")



		);
		Color::insert($color);


    }
}
