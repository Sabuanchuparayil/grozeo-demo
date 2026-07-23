<?php

return [

/**
 * Checkout process api response Style.
 */
"checkout" => [

            /**
             * Total basket Amount, including tax value.
             */
            "subtotal" => [
                "label" => "Basket Value",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 1
            ],
            /**
             * Total basket Amount, before tax value.
             */
            "order_total_amount" => [
                "label" => "Amount Before Tax",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 2
            ],
            /**
             * Total Gst Amount
             */
            "order_total_gst" => [
                "label" => "Taxes",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 3
            ],

            /**
           * Total sgst Amount
           */
          "order_total_sgst" => [
            "label" => "SGST",
            "value" => "",
            "color_code" => "#858383",
            "is_bold" => false,
            "is_italics" => false,
            "order" => 4
        ],
         /**
           * Total sgst Amount
           */
          "order_total_cgst" => [
            "label" => "CGST",
            "value" => "",
            "color_code" => "#858383",
            "is_bold" => false,
            "is_italics" => false,
            "order" => 5
        ],
            /**
             * Total kfc amount
             */
            /*"order_kfc_amount" => [
                "label" => "KFC",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 6
            ],*/
            /**
             * Delivary charge
             */
            "order_delivery_charge" => [
                "label" => "Delivery charge",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 7
            ],

            /**
             * Courier charge
             */
            "order_courier_charge" => [
                "label" => "Courier charge",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 8
            ],

            /**
             * Discount
             *
             */
            "order_discount" => [
                "label" => "Discount",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 9
            ],
            /**
             * Total amount - Payable amount
             */
            "order_roundoff" => [
                "label" => "Round Off",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 10
            ],
            /**
             * Total amount - Payable amount
             */
            "total" => [
                "label" => "Total",
                "value" => "",
                "color_code" => "#000000",
                "is_bold" => true,
                "is_italics" => false,
                "order" => 11
            ],


        ],

/**
* Coupon Api - Response style.
*/
"coupon" => [
              /**
             * Total basket Amount, including tax value.
             */
            "subtotal" => [
                "label" => "Basket Value",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 1
            ],
            /**
             * Total basket Amount, before tax value.
             */
            "order_total_amount" => [
                "label" => "Amount Before Tax",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 2
            ],
            /**
             * Total basket Amount, before tax value.
             */
            "order_discount_amount" => [
                "label" => "Discount",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 3
            ],
            /**
             * Total Gst Amount
             */
            "order_total_gst" => [
                "label" => "Gst",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 4
            ],
            /**
             * Total kfc amount
             
            "order_kfc_amount" => [
                "label" => "KFC",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 5
            ],*/
            /**
             * Delivary charge
             */
            "order_delivery_charge" => [
                "label" => "Delivery",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 6
            ],
            /**
             * Total amount - Payable amount
             */
            "order_roundoff" => [
                "label" => "Round Off",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 7
            ],
            /**
             * Total amount - Payable amount
             */
            "total" => [
                "label" => "Total",
                "value" => "",
                "color_code" => "#000000",
                "is_bold" => true,
                "is_italics" => false,
                "order" => 8
            ],

            "wallet_amount_used" => [
                "label" => "Wallet Amount Used",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 11
            ],

            "net_amount_payable" => [
                "label" => "Net Amount Payable",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 12
            ],

        ],

/**
 * Order Complete api Response style
 */
"order_complete" => [
            /**
           * Total basket Amount, including tax value.
           */
          "subtotal" => [
              "label" => "Basket Value",
              "value" => "",
              "color_code" => "#858383",
              "is_bold" => false,
              "is_italics" => false,
              "order" => 1
          ],
          /**
           * Total basket Amount, before tax value.
           */
          "order_total_amount" => [
              "label" => "Amount Before Tax",
              "value" => "",
              "color_code" => "#858383",
              "is_bold" => false,
              "is_italics" => false,
              "order" => 2
          ],
          /**
           * Total Gst Amount
           */
          "order_total_gst" => [
              "label" => "IGST",
              "value" => "",
              "color_code" => "#858383",
              "is_bold" => false,
              "is_italics" => false,
              "order" => 3
          ],
          /**
           * Total sgst Amount
           */
          "order_total_sgst" => [
            "label" => "SGST",
            "value" => "",
            "color_code" => "#858383",
            "is_bold" => false,
            "is_italics" => false,
            "order" => 4
        ],
         /**
           * Total sgst Amount
           */
          "order_total_cgst" => [
            "label" => "CGST",
            "value" => "",
            "color_code" => "#858383",
            "is_bold" => false,
            "is_italics" => false,
            "order" => 5
        ],
          /**
           * Total kfc amount
           */
//          "order_kfc_amount" => [
//              "label" => "KFC",
//              "value" => "",
//              "color_code" => "#858383",
//              "is_bold" => false,
//              "is_italics" => false,
//              "order" => 6
//          ],
          /**
           * Delivary charge
           */
          "order_delivery_charge" => [
              "label" => "Delivery charge",
              "value" => "",
              "color_code" => "#858383",
              "is_bold" => false,
              "is_italics" => false,
              "order" => 7
          ],

           /**
             * Courier charge
             */
            "order_courier_charge" => [
                "label" => "Courier charge",
                "value" => "",
                "color_code" => "#858383",
                "is_bold" => false,
                "is_italics" => false,
                "order" => 8
            ],

           /**
           * Total basket Amount, before tax value.
           */
          "order_discount_amount" => [
            "label" => "Discount",
            "value" => "",
            "color_code" => "#858383",
            "is_bold" => false,
            "is_italics" => false,
            "order" => 9
        ],
          /**
           * Total amount - Payable amount
           */
          "order_roundoff" => [
              "label" => "Round Off",
              "value" => "",
              "color_code" => "#858383",
              "is_bold" => false,
              "is_italics" => false,
              "order" => 10
          ],
          /**
           * Total amount - Payable amount
           */
          "total" => [
              "label" => "Total",
              "value" => "",
              "color_code" => "#000000",
              "is_bold" => true,
              "is_italics" => false,
              "order" => 11
          ],


      ],


];
