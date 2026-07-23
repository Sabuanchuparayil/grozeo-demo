# Order Complete


### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| GET | `api/orders/{order_order_id}/complete` | YES |





### Response

```json
{
    "status": "ok",
    "data": {
        "order_id": "2005060001",
        "order_shipping_address": [
            {
                "id": 1,
                "order_id": "2005060001",
                "customer_order_id": 1,
                "order_customer_name": "harish",
                "order_customer_email": "harish.a@velosit.in",
                "order_customer_id": 82,
                "order_contact_no": "9995256535",
                "order_house_no": "123",
                "order_house_name": "thakazhy",
                "order_land_mark": "alappuzha",
                "order_city": "alappuzha",
                "order_post": "chirayakom",
                "order_state": "kerala",
                "order_pin": 688562,
                "order_country": "India",
                "order_deli_note": null,
                "order_is_free_deli": null,
                "order_latitude": 10.8505159,
                "order_longitude": 76.2710833
            }
        ],
        "order_items": [
            {
                "item_id": 1,
                "item_order_id": "2005060001",
                "customer_order_id": 1,
                "item_product_id": 13,
                "item_isMedicine": 0,
                "item_group_id": 13,
                "item_order_qty": 1,
                "item_order_qty_scanned": null,
                "item_price": 189.2,
                "item_retail_price": 220,
                "item_sales_price": 189.2,
                "item_amount": 0,
                "item_subcategory_id": null,
                "item_package_type_id": null,
                "item_is_taxable": 0,
                "item_cgst": null,
                "item_sgst": null,
                "item_igst": null,
                "item_discount": null,
                "item_sku_id": null,
                "item_status": null,
                "image": {
                    "id": 264,
                    "product_id": 13,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-cb2f47af-cbf8-44ac-947d-1fc7a4dfad60.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-cb2f47af-cbf8-44ac-947d-1fc7a4dfad60.jpg"
                },
                "item": {
                    "stit_ID": 13,
                    "stit_sku": "cough Himalaya Koflet Lozenges packet 10 lozenges",
                    "stit_brand_name": "Himalaya"
                }
            },
            {
                "item_id": 2,
                "item_order_id": "2005060001",
                "customer_order_id": 1,
                "item_product_id": 7,
                "item_isMedicine": 0,
                "item_group_id": 10,
                "item_order_qty": 2,
                "item_order_qty_scanned": null,
                "item_price": 199,
                "item_retail_price": 130,
                "item_sales_price": 99.5,
                "item_amount": 0,
                "item_subcategory_id": null,
                "item_package_type_id": null,
                "item_is_taxable": 0,
                "item_cgst": null,
                "item_sgst": null,
                "item_igst": null,
                "item_discount": null,
                "item_sku_id": null,
                "item_status": null,
                "image": {
                    "id": 152,
                    "product_id": 7,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0a6c958e-e66e-489b-a283-4e4306bcde7d.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0a6c958e-e66e-489b-a283-4e4306bcde7d.jpg"
                },
                "item": {
                    "stit_ID": 7,
                    "stit_sku": "cough Vicks Cough Drops  190 lozenges",
                    "stit_brand_name": "Vicks"
                }
            }
        ],
        "order_total": 388,
        "order_subtotal": 388.2,
        "order_kfc": 3.88,
        "order_roundoff": -0.2,
        "order_shipping_charge": 0,
        "order_total_gst": 213.08,
        "order_amount": 171.24,
        "order_discount": 0,
        "payment_mode_val": 2,
        "payment_mode": "online",
        "order_trackURL": "",
        "order_status": {
            "status_id": 4,
            "status": "Order Placed"
        },
        "order_primary_key": 1,
        "order_DeliveryDriver": "",
        "order_DeliveryDriverNumber": "",
        "style": [
            {
                "label": "Basket Value",
                "value": "388.2",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 1
            },
            {
                "label": "Amount Before Tax",
                "value": "171.24",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 2
            },
            {
                "label": "Discount",
                "value": "0",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 3
            },
            {
                "label": "Gst",
                "value": "213.08",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 4
            },
            {
                "label": "KFC",
                "value": "3.88",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 5
            },
            {
                "label": "Delivery",
                "value": "0",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 6
            },
            {
                "label": "Round Off",
                "value": "-0.2",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 7
            },
            {
                "label": "Total",
                "value": "₹ 388",
                "color_code": "#000000",
                "is_bold": true,
                "is_italics": false,
                "order": 8
            }
        ]
    }
}
````

