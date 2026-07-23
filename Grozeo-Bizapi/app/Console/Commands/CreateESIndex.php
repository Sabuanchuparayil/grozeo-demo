<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateESIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'create:index {name : The name of the index}';
    protected $signature = 'es:create-index {index : The name of the index:- itemmaster }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new elasticsearch index';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if($this->argument('index')=="itemmaster"){
            \Elasticsearch::indices()->create(
                $this->getItemmasterMappings()
            );
        }
        else if($this->argument('index')=="blockeditems"){
            \Elasticsearch::indices()->create(
                $this->getBlockedItemsMappings()
            );
        }
        else if($this->argument('index')=="inventory"){
            \Elasticsearch::indices()->create(
                $this->getInventoryMappings()
            );
        }
        else if($this->argument('index')=="productsearch"){
            \Elasticsearch::indices()->create(
                $this->getProductSearchMappings()
            );
        }
        $this->info("Index created successfully");
    }

    public function getItemmasterMappings()
    {
        return [
            'index' => 'mypharm_itemmaster',
            'body' => [
                'mappings' => [
                    'properties' => [
                        'fsi_uid' => [
                            'type' => 'integer'
                        ],
                        'item_name' => [
                            'type' => 'text'
                        ],
                        'stit_ID' => [
                            'type' => 'integer'
                        ],
                        'stit_fsiuid' => [
                            'type' => 'integer'
                        ],
                        'quantity' => [
                            'type' => 'text'
                        ],
                        'itemId' => [
                            'type' => 'integer'
                        ],
                        'short_description' => [
                            'type' => 'text'
                        ],
                        'long_description' => [
                            'type' => 'text'
                        ],
                        'product_variant' => [
                            'type' => 'text'
                        ],
                        'brand_name' => [
                            'type' => 'text'
                        ],
                        'variant' => [
                            'type' => 'text'
                        ],
                        'category_name' => [
                            'type' => 'text'
                        ],
                        'category_id' => [
                            'type' => 'integer'
                        ],
                        'isMedicine' => [
                            'type' => 'integer'
                        ],
                        'updated_on' => [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                        ],
                        'main_image' => [
                            'type' => 'nested',
                            'properties' => [
                                'image_type' => [
                                    'type' => 'integer'
                                ],
                                'product_id' => [
                                    'type' => 'integer'
                                ],
                                'image_url' => [
                                    'type' => 'text'
                                ],
                                'image_thumb_url' => [
                                    'type' => 'text'
                                ],
                            ]
                        ],
                        'additional_image' => [
                            'type' => 'nested',
                            'properties' => [
                                'image_type' => [
                                    'type' => 'integer'
                                ],
                                'product_id' => [
                                    'type' => 'integer'
                                ],
                                'image_url' => [
                                    'type' => 'text'
                                ],
                                'image_thumb_url' => [
                                    'type' => 'text'
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function getInventoryMappings()
    {
        return [
            'index' => 'mypharm_inventory',
            'body' => [
                'mappings' => [
                    'properties' => [
                        'id' => [
                            'type' => 'integer'
                        ],
                        'stit_id' => [
                            'type' => 'integer'
                        ],
                        'branch_id' => [
                            'type' => 'integer'
                        ],
                        'fsbg_id' => [
                            'type' => 'integer'
                        ],
                        'item_count' => [
                            'type' => 'integer'
                        ],
                        'mrp' => [
                            'type' => 'float'
                        ],
                        'selling_price' => [
                            'type' => 'float'
                        ],
                        'updated_on' => [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                        ]
                    ]
                ]
            ]
        ];
    }

    public function getBlockedItemsMappings()
    {
        return [
            'index' => 'mypharm_blockeditems',
            'body' => [
                'mappings' => [
                    'properties' => [
                        'id' => [
                            'type' => 'integer'
                        ],
                        'item_id' => [
                            'type' => 'integer'
                        ],
                        'count' => [
                            'type' => 'integer'
                        ],
                        'branch_id' => [
                            'type' => 'integer'
                        ],
                        'expiry' => [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                        ],
                        'updated_at' => [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
                        ]
                    ]
                ]
            ]
        ];
    }

    public function getProductSearchMappings()
    {
        return [
            'index' => 'mypharm_productsearch',
            'body' => [
                'mappings' => [
                    'properties' => [
                        'fsi_uid' => [
                            'type' => 'integer'
                        ],
                        'item_group_id' => [
                            'type' => 'integer'
                        ],
                        'item_name' => [
                            'type' => 'text'
                        ],
                        'brand_name' => [
                            'type' => 'text'
                        ],
                        'isMedicine' => [
                            'type' => 'integer'
                        ],
                    ]
                ]
            ]
        ];
    }
}
