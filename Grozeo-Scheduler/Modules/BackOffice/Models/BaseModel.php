<?php
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class BaseModel extends Eloquent{
    
     public static function boot()
     {
        parent::boot();
       
        static::updating(function($model)
        {
            
            $model->fsto_updateon = Carbon::now() ;
        });        
    }
    
}