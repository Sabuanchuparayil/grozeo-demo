<?php

namespace Models {

    abstract class ModelAbstract {

        public function __construct($functionality) {
            if (!method_exists($this, $functionality))
            {
                throw new \Exception('Functionality  (' . $functionality . ') does not exists');
            }
        }

    }

}
