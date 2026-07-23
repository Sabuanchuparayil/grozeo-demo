# HealthConcern Screen


### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `api/healthconcernscreen` | NO |



### Request

```json

{
 "requested_id":"1",
 "branch_id":"1",
 "order_method":"1",

 "sort": {
       "price": "1"
   },
  "filter": {
       "category": ["1"],
       "brands": [],
       "price_range":["0","100"]
   }

}

 

```
### Response

```json
{
    "status": "ok",
    "data": [
        {
            "id": 24,
            "screen": "Shop_by_concern",
            "type": "product",
            "type_id": 9,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "Leorem hayr tfhfh v ff",
            "title": "Health by Concern",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 1,
            "delivery_type": 1,
            "value": [
                {
                    "fsi_uid": 7,
                    "fsi_item_id": 3,
                    "fsi_count": "-2",
                    "fsi_brand_id": 16,
                    "fsi_def_itemmaster_id": 3,
                    "isMedicine": 0,
                    "fsi_displaylabel": "",
                    "item_name": "SBL Stobal Cough Syrup",
                    "brand_name": "SBL",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": " ",
                    "item_master": [
                        {
                            "stit_ID": 3,
                            "stit_fsiuid": 7,
                            "quantity": "60 ml Syrup",
                            "itemId": 3,
                            "short_description": "SBLâ€™s StodalÂ Or Stobal cough syrupÂ with no narcotics helps in facilitating recovery from cough and wheezes and thus, often helps in avoiding usage of antibiotics. Stodal+cough syrup is a safe and effective remedy, and is well suited for all age groups. It has no narcotics. Safe for pregnant women, children and elderly persons.\n",
                            "long_description": "<p><strong>SBL&rsquo;s Stodal&nbsp;Or Stobal cough syrup</strong>&nbsp;with no narcotics helps in facilitating recovery from cough and wheezes and thus, often helps in avoiding usage of antibiotics. Stodal+cough syrup is a safe and effective remedy, and is well suited for all age groups. It has no narcotics. Safe for pregnant women, children and elderly persons.<br />\n<br />\nINGREDIENTS: SBL&rsquo;s stodal&nbsp;Or Stobal&nbsp;cough syrup is a clinically proven product and contains well balanced homeopathic ingredients like Pulsatilla nigricans 3, Justicia adhatoda 3x, Rumex crispus 3x, Ipecacuanha 3 etc.<br />\n<br />\nDOSAGE: Adults: one tablespoon of SBL Stodal Cough Syrup 3-5 time a day. Children: one teaspoon 3-5 times a day or as directed by the physician.</p>\n",
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 140,
                                    "product_id": 3,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg"
                                }
                            ],
                            "additional_image": [
                                {
                                    "id": 141,
                                    "product_id": 3,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-2fff7b05-cf95-46ff-b5d2-b5c6fdd51096.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-2fff7b05-cf95-46ff-b5d2-b5c6fdd51096.jpg"
                                },
                                {
                                    "id": 142,
                                    "product_id": 3,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-63615a39-529b-425c-b63e-82387b07e732.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-63615a39-529b-425c-b63e-82387b07e732.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 18
                        }
                    ]
                },
                {
                    "fsi_uid": 31,
                    "fsi_item_id": 37,
                    "fsi_count": "-2",
                    "fsi_brand_id": 30,
                    "fsi_def_itemmaster_id": 31,
                    "isMedicine": 1,
                    "fsi_displaylabel": "",
                    "item_name": "DIGENE TOTAL 40mg ",
                    "brand_name": "PANTOPRAZOLE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "40mg ",
                    "item_master": [
                        {
                            "stit_ID": 31,
                            "stit_fsiuid": 31,
                            "quantity": "",
                            "itemId": 31,
                            "short_description": "Digene Total 40mg Tablet is a medicine that reduces the amount of acid produced in your stomach. It is used for treating acid-related diseases of the stomach and intestine such as heartburn, acid reflux, peptic ulcer disease, and some other stomach conditions associated with excessive acid production.",
                            "long_description": null,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 219,
                                    "product_id": 31,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-84a88d92-4f2f-4d0e-96c9-c970157b31bd.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-84a88d92-4f2f-4d0e-96c9-c970157b31bd.jpg"
                                }
                            ],
                            "additional_image": [
                                {
                                    "id": 216,
                                    "product_id": 31,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b5de6a8b-d5a1-461a-a98d-9ef09f134232.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b5de6a8b-d5a1-461a-a98d-9ef09f134232.jpg"
                                },
                                {
                                    "id": 217,
                                    "product_id": 31,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-93ede3fa-0c52-4c4b-8a1b-600e7db03672.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-93ede3fa-0c52-4c4b-8a1b-600e7db03672.jpg"
                                },
                                {
                                    "id": 218,
                                    "product_id": 31,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-c5017954-5009-4eaf-8b45-4a6e2098dbb2.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-c5017954-5009-4eaf-8b45-4a6e2098dbb2.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 389
                        }
                    ]
                }
            ],
            "total_count": 2,
            "min_count": 9,
            "pagenate_details": {
                "currentpage": 1,
                "first_page_url": "http://localhost/pharmacy/public/api/healthconcernscreen?page=1",
                "from": 1,
                "last_page": 1,
                "last_page_url": "http://localhost/pharmacy/public/api/healthconcernscreen?page=1",
                "next_page_url": null,
                "path": "http://localhost/pharmacy/public/api/healthconcernscreen",
                "per_page": 10,
                "prev_page_url": null,
                "to": 2,
                "total": 2
            }
        }
    ]
}
```
