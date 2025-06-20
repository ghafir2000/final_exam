<?php

namespace App\Services;

use App\Models\Record;
use App\Models\Pet;
use App\Models\Animal;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RecordService
{
       public function find($id, $withTrashed = false, $withes = [])
    {
        return Record::with($withes)
            ->withTrashed($withTrashed)
            ->find($id);
    }

    public function store($data) {
        // Create Record
        $Record = Record::create($data);
        return $Record;
    }

    
    public function update($id, $data)
    {
        // Find Record and validate existence
        // dd($id);
        $Record = Record::find($id);
        if (!$Record) {
            throw new \Exception("Record not found");
        }
        // dd($Record);

        // Update Record
        $Record->update($data);
        return $Record;
    }


    public function destroy($id)
    {
        // Find Record with `withTrashed` to handle soft deletes
        $Record = Record::withTrashed()->find($id);
        if (!$Record) {
            throw new \Exception("Record not found");
        }
        $Record->delete();
        return $Record;
    }


    public function restore($id)
    {
        // Find the Record with soft-deleted records
        $Record = Record::withTrashed()->find($id);

        if (!$Record) {
            throw new \Exception("Record not found");
        }

        // Restore the Record
        $Record->restore();
    }
    

    
}

