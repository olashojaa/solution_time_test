<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notes;
use App\Models\Status;

class NoteFixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $status=Status::select('id')->get();
      Notes::whereNotIn('status_id',$status->pluck('id'))->update(['status_id' => 1]);

    }
}
