# GET Mymedicine list

---

 GET Mymedicine list

### Details

| Method | Uri               | Authorization |
| :----- | :---------------- | :------------ |
| GET   | `api/medicinereminder/mymedicinereminder/{branch_id}` | YES            |




### Response 

```json
{
    "status": "ok",
    "data": [
        {
            "id": 8,
            "customer_id": 82,
            "purpose": "HIgh blood pressure",
            "interval": "10",
            "created_at": "2020-05-12 09:12:22",
            "items": [
                {
                    "item_id": 8,
                    "stit_ID": 7,
                    "ismedicine": 0
                },
                {
                    "item_id": 8,
                    "stit_ID": 1,
                    "ismedicine": 0
                }
            ],
            "item_details": [
                {
                    "fsi_item_id": 5,
                    "fsi_uid": 10,
                    "item_group_id": 10,
                    "item_name": "Vicks Cough Drops",
                    "brand_name": "Vicks",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "",
                    "isMedicine": 0,
                    "fsi_displaylabel": "",
                    "fsi_def_itemmaster_id": 7,
                    "item_master": [
                        {
                            "stit_ID": 7,
                            "stit_fsiuid": 10,
                            "quantity": "190 lozenges",
                            "itemId": 7,
                            "short_description": "Vicks Cough Drops contain Karpoor, Pudinah Ke Phool, Nilgiri Tel, Ajwain Ke Phool, and Flavoured Sugar as active ingredients. It acts as a cough suppressant and comes in ginger, honey and menthol flavors",
                            "long_description": "<p>Vicks Cough Drops contain Karpoor, Pudinah Ke Phool, Nilgiri Tel, Ajwain Ke Phool, and Flavoured Sugar as active ingredients. It acts as a cough suppressant and comes in ginger, honey and menthol flavors.<br />\n<br />\nKey benefits/uses of Vicks Cough Drops:<br />\n- Works as anesthetic and cough suppressant<br />\n- Relieves the tingling sensation even before cough starts<br />\n- Menthol: Provides a cooling sensation when applied to the skin or other tissues, treats minor sore throat pain, or mouth irritation caused by a canker sore<br />\n- Mint: Opens the air code slightly so that the sufferer of respiratory problems like bronchitis, asthma, pneumonia feel relief after taking it<br />\n- Ajwain: Possesses anesthetic property<br />\n<br />\nDirection for use/Dosage:<br />\n- As described by the doctor<br />\n<br />\nRecommendation:<br />\n- Children above 2 years of age<br />\n<br />\nIndications:<br />\n- Cough<br />\n<br />\nStorage instructions:<br />\n- Store in a cool, dry, &amp; dark place<br />\n- Protect from direct sunlight<br />\n<br />\nSafety information:<br />\n- Read the label carefully before use<br />\n- Do not exceed the recommended dose<br />\n- Keep out of the reach and sight of children</p>\n",
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 150,
                                    "product_id": 7,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-ff060648-75ab-4ac8-b11d-2418a1867d05.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ff060648-75ab-4ac8-b11d-2418a1867d05.jpg"
                                }
                            ],
                            "additional_image": [
                                {
                                    "id": 149,
                                    "product_id": 7,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-67537a54-0901-48ce-88c4-7f2ced389873.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-67537a54-0901-48ce-88c4-7f2ced389873.jpg"
                                },
                                {
                                    "id": 151,
                                    "product_id": 7,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-4e2f8023-9be3-4668-8107-e70bdeb9cc90.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-4e2f8023-9be3-4668-8107-e70bdeb9cc90.jpg"
                                },
                                {
                                    "id": 152,
                                    "product_id": 7,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0a6c958e-e66e-489b-a283-4e4306bcde7d.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0a6c958e-e66e-489b-a283-4e4306bcde7d.jpg"
                                }
                            ],
                            "selling_price": 0,
                            "godown_itemId": 486,
                            "default_value": 0
                        }
                    ]
                },
                {
                    "fsi_item_id": 2,
                    "fsi_uid": 6,
                    "item_group_id": 6,
                    "item_name": "Vicks Inhailer",
                    "brand_name": "Vicks",
                    "category_id": 1,
                    "category_name": "Nasal Congestion",
                    "variant": "Inhaller",
                    "isMedicine": 0,
                    "fsi_displaylabel": "",
                    "fsi_def_itemmaster_id": 1,
                    "item_master": [
                        {
                            "stit_ID": 1,
                            "stit_fsiuid": 6,
                            "quantity": "50gm",
                            "itemId": 1,
                            "short_description": "The Vicks InhalerÂ opens up the blocked stuffed nose due to cold, and nasal allergies.",
                            "long_description": "<p><strong>The Vicks Inhaler</strong>&nbsp;opens up the blocked stuffed nose due to cold, and nasal allergies.<br />\n<br />\n<strong>Key Ingredients:</strong></p>\n\n<ul>\n\t<li>Kapoor</li>\n</ul>\n\n<ul>\n\t<li>Pudinah Ke Phool</li>\n</ul>\n\n<ul>\n\t<li>Wintergreen oil</li>\n</ul>\n\n<p><br />\n<strong>Key Benefits:</strong></p>\n\n<ul>\n\t<li>Economical, compact, and effective product</li>\n</ul>\n\n<ul>\n\t<li>Gives fast and temporary mobile relief</li>\n</ul>\n\n<ul>\n\t<li>Opens up clogged nose due to cold, hay fever, and upper respiratory tract allergy</li>\n</ul>\n\n<p><br />\n<strong>Directions For Use:</strong></p>\n\n<ul>\n\t<li>Inhale medicated vapors through a nostril while holding the other nostril closed</li>\n</ul>\n\n<ul>\n\t<li>Inhale deeply to make breathing free and cool</li>\n</ul>\n\n<p><br />\n<strong>Safety Information:</strong></p>\n\n<ul>\n\t<li>Read the label carefully before use</li>\n</ul>\n\n<ul>\n\t<li>Do not exceed the recommended dose</li>\n</ul>\n\n<ul>\n\t<li>Keep out of reach and sight of children</li>\n</ul>\n\n<ul>\n\t<li>Store the formulation in cool and dry place</li>\n</ul>\n",
                            "stock_available": 2,
                            "selling_prize": 99.5,
                            "mrp": 100,
                            "main_image": [
                                {
                                    "id": 133,
                                    "product_id": 1,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg"
                                }
                            ],
                            "additional_image": [
                                {
                                    "id": 134,
                                    "product_id": 1,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-c341f67a-80f4-4057-ad11-1b4d8361645e.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-c341f67a-80f4-4057-ad11-1b4d8361645e.jpg"
                                },
                                {
                                    "id": 135,
                                    "product_id": 1,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-09ca5314-9665-4745-9f0e-8972fc285b50.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-09ca5314-9665-4745-9f0e-8972fc285b50.jpg"
                                }
                            ],
                            "selling_price": 99.5,
                            "godown_itemId": 361,
                            "default_value": 1
                        }
                    ]
                }
            ],
            "item_count": 2
        }
    ]
}
```


