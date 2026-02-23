<?php

namespace App\Models;

use App\Models\Core\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Str;


class MainModel extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id', 'uuid'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'uuid')) {
                $model->uuid = (string)Str::uuid();
            }
            if (Schema::hasColumn($model->getTable(), 'created_by')) {
                $model->created_by = Auth::id() ? Auth::id() : 1;
            }
        });
        static::updating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'updated_by')) {
                $model->updated_by = Auth::id() ? Auth::id() : $model->updated_by;
            }
        });
    }

    //Get Record By ID
    public function getByID(int $id)
    {
        return $this->find($id);
    }

    //Get Record By UUID
    public function getByUUID(string $uuid)
    {
        return $this::where('uuid', $uuid)->first();
    }

    //Get all Records
    public function getAllRecords()
    {
        return $this::all();
    }

    function getCreatorAttribute(){
        $creator = $this['created_by'];
        if ($creator) {
            $user = User::find($this->created_by)->first();
            $creator = $user->first_name. ' ' .$user->last_name;
        }

        return $creator;
    }

    function getUpdatorAttribute(){
        $updator = $this['updated_by'];
        if ($updator) {
            $updator = User::find($this->updated_by)->first('first_name','last_name');
            $updator = $updator->first_name. ' ' .$updator->last_name;
        }

        return $updator;
    }

    //Get PaginatedRecords
    public function getPaginatedRecords(int $recordsPerPage = 15)
    {
        return $this::paginate($recordsPerPage);
    }

    /** saving the record
     * attributes may contain 1 to multiple records
     * Parameter for Saving can be
     * An array of records for single record saving
     * An array of multiple records for saving
     * ***/
    public function store(array $dataArray){
        $result = true;
        //return if $dataArray is invalid
        if (!$dataArray || count($dataArray) == 0 || !is_array($dataArray)) return false;

        //we check if the $dataArray being passed contain a single or multiple record of data for saving
        if (isset($dataArray[0]) && is_array($dataArray[0])) {
            // passed value is a set of multiple rows for saving
            foreach ($dataArray as $data) {
                $recordID = null;
                if (array_key_exists($this->getKeyName(), $data)) {
                    $recordID = $data[$this->getKeyName()];
                }
                $this::updateOrCreate([$this->getKeyName() => $recordID],$data);
            }
        } else {
            // passed array is a single record
            $recordID = null;
            if (array_key_exists($this->getKeyName(), $dataArray)) {
                $recordID = $dataArray[$this->getKeyName()];
            }

            $result = $this::updateOrCreate([$this->getKeyName() => $recordID],$dataArray);
        }
        return $result;
    }

    /* Deleting a record
    * parameter
     * $recordID : array of ID or a single ID for deleting
     * $autocommit : default to TRUE, store will not issue Begin transaction
    */
    public function deleteRecord($recordID)  {
        if ($recordID) {
            try {
                if (is_array($recordID)) { // Multiple records passed for saving
                    $responses['result'] = array();
                    foreach ($recordID as $key => $id) {
                        $this->where($this->getKeyName(),$id)->delete();
                    }
                } else { // single record is being passed for saving
                    $this->where($this->getKeyName(),$recordID)->delete();
                }
            } catch (Exception $e) {
                abort(403,get_called_class() . ' : ' .  $e->getMessage());
            }
        }
        return true;
    }

    //Search record from an array of fields to search
    public function searchRecord(array $fieldsArray, $searchValue)
    {
        $result = $this->Where(function ($q) use ($fieldsArray, $searchValue) {
            $q->orWhere($fieldsArray[0], 'LIKE', '%' . $searchValue . '%');
            for ($i = 1; $i < count($fieldsArray); $i++) {
                $q->orWhere($fieldsArray[$i], 'LIKE', '%' . $searchValue . '%');
            }
        });
        return $result;
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

}
