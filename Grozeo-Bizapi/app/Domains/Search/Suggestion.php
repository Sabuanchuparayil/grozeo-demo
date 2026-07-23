<?php

namespace App\Domains\Search;

/**
 * Elastic search auto complete provider.
 * 
 * @author Jino Antony <jinoantony99@gmail.com>
 */
class Suggestion
{
    /**
     * Fetch auto complete results from Elasticsearch
     *
     * @param string $index
     * @param string $type
     * @param string $field
     * @param string $keyword
     * @return array
     */
    public static function getSuggestions($index, $type, $field, $keyword)
    {
        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => [
                    'match_phrase_prefix' => [
                        $field => [
                            'query' => $keyword,
                            'slop' => 10
                        ]
                    ]
                ]
            ]
        ];
        $filtered = [];
        $data = \Elasticsearch::search($params);
        foreach ($data['hits']['hits'] as $result) {
            $filtered[] = [
                'score' => $result['_score'],
                'name' => $result['_source']['product_name'],
            ];
        }
        return $filtered;
    }
}
