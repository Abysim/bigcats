<?php

namespace App\Console\Commands;

use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PopulateTagsCommand extends Command
{
    protected $signature = 'populate:tags';

    protected $description = 'Migrate initial data';

    public function handle(): void
    {
        $species = json_decode(File::get(resource_path('json/species.json')), true);

        foreach ($species as $key => $value) {
            Tag::updateOrCreate(['name' => $value], ['name' => $value, 'slug' => $key, 'type_id' => 1]);
        }

        $countries = json_decode(File::get(resource_path('json/country.json')), true);

        foreach ($countries as $key => $value) {
            Tag::updateOrCreate(['name' => $value], ['name' => $value, 'slug' => $key, 'type_id' => 2]);
        }

        $regions = json_decode(File::get(resource_path('json/region.json')), true);
        $ukraineId = Tag::where('slug', 'ukraine')->first()->id;
        foreach ($regions as $key => $value) {
            Tag::updateOrCreate(['name' => $value], [
                'name' => $value,
                'slug' => $key,
                'type_id' => 3,
                'parent_id' => $ukraineId
            ]);
        }
    }
}
