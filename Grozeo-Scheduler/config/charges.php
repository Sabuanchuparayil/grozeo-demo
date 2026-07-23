<?php

return [

     /**
     * Delivery charge for Payment
     */
    "delivery_charge" => env("DELIVERY_CHARGE", "0"),

     /**
     * Courier charge for Payment
     */
    "courier_charge" => env("COURIER_CHARGE", "0"),

    /**
     * Not Available Product in 48 hrs (case 2) , Discount percentage
     */
    "discount1" => env("DISCOUNT1", "2"),

     /**
     * All Available Product in 48 hrs (case 3) , Discount percentage
     */
    "discount2" => env("DISCOUNT2", "5"),

    
];