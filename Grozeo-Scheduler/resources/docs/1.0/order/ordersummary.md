# Order Summary


### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| GET | `api/order/summary/{order_id}` | YES |





### Response

```json
{
    "status": "ok",
    "data": {
        "order_id": 2,
        "order_order_id": "2005060002",
        "status_id": 7,
        "created_at": "06-05-2020",
        "order_status": {
            "status_id": 7,
            "status": "Processing"
        },
        "delivery_address": {
            "id": 2,
            "order_id": "2005060002",
            "customer_order_id": 2,
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
    }
}
````

