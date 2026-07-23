# Order Detail


### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| GET | `api/order/detail/{order_id}` | YES |





### Response

```json
{
    "status": "ok",
    "data": [
        {
            "order_id": 1,
            "order_order_id": "2005060001",
            "order_customer_id": 82,
            "order_total_amount": 171.24,
            "order_delivery_charge": 0,
            "order_total_gst": 213.08,
            "order_kfc_amount": 3.88,
            "order_discount_amount": 0,
            "order_discount_add_total": 0,
            "order_mrp": 480,
            "subtotal": 388.2,
            "order_roundoff": -0.2,
            "total": 388,
            "order_saved_amount": 91.8,
            "order_method": 2,
            "payment_mode": 2,
            "order_branch_id": 1,
            "order_company_id": 1,
            "status_id": 7,
            "order_status_addinfo": "",
            "order_confirm_date": "2020-05-06",
            "order_confirmed_on": "2020-05-06 05:37:36",
            "order_cancel_date": null,
            "order_packedbags_count": 0,
            "order_payment_response_received": 1,
            "order_payment_status": "Success",
            "order_payment_gateway": "Instamojo",
            "order_payment_gateway_refid": "MOJO0506U05N77077820",
            "order_trackURL": "",
            "order_DeliveryRatingStar": 0,
            "order_DeliveryRatingComment": "",
            "order_DeliveryDriver": "",
            "order_DeliveryDriverNumber": "",
            "order_HasReturn": 0,
            "order_ItemsReturned": null,
            "order_ReturnVerified": 0,
            "order_approvedOn": "0000-00-00 00:00:00",
            "order_approvalStatus": 1,
            "order_approvedBy": 0,
            "order_status": {
                "status_id": 7,
                "status": "Processing"
            },
            "delivery_address": {
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
            },
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
                    "order_unique_item": {
                        "fsi_uid": 13,
                        "item_group_id": 13,
                        "item_name": "Koflet Lozenges",
                        "brand_name": "Himalaya",
                        "category_id": 2,
                        "category_name": "cough",
                        "variant": "packet",
                        "item_master": [
                            {
                                "stit_ID": 13,
                                "stit_fsiuid": 13,
                                "quantity": "10 lozenges",
                                "itemId": 13,
                                "short_description": "Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx.",
                                "long_description": "<p>Information about Himalaya Koflet Lozenges</p>\n\n<p>Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx. In addition, the anti-allergic, antimicrobial and immune-resistance building properties provide relief from cough of varied etiology. It contains Trikatu, Yashtimadhu and Clove.<br />\n&nbsp;<br />\nRole of Key ingredients:</p>\n\n<ul>\n\t<li>Trikatu a polyherbal composition containing Long Pepper, Black Pepper and Ginger, treats chest congestion by reducing mucus. It also works as an anti microbial, thereby helping in managing upper respiratory infections.</li>\n\t<li>Lavanga (Clove) is useful in coughs due to its antitussive activity and other upper respiratory disorders due to its antimicrobial activity. It has been used in Ayurveda as a popular remedy for sore throat.</li>\n\t<li>Licorice (Yashtimadhu) has antitussive, expectorant and immune-enhancing properties that are helpful in relieving cough.</li>\n</ul>\n\n<p><br />\n&nbsp;<br />\nDirections for use:<br />\nOne lozenge three-four times a day, or as directed by the physician.</p>\n",
                                "main_image": [
                                    {
                                        "id": 263,
                                        "product_id": 13,
                                        "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                                        "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
                                    }
                                ]
                            },
                            {
                                "stit_ID": 14,
                                "stit_fsiuid": 13,
                                "quantity": "200 lozenges",
                                "itemId": 14,
                                "short_description": "Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx.",
                                "long_description": "<p>Information about Himalaya Koflet Lozenges</p>\n\n<p>Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx. In addition, the anti-allergic, antimicrobial and immune-resistance building properties provide relief from cough of varied etiology. It contains Trikatu, Yashtimadhu and Clove.<br />\n&nbsp;<br />\nRole of Key ingredients:</p>\n\n<ul>\n\t<li>Trikatu a polyherbal composition containing Long Pepper, Black Pepper and Ginger, treats chest congestion by reducing mucus. It also works as an anti microbial, thereby helping in managing upper respiratory infections.</li>\n\t<li>Lavanga (Clove) is useful in coughs due to its antitussive activity and other upper respiratory disorders due to its antimicrobial activity. It has been used in Ayurveda as a popular remedy for sore throat.</li>\n\t<li>Licorice (Yashtimadhu) has antitussive, expectorant and immune-enhancing properties that are helpful in relieving cough.</li>\n</ul>\n\n<p><br />\n&nbsp;<br />\nDirections for use:<br />\nOne lozenge three-four times a day, or as directed by the physician.</p>\n",
                                "main_image": [
                                    {
                                        "id": 261,
                                        "product_id": 14,
                                        "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg",
                                        "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg"
                                    }
                                ]
                            }
                        ]
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
                    "order_unique_item": {
                        "fsi_uid": 10,
                        "item_group_id": 10,
                        "item_name": "Cough Drops",
                        "brand_name": "Vicks",
                        "category_id": 2,
                        "category_name": "cough",
                        "variant": "",
                        "item_master": [
                            {
                                "stit_ID": 7,
                                "stit_fsiuid": 10,
                                "quantity": "190 lozenges",
                                "itemId": 7,
                                "short_description": "Vicks Cough Drops contain Karpoor, Pudinah Ke Phool, Nilgiri Tel, Ajwain Ke Phool, and Flavoured Sugar as active ingredients. It acts as a cough suppressant and comes in ginger, honey and menthol flavors",
                                "long_description": "<p>Vicks Cough Drops contain Karpoor, Pudinah Ke Phool, Nilgiri Tel, Ajwain Ke Phool, and Flavoured Sugar as active ingredients. It acts as a cough suppressant and comes in ginger, honey and menthol flavors.<br />\n<br />\nKey benefits/uses of Vicks Cough Drops:<br />\n- Works as anesthetic and cough suppressant<br />\n- Relieves the tingling sensation even before cough starts<br />\n- Menthol: Provides a cooling sensation when applied to the skin or other tissues, treats minor sore throat pain, or mouth irritation caused by a canker sore<br />\n- Mint: Opens the air code slightly so that the sufferer of respiratory problems like bronchitis, asthma, pneumonia feel relief after taking it<br />\n- Ajwain: Possesses anesthetic property<br />\n<br />\nDirection for use/Dosage:<br />\n- As described by the doctor<br />\n<br />\nRecommendation:<br />\n- Children above 2 years of age<br />\n<br />\nIndications:<br />\n- Cough<br />\n<br />\nStorage instructions:<br />\n- Store in a cool, dry, &amp; dark place<br />\n- Protect from direct sunlight<br />\n<br />\nSafety information:<br />\n- Read the label carefully before use<br />\n- Do not exceed the recommended dose<br />\n- Keep out of the reach and sight of children</p>\n",
                                "main_image": [
                                    {
                                        "id": 150,
                                        "product_id": 7,
                                        "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-ff060648-75ab-4ac8-b11d-2418a1867d05.jpg",
                                        "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ff060648-75ab-4ac8-b11d-2418a1867d05.jpg"
                                    }
                                ]
                            }
                        ]
                    }
                }
            ]
        }
    ]
}
````

