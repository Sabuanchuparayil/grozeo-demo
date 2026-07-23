<?php

namespace App\Traits;

use DB;
use Elasticsearch;

trait ExportToES
{
    /**
     * Attributes to be indexed
     *
     * @var array
     */
    protected $searchable = ['*'];

    /**
     * ElasticSearch Index
     *
     * @var string
     */
    protected $esIndex = 'demo_index';

    /**
     * ElasticSearch Type
     *
     * @var string
     */
    protected $esType = 'demo';

    /**
     * Static interface for exportToElasticSearch
     *
     * @return array
     */
    public static function export()
    {
        return (new static)->exportToElasticSearch();
    }

    /**
     * Export existing records to ES
     *
     * @return array
     */
    public function exportToElasticSearch()
    {
        // DB::enableQueryLog(); 
        $records = $this->getRecords();
        //dd($records);
        //dd($records->toArrray());
        $data = $this->prepareData($records);
        //dd($data);
        // dd(DB::getQueryLog());
        // dd($records->toArray());
        if($data!=[]) return Elasticsearch::bulk($data);
        else {
            return "no-data";
        }
        //return true;
    }

    /**
     * Fetch the records for indexing
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRecords()
    {
        return $this->all($this->searchable());
    }

    /**
     * Prepare the data for bulk indexing
     *
     * @param \Illuminate\Support\Collection $records
     * @return array
     */
    public function prepareData($records)
    {
        $data = [];
        if($records!=[]){
            $records->each(function($record)  use (&$data) {
                $data_inner = $record->getAttributes();
                if($record->main_image){
                    $data_inner['main_image'] = $record->main_image->toArray();
                    //$data_inner['additional_image'] = $record->additional_image->toArray();
                }
                $data['body'][] = [
                    'index' => [
                        '_index' => $this->getEsIndex(),
                        '_id' => $record->getkey(),
                    ]
                ];
                $data['body'][] = $data_inner;
            });
            return $data;
        }
        else return [];
    }

    /**
     * Check if an index exist
     *
     * @return boolean
     */
    public function isIndexExist()
    {
        return Elasticsearch::indices()->exists(['index' => $this->getEsIndex()]);
    }

    public function searchable()
    {
        return $this->searchable;
    }

    // public function getEsIndex()
    // {
    //     return $this->esIndex;
    // }

    public function getEsType()
    {
        return $this->esType;
    }
}
