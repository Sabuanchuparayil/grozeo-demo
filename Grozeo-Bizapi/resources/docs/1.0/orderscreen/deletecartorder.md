# Delete Cart Order

---

### Details

| Method | Uri               | Authorization |
| :----- | :---------------- | :------------ |
| DELETE   | `cartorder/delete` | YES            |

### Request



```json
 {
"branch_id":4,
"order_method":2,
"data":[

    {
    "id":"587",
    "quantity":"10"

    },
    {
    "id":"715",
    "quantity":"100"	
    }

    ]

}
 
```

### Response 

```json
{
    "status": "ok",
    "data": {
        "hour": 48,
        "cart": {
            "all_available_product_quality": [
                {
                    "id": 587,
                    "cart_customer_id": 82,
                    "cart_group_id": 13,
                    "cart_product_id": 13,
                    "cart_branch_id": 4,
                    "cart_order_qty": 1,
                    "cart_price": null,
                    "cart_retail_price": null,
                    "cart_sales_price": null,
                    "cart_subcategory_id": null,
                    "cart_package_type_id": null,
                    "cart_is_taxable": 0,
                    "cart_cgst": null,
                    "cart_sgst": null,
                    "cart_igst": null,
                    "cart_discount": 0,
                    "cart_sku_id": null,
                    "cart_status": "added",
                    "order_method": 2,
                    "item": {
                        "fsi_uid": 13,
                        "item_group_id": 13,
                        "item_name": "Koflet Lozenges",
                        "brand_name": "Himalaya",
                        "category_id": 2,
                        "category_name": "cough",
                        "variant": "packet",
                        "isMedicine": 0,
                        "item_master": [
                            {
                                "stit_ID": 13,
                                "stit_fsiuid": 13,
                                "quantity": "10 lozenges",
                                "itemId": 13,
                                "short_description": "Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx.",
                                "long_description": "<p>Information about Himalaya Koflet Lozenges</p>\n\n<p>Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx. In addition, the anti-allergic, antimicrobial and immune-resistance building properties provide relief from cough of varied etiology. It contains Trikatu, Yashtimadhu and Clove.<br />\n&nbsp;<br />\nRole of Key ingredients:</p>\n\n<ul>\n\t<li>Trikatu a polyherbal composition containing Long Pepper, Black Pepper and Ginger, treats chest congestion by reducing mucus. It also works as an anti microbial, thereby helping in managing upper respiratory infections.</li>\n\t<li>Lavanga (Clove) is useful in coughs due to its antitussive activity and other upper respiratory disorders due to its antimicrobial activity. It has been used in Ayurveda as a popular remedy for sore throat.</li>\n\t<li>Licorice (Yashtimadhu) has antitussive, expectorant and immune-enhancing properties that are helpful in relieving cough.</li>\n</ul>\n\n<p><br />\n&nbsp;<br />\nDirections for use:<br />\nOne lozenge three-four times a day, or as directed by the physician.</p>\n",
                                "stit_displaylabel": "",
                                "prescription": null,
                                "main_image": [
                                    {
                                        "id": 8,
                                        "product_id": 13,
                                        "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet1.jpg",
                                        "image_thumb_url": null
                                    }
                                ],
                                "stock_available": 1,
                                "selling_prize": 10,
                                "godown_itemId": 66,
                                "mrp": 50,
                                "default_value": 1
                            },
                            {
                                "stit_ID": 14,
                                "stit_fsiuid": 13,
                                "quantity": "200 lozenges",
                                "itemId": 14,
                                "short_description": "Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx.",
                                "long_description": "<p>Information about Himalaya Koflet Lozenges</p>\n\n<p>Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx. In addition, the anti-allergic, antimicrobial and immune-resistance building properties provide relief from cough of varied etiology. It contains Trikatu, Yashtimadhu and Clove.<br />\n&nbsp;<br />\nRole of Key ingredients:</p>\n\n<ul>\n\t<li>Trikatu a polyherbal composition containing Long Pepper, Black Pepper and Ginger, treats chest congestion by reducing mucus. It also works as an anti microbial, thereby helping in managing upper respiratory infections.</li>\n\t<li>Lavanga (Clove) is useful in coughs due to its antitussive activity and other upper respiratory disorders due to its antimicrobial activity. It has been used in Ayurveda as a popular remedy for sore throat.</li>\n\t<li>Licorice (Yashtimadhu) has antitussive, expectorant and immune-enhancing properties that are helpful in relieving cough.</li>\n</ul>\n\n<p><br />\n&nbsp;<br />\nDirections for use:<br />\nOne lozenge three-four times a day, or as directed by the physician.</p>\n",
                                "stit_displaylabel": "",
                                "prescription": null,
                                "main_image": [
                                    {
                                        "id": 9,
                                        "product_id": 14,
                                        "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet200.jpg",
                                        "image_thumb_url": null
                                    }
                                ],
                                "stock_available": 0,
                                "selling_prize": 0,
                                "godown_itemId": 42,
                                "mrp": 0,
                                "default_value": 0
                            }
                        ]
                    },
                    "availabe_quantity": 1,
                    "notavailable_quantity": 89
                },
                {
                    "id": 699,
                    "cart_customer_id": 82,
                    "cart_group_id": 27,
                    "cart_product_id": 28,
                    "cart_branch_id": 4,
                    "cart_order_qty": 1,
                    "cart_price": null,
                    "cart_retail_price": null,
                    "cart_sales_price": null,
                    "cart_subcategory_id": null,
                    "cart_package_type_id": null,
                    "cart_is_taxable": 0,
                    "cart_cgst": null,
                    "cart_sgst": null,
                    "cart_igst": null,
                    "cart_discount": 0,
                    "cart_sku_id": null,
                    "cart_status": "added",
                    "order_method": 2,
                    "item": {
                        "fsi_uid": 27,
                        "item_group_id": 27,
                        "item_name": "Van Tulsi Cough Syrup",
                        "brand_name": "Basic Ayurveda",
                        "category_id": 19,
                        "category_name": "Ayurvedic Supplements",
                        "variant": "syrup",
                        "isMedicine": 0,
                        "item_master": [
                            {
                                "stit_ID": 28,
                                "stit_fsiuid": 27,
                                "quantity": "200ml ",
                                "itemId": 28,
                                "short_description": "Basic Ayurveda Van Tulsi Cough Syrup contains van tulsi(ocimum sanctum), mulethi(glycyrrhiza glabra), banafsha(viola odorata), talispatra(taxus baccata)\nhaldi(curcuma longa), sudh tankan(purified borex) and sudh navsadhar(ammonium chloride). It is very effective herbal cough syrup to give you faster relief from chest congestion.",
                                "long_description": "<p>Information about Basic Ayurveda Van Tulsi Cough Syrup</p>\n\n<p>Basic Ayurveda Van Tulsi Cough Syrup contains van tulsi(ocimum sanctum), mulethi(glycyrrhiza glabra), banafsha(viola odorata), talispatra(taxus baccata)<br />\nhaldi(curcuma longa), sudh tankan(purified borex) and sudh navsadhar(ammonium chloride). It is very effective herbal cough syrup to give you faster relief from chest congestion. It can be used for children to old age people.<br />\n<br />\nKey benefits of Basic Ayurveda Van Tulsi Cough Syrup:<br />\nIt facilitates the easy expectoration of tenacious mucus from the respiratory tract.<br />\nIt provides rapid relief from chest congestion.<br />\nIt can be used for children to old age people.<br />\n<br />\nDirection of use:<br />\nChildren: Take 5 ml twice a day.<br />\nAdults: Take 10 ml twice a day.</p>\n",
                                "stit_displaylabel": "",
                                "prescription": null,
                                "main_image": [],
                                "stock_available": 100,
                                "selling_prize": 5,
                                "godown_itemId": 31,
                                "mrp": 20,
                                "default_value": 1
                            }
                        ]
                    },
                    "availabe_quantity": 1,
                    "notavailable_quantity": 0
                }
            ],
            "not_available_product_quality_in_48_hours": [
                {
                    "id": 587,
                    "cart_customer_id": 82,
                    "cart_group_id": 13,
                    "cart_product_id": 13,
                    "cart_branch_id": 4,
                    "cart_order_qty": 89,
                    "cart_price": null,
                    "cart_retail_price": null,
                    "cart_sales_price": null,
                    "cart_subcategory_id": null,
                    "cart_package_type_id": null,
                    "cart_is_taxable": 0,
                    "cart_cgst": null,
                    "cart_sgst": null,
                    "cart_igst": null,
                    "cart_discount": 0,
                    "cart_sku_id": null,
                    "cart_status": "added",
                    "order_method": 2,
                    "item": {
                        "fsi_uid": 13,
                        "item_group_id": 13,
                        "item_name": "Koflet Lozenges",
                        "brand_name": "Himalaya",
                        "category_id": 2,
                        "category_name": "cough",
                        "variant": "packet",
                        "isMedicine": 0,
                        "item_master": [
                            {
                                "stit_ID": 13,
                                "stit_fsiuid": 13,
                                "quantity": "10 lozenges",
                                "itemId": 13,
                                "short_description": "Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx.",
                                "long_description": "<p>Information about Himalaya Koflet Lozenges</p>\n\n<p>Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx. In addition, the anti-allergic, antimicrobial and immune-resistance building properties provide relief from cough of varied etiology. It contains Trikatu, Yashtimadhu and Clove.<br />\n&nbsp;<br />\nRole of Key ingredients:</p>\n\n<ul>\n\t<li>Trikatu a polyherbal composition containing Long Pepper, Black Pepper and Ginger, treats chest congestion by reducing mucus. It also works as an anti microbial, thereby helping in managing upper respiratory infections.</li>\n\t<li>Lavanga (Clove) is useful in coughs due to its antitussive activity and other upper respiratory disorders due to its antimicrobial activity. It has been used in Ayurveda as a popular remedy for sore throat.</li>\n\t<li>Licorice (Yashtimadhu) has antitussive, expectorant and immune-enhancing properties that are helpful in relieving cough.</li>\n</ul>\n\n<p><br />\n&nbsp;<br />\nDirections for use:<br />\nOne lozenge three-four times a day, or as directed by the physician.</p>\n",
                                "stit_displaylabel": "",
                                "prescription": null,
                                "main_image": [
                                    {
                                        "id": 8,
                                        "product_id": 13,
                                        "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet1.jpg",
                                        "image_thumb_url": null
                                    }
                                ],
                                "stock_available": 1,
                                "selling_prize": 10,
                                "godown_itemId": 66,
                                "mrp": 50,
                                "default_value": 1
                            },
                            {
                                "stit_ID": 14,
                                "stit_fsiuid": 13,
                                "quantity": "200 lozenges",
                                "itemId": 14,
                                "short_description": "Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx.",
                                "long_description": "<p>Information about Himalaya Koflet Lozenges</p>\n\n<p>Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx. In addition, the anti-allergic, antimicrobial and immune-resistance building properties provide relief from cough of varied etiology. It contains Trikatu, Yashtimadhu and Clove.<br />\n&nbsp;<br />\nRole of Key ingredients:</p>\n\n<ul>\n\t<li>Trikatu a polyherbal composition containing Long Pepper, Black Pepper and Ginger, treats chest congestion by reducing mucus. It also works as an anti microbial, thereby helping in managing upper respiratory infections.</li>\n\t<li>Lavanga (Clove) is useful in coughs due to its antitussive activity and other upper respiratory disorders due to its antimicrobial activity. It has been used in Ayurveda as a popular remedy for sore throat.</li>\n\t<li>Licorice (Yashtimadhu) has antitussive, expectorant and immune-enhancing properties that are helpful in relieving cough.</li>\n</ul>\n\n<p><br />\n&nbsp;<br />\nDirections for use:<br />\nOne lozenge three-four times a day, or as directed by the physician.</p>\n",
                                "stit_displaylabel": "",
                                "prescription": null,
                                "main_image": [
                                    {
                                        "id": 9,
                                        "product_id": 14,
                                        "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet200.jpg",
                                        "image_thumb_url": null
                                    }
                                ],
                                "stock_available": 0,
                                "selling_prize": 0,
                                "godown_itemId": 42,
                                "mrp": 0,
                                "default_value": 0
                            }
                        ]
                    },
                    "availabe_quantity": 1,
                    "notavailable_quantity": 89
                },
                {
                    "id": 700,
                    "cart_customer_id": 82,
                    "cart_group_id": 111,
                    "cart_product_id": 110,
                    "cart_branch_id": 4,
                    "cart_order_qty": 1,
                    "cart_price": null,
                    "cart_retail_price": null,
                    "cart_sales_price": null,
                    "cart_subcategory_id": null,
                    "cart_package_type_id": null,
                    "cart_is_taxable": 0,
                    "cart_cgst": null,
                    "cart_sgst": null,
                    "cart_igst": null,
                    "cart_discount": 0,
                    "cart_sku_id": null,
                    "cart_status": "added",
                    "order_method": 2,
                    "item": {
                        "fsi_uid": 111,
                        "item_group_id": 111,
                        "item_name": "Heart Guard Capsule",
                        "brand_name": "Accu Chek",
                        "category_id": 4,
                        "category_name": "Accu-Check",
                        "variant": "Tablet",
                        "isMedicine": 0,
                        "item_master": [
                            {
                                "stit_ID": 110,
                                "stit_fsiuid": 111,
                                "quantity": "52tabs",
                                "itemId": 110,
                                "short_description": "Strips",
                                "long_description": "",
                                "stit_displaylabel": "15 strips",
                                "prescription": null,
                                "main_image": [],
                                "stock_available": 0,
                                "selling_prize": 80,
                                "godown_itemId": 100,
                                "mrp": 100,
                                "default_value": 1
                            }
                        ]
                    },
                    "availabe_quantity": 0,
                    "notavailable_quantity": 1
                }
            ],
            "all_product_in_48_hours": [
                {
                    "id": 587,
                    "cart_customer_id": 82,
                    "cart_group_id": 13,
                    "cart_product_id": 13,
                    "cart_branch_id": 4,
                    "cart_order_qty": 90,
                    "cart_price": null,
                    "cart_retail_price": null,
                    "cart_sales_price": null,
                    "cart_subcategory_id": null,
                    "cart_package_type_id": null,
                    "cart_is_taxable": 0,
                    "cart_cgst": null,
                    "cart_sgst": null,
                    "cart_igst": null,
                    "cart_discount": 0,
                    "cart_sku_id": null,
                    "cart_status": "added",
                    "order_method": 2,
                    "item": {
                        "fsi_uid": 13,
                        "item_group_id": 13,
                        "item_name": "Koflet Lozenges",
                        "brand_name": "Himalaya",
                        "category_id": 2,
                        "category_name": "cough",
                        "variant": "packet",
                        "isMedicine": 0,
                        "item_master": [
                            {
                                "stit_ID": 13,
                                "stit_fsiuid": 13,
                                "quantity": "10 lozenges",
                                "itemId": 13,
                                "short_description": "Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx.",
                                "long_description": "<p>Information about Himalaya Koflet Lozenges</p>\n\n<p>Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx. In addition, the anti-allergic, antimicrobial and immune-resistance building properties provide relief from cough of varied etiology. It contains Trikatu, Yashtimadhu and Clove.<br />\n&nbsp;<br />\nRole of Key ingredients:</p>\n\n<ul>\n\t<li>Trikatu a polyherbal composition containing Long Pepper, Black Pepper and Ginger, treats chest congestion by reducing mucus. It also works as an anti microbial, thereby helping in managing upper respiratory infections.</li>\n\t<li>Lavanga (Clove) is useful in coughs due to its antitussive activity and other upper respiratory disorders due to its antimicrobial activity. It has been used in Ayurveda as a popular remedy for sore throat.</li>\n\t<li>Licorice (Yashtimadhu) has antitussive, expectorant and immune-enhancing properties that are helpful in relieving cough.</li>\n</ul>\n\n<p><br />\n&nbsp;<br />\nDirections for use:<br />\nOne lozenge three-four times a day, or as directed by the physician.</p>\n",
                                "stit_displaylabel": "",
                                "prescription": null,
                                "main_image": [
                                    {
                                        "id": 8,
                                        "product_id": 13,
                                        "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet1.jpg",
                                        "image_thumb_url": null
                                    }
                                ],
                                "stock_available": 1,
                                "selling_prize": 10,
                                "godown_itemId": 66,
                                "mrp": 50,
                                "default_value": 1
                            },
                            {
                                "stit_ID": 14,
                                "stit_fsiuid": 13,
                                "quantity": "200 lozenges",
                                "itemId": 14,
                                "short_description": "Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx.",
                                "long_description": "<p>Information about Himalaya Koflet Lozenges</p>\n\n<p>Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx. In addition, the anti-allergic, antimicrobial and immune-resistance building properties provide relief from cough of varied etiology. It contains Trikatu, Yashtimadhu and Clove.<br />\n&nbsp;<br />\nRole of Key ingredients:</p>\n\n<ul>\n\t<li>Trikatu a polyherbal composition containing Long Pepper, Black Pepper and Ginger, treats chest congestion by reducing mucus. It also works as an anti microbial, thereby helping in managing upper respiratory infections.</li>\n\t<li>Lavanga (Clove) is useful in coughs due to its antitussive activity and other upper respiratory disorders due to its antimicrobial activity. It has been used in Ayurveda as a popular remedy for sore throat.</li>\n\t<li>Licorice (Yashtimadhu) has antitussive, expectorant and immune-enhancing properties that are helpful in relieving cough.</li>\n</ul>\n\n<p><br />\n&nbsp;<br />\nDirections for use:<br />\nOne lozenge three-four times a day, or as directed by the physician.</p>\n",
                                "stit_displaylabel": "",
                                "prescription": null,
                                "main_image": [
                                    {
                                        "id": 9,
                                        "product_id": 14,
                                        "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet200.jpg",
                                        "image_thumb_url": null
                                    }
                                ],
                                "stock_available": 0,
                                "selling_prize": 0,
                                "godown_itemId": 42,
                                "mrp": 0,
                                "default_value": 0
                            }
                        ]
                    },
                    "availabe_quantity": 1,
                    "notavailable_quantity": 89
                },
                {
                    "id": 699,
                    "cart_customer_id": 82,
                    "cart_group_id": 27,
                    "cart_product_id": 28,
                    "cart_branch_id": 4,
                    "cart_order_qty": 1,
                    "cart_price": null,
                    "cart_retail_price": null,
                    "cart_sales_price": null,
                    "cart_subcategory_id": null,
                    "cart_package_type_id": null,
                    "cart_is_taxable": 0,
                    "cart_cgst": null,
                    "cart_sgst": null,
                    "cart_igst": null,
                    "cart_discount": 0,
                    "cart_sku_id": null,
                    "cart_status": "added",
                    "order_method": 2,
                    "item": {
                        "fsi_uid": 27,
                        "item_group_id": 27,
                        "item_name": "Van Tulsi Cough Syrup",
                        "brand_name": "Basic Ayurveda",
                        "category_id": 19,
                        "category_name": "Ayurvedic Supplements",
                        "variant": "syrup",
                        "isMedicine": 0,
                        "item_master": [
                            {
                                "stit_ID": 28,
                                "stit_fsiuid": 27,
                                "quantity": "200ml ",
                                "itemId": 28,
                                "short_description": "Basic Ayurveda Van Tulsi Cough Syrup contains van tulsi(ocimum sanctum), mulethi(glycyrrhiza glabra), banafsha(viola odorata), talispatra(taxus baccata)\nhaldi(curcuma longa), sudh tankan(purified borex) and sudh navsadhar(ammonium chloride). It is very effective herbal cough syrup to give you faster relief from chest congestion.",
                                "long_description": "<p>Information about Basic Ayurveda Van Tulsi Cough Syrup</p>\n\n<p>Basic Ayurveda Van Tulsi Cough Syrup contains van tulsi(ocimum sanctum), mulethi(glycyrrhiza glabra), banafsha(viola odorata), talispatra(taxus baccata)<br />\nhaldi(curcuma longa), sudh tankan(purified borex) and sudh navsadhar(ammonium chloride). It is very effective herbal cough syrup to give you faster relief from chest congestion. It can be used for children to old age people.<br />\n<br />\nKey benefits of Basic Ayurveda Van Tulsi Cough Syrup:<br />\nIt facilitates the easy expectoration of tenacious mucus from the respiratory tract.<br />\nIt provides rapid relief from chest congestion.<br />\nIt can be used for children to old age people.<br />\n<br />\nDirection of use:<br />\nChildren: Take 5 ml twice a day.<br />\nAdults: Take 10 ml twice a day.</p>\n",
                                "stit_displaylabel": "",
                                "prescription": null,
                                "main_image": [],
                                "stock_available": 100,
                                "selling_prize": 5,
                                "godown_itemId": 31,
                                "mrp": 20,
                                "default_value": 1
                            }
                        ]
                    },
                    "availabe_quantity": 1,
                    "notavailable_quantity": 0
                },
                {
                    "id": 700,
                    "cart_customer_id": 82,
                    "cart_group_id": 111,
                    "cart_product_id": 110,
                    "cart_branch_id": 4,
                    "cart_order_qty": 1,
                    "cart_price": null,
                    "cart_retail_price": null,
                    "cart_sales_price": null,
                    "cart_subcategory_id": null,
                    "cart_package_type_id": null,
                    "cart_is_taxable": 0,
                    "cart_cgst": null,
                    "cart_sgst": null,
                    "cart_igst": null,
                    "cart_discount": 0,
                    "cart_sku_id": null,
                    "cart_status": "added",
                    "order_method": 2,
                    "item": {
                        "fsi_uid": 111,
                        "item_group_id": 111,
                        "item_name": "Heart Guard Capsule",
                        "brand_name": "Accu Chek",
                        "category_id": 4,
                        "category_name": "Accu-Check",
                        "variant": "Tablet",
                        "isMedicine": 0,
                        "item_master": [
                            {
                                "stit_ID": 110,
                                "stit_fsiuid": 111,
                                "quantity": "52tabs",
                                "itemId": 110,
                                "short_description": "Strips",
                                "long_description": "",
                                "stit_displaylabel": "15 strips",
                                "prescription": null,
                                "main_image": [],
                                "stock_available": 0,
                                "selling_prize": 80,
                                "godown_itemId": 100,
                                "mrp": 100,
                                "default_value": 1
                            }
                        ]
                    },
                    "availabe_quantity": 0,
                    "notavailable_quantity": 1
                }
            ]
        },
        "price": {
            "all_available_product_price": {
                "total_gst": 10.6,
                "basket_price": 15,
                "delivery_charge": 0,
                "total": 25.6
            },
            "not_available_product_price_48_hours": {
                "total_gst": 899.6,
                "basket_price": 388,
                "delivery_charge": 0,
                "total": 1287.6,
                "discount": 515.04
            },
            "all_product_price_48_hours": {
                "total_gst": 910.2,
                "basket_price": 394,
                "delivery_charge": 0,
                "total": 1304.2,
                "discount": 521.6800000000001
            }
        }
    }
}
```
