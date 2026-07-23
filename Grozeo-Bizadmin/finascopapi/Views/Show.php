<?php

namespace Views; {

    class Show {

        public function __construct() {
            
        }

        public function getJson($payload, $status) {

            if ($status == '200')
            {
                if (array_key_exists('success', $payload) == false)
                {
                    $payload['success'] = false;
                }
                if ($payload['success'] == false)
                {
                    file_put_contents('php://stderr', "FALSEDDDDD  \n");
                    file_put_contents('php://stderr', print_r($payload, TRUE));
                }
                if (array_key_exists('msg', $payload) == false)
                {
                    throw new \Exception('No msg key found in payload');
                }
                if (array_key_exists('Data', $payload) == false)
                {
                    throw new \Exception('No Data key found in payload');
                }
                if ($payload['success'] !== false && $payload['success'] !== true)
                {
                    throw new \Exception('Invalid value for Success key in payload');
                }
            }
            return json_encode($payload);
        }

    }

}
