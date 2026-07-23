<?php

namespace Views; {

    class Show {

        public function __construct() {
            
        }

        public function getJson($payload, $status) {

            if ($status == '200')
            {
                if (array_key_exists('status', $payload) == false)
                {
                    $payload['status'] = "false";
                }
                if ($payload['status'] == "false")
                {
                    file_put_contents('php://stderr', "FALSEDDDDD  \n");
                    file_put_contents('php://stderr', print_r($payload, TRUE));
                }
                if (array_key_exists('msg', $payload) == false)
                {
                    throw new \Exception('No msg key found in payload');
                }
                if (array_key_exists('data', $payload) == false)
                {
                    throw new \Exception('No data key found in payload');
                }
                if ($payload['status'] !== "false" && $payload['status'] !== "ok")
                {
                    throw new \Exception('Invalid value for status key in payload');
                }
            }
            return json_encode($payload);
        }

    }

}
