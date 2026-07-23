# Edit My Medicine

---

 Edit My Medicine

### Details

| Method | Uri               | Authorization |
| :----- | :---------------- | :------------ |
| PUT   | `api/medicinereminder/updatereminder` | YES            |

### Request

```json


{
"id":11,
"puporse":"syrup",
"interval":10,
"notification_status":1,
"notification_interwell":4,
"item":[
{
	"stit_ID":10,
	"quantity":100
	
},	
{
	"stit_ID":12,
	"quantity":200
	
},
{
	"stit_ID":13,
	"quantity":300
	
}
],
	
	
	
"branch_id":10
}




```


### Response 

```json
{
    "status": "ok",
    "data": [
        {
            "id": 11,
            "customer_id": 82,
            "purpose": "syrup",
            "interval": "10",
            "notification_status": 1,
            "notification_interwell": "4",
            "created_at": "2020-05-20 11:19:28",
            "items": [
                {
                    "item_id": 11,
                    "stit_ID": 10,
                    "ismedicine": 0,
                    "qty": 100
                },
                {
                    "item_id": 11,
                    "stit_ID": 12,
                    "ismedicine": 0,
                    "qty": 200
                },
                {
                    "item_id": 11,
                    "stit_ID": 13,
                    "ismedicine": 0,
                    "qty": 300
                }
            ],
            "item_details": [
                {
                    "fsi_item_id": 3,
                    "fsi_uid": 42,
                    "item_group_id": 42,
                    "item_name": "SBL Stobal Cough Syrup",
                    "brand_name": "SBL",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "syrup",
                    "isMedicine": 0,
                    "fsi_displaylabel": "",
                    "fsi_def_itemmaster_id": 10,
                    "item_master": [
                        {
                            "stit_ID": 8,
                            "stit_fsiuid": 42,
                            "quantity": "180ml",
                            "itemId": 8,
                            "short_description": "SBLâ€™s Stodal Or Stobal cough syrup with no narcotics helps in facilitating recovery from cough and wheezes and thus, often helps in avoiding usage of antibiotics. Stodal+cough syrup is a safe and effective remedy, and is well suited for all age groups. It has no narcotics. Safe for pregnant women, children and elderly persons.",
                            "long_description": "<p><strong>SBL&rsquo;s Stodal&nbsp;Or Stobal cough syrup</strong>&nbsp;with no narcotics helps in facilitating recovery from cough and wheezes and thus, often helps in avoiding usage of antibiotics. Stodal+cough syrup is a safe and effective remedy, and is well suited for all age groups. It has no narcotics. Safe for pregnant women, children and elderly persons.<br />\n<br />\nINGREDIENTS: SBL&rsquo;s stodal&nbsp;Or Stobal&nbsp;cough syrup is a clinically proven product and contains well balanced homeopathic ingredients like Pulsatilla nigricans 3, Justicia adhatoda 3x, Rumex crispus 3x, Ipecacuanha 3 etc.<br />\n<br />\nDOSAGE: Adults: one tablespoon of SBL Stodal Cough Syrup 3-5 time a day. Children: one teaspoon 3-5 times a day or as directed by the physician.</p>\n",
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 157,
                                    "product_id": 8,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-7f6204b3-521f-4a1e-a250-ad3fb7178e35.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7f6204b3-521f-4a1e-a250-ad3fb7178e35.jpg"
                                }
                            ],
                            "additional_image": [],
                            "selling_price": 0,
                            "godown_itemId": 695,
                            "default_value": 0
                        },
                        {
                            "stit_ID": 9,
                            "stit_fsiuid": 42,
                            "quantity": "60ml",
                            "itemId": 9,
                            "short_description": "SBLâ€™s Stodal Or Stobal cough syrup with no narcotics helps in facilitating recovery from cough and wheezes and thus, often helps in avoiding usage of antibiotics. Stodal+cough syrup is a safe and effective remedy, and is well suited for all age groups. It has no narcotics. Safe for pregnant women, children and elderly persons.",
                            "long_description": "<p><strong>SBL&rsquo;s Stodal&nbsp;Or Stobal cough syrup</strong>&nbsp;with no narcotics helps in facilitating recovery from cough and wheezes and thus, often helps in avoiding usage of antibiotics. Stodal+cough syrup is a safe and effective remedy, and is well suited for all age groups. It has no narcotics. Safe for pregnant women, children and elderly persons.<br />\n<br />\nINGREDIENTS: SBL&rsquo;s stodal&nbsp;Or Stobal&nbsp;cough syrup is a clinically proven product and contains well balanced homeopathic ingredients like Pulsatilla nigricans 3, Justicia adhatoda 3x, Rumex crispus 3x, Ipecacuanha 3 etc.<br />\n<br />\nDOSAGE: Adults: one tablespoon of SBL Stodal Cough Syrup 3-5 time a day. Children: one teaspoon 3-5 times a day or as directed by the physician.</p>\n",
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 154,
                                    "product_id": 9,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-fb963957-f032-47c0-872b-368a4134e63e.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-fb963957-f032-47c0-872b-368a4134e63e.jpg"
                                }
                            ],
                            "additional_image": [
                                {
                                    "id": 153,
                                    "product_id": 9,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-44e26a4e-7da5-4fc3-94ec-353732e8fa0a.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-44e26a4e-7da5-4fc3-94ec-353732e8fa0a.jpg"
                                }
                            ],
                            "selling_price": 0,
                            "godown_itemId": 354,
                            "default_value": 0
                        },
                        {
                            "stit_ID": 10,
                            "stit_fsiuid": 42,
                            "quantity": "115ml",
                            "itemId": 10,
                            "short_description": "SBLâ€™s Stodal Or Stobal cough syrup with no narcotics helps in facilitating recovery from cough and wheezes and thus, often helps in avoiding usage of antibiotics. Stodal+cough syrup is a safe and effective remedy, and is well suited for all age groups. It has no narcotics. Safe for pregnant women, children and elderly persons.",
                            "long_description": "<p><strong>SBL&rsquo;s Stodal&nbsp;Or Stobal cough syrup</strong>&nbsp;with no narcotics helps in facilitating recovery from cough and wheezes and thus, often helps in avoiding usage of antibiotics. Stodal+cough syrup is a safe and effective remedy, and is well suited for all age groups. It has no narcotics. Safe for pregnant women, children and elderly persons.<br />\n<br />\nINGREDIENTS: SBL&rsquo;s stodal&nbsp;Or Stobal&nbsp;cough syrup is a clinically proven product and contains well balanced homeopathic ingredients like Pulsatilla nigricans 3, Justicia adhatoda 3x, Rumex crispus 3x, Ipecacuanha 3 etc.<br />\n<br />\nDOSAGE: Adults: one tablespoon of SBL Stodal Cough Syrup 3-5 time a day. Children: one teaspoon 3-5 times a day or as directed by the physician.</p>\n",
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 155,
                                    "product_id": 10,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-3e8d1177-8cbb-4685-b139-e90837383aa0.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-3e8d1177-8cbb-4685-b139-e90837383aa0.jpg"
                                }
                            ],
                            "additional_image": [
                                {
                                    "id": 156,
                                    "product_id": 10,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-3573700a-b61b-410f-a2c8-3ff185ba7c15.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-3573700a-b61b-410f-a2c8-3ff185ba7c15.jpg"
                                }
                            ],
                            "selling_price": 0,
                            "godown_itemId": 459,
                            "default_value": 0
                        }
                    ]
                },
                {
                    "fsi_item_id": 7,
                    "fsi_uid": 12,
                    "item_group_id": 12,
                    "item_name": "SBL Bronchoherb Cough Syrup",
                    "brand_name": "SBL",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "syrup",
                    "isMedicine": 0,
                    "fsi_displaylabel": "",
                    "fsi_def_itemmaster_id": 11,
                    "item_master": [
                        {
                            "stit_ID": 11,
                            "stit_fsiuid": 12,
                            "quantity": "100ml",
                            "itemId": 11,
                            "short_description": "BRONCHOHERB COUGH SYRUP\nCough is a violent exhalation by which irritant particles in the airways can be expelled. It occurs due to stimulation of receptors in the throat, respiratory passages. Most cough syrups in market contain narcotic substances, which can lead to addiction as well as reduce mental activity causing sleepiness. These cough syrups should not be given to children, pregnant and lactating females.",
                            "long_description": "",
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 131,
                                    "product_id": 11,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg"
                                }
                            ],
                            "additional_image": [
                                {
                                    "id": 132,
                                    "product_id": 11,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-4ff9c83a-10e4-4140-ab22-a1b2591db461.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-4ff9c83a-10e4-4140-ab22-a1b2591db461.jpg"
                                }
                            ],
                            "selling_price": 0,
                            "godown_itemId": 297,
                            "default_value": 0
                        },
                        {
                            "stit_ID": 12,
                            "stit_fsiuid": 12,
                            "quantity": "180ml",
                            "itemId": 12,
                            "short_description": "BRONCHOHERB COUGH SYRUP\nCough is a violent exhalation by which irritant particles in the airways can be expelled. It occurs due to stimulation of receptors in the throat, respiratory passages. Most cough syrups in market contain narcotic substances, which can lead to addiction as well as reduce mental activity causing sleepiness. These cough syrups should not be given to children, pregnant and lactating females.",
                            "long_description": "<p>Information about SBL Bronchoherb Cough Syrup</p>\n\n<p>BRONCHOHERB COUGH SYRUP<br />\nCough is a violent exhalation by which irritant particles in the airways can be expelled. It occurs due to stimulation of receptors in the throat, respiratory passages. Most cough syrups in market contain narcotic substances, which can lead to addiction as well as reduce mental activity causing sleepiness. These cough syrups should not be given to children, pregnant and lactating females. &nbsp; Bronchoherb syrup is clinically proven Herbal Formulation which treats irritating cough caused by irritation, smoking, nasal and post nasal mucus discharge. &nbsp; COMPOSITION<br />\nGarcinia pedunculata (Amalvet, fruit), Cephaelis ipecacuanha (Epikak, root), Styrax benzoin dry (Lobaan, Exudate) (BPN) Each 95mg, Adhatoda vasica (Vasa, leaf), Glycyrrhiza glabra (Yastimadhu, root) (API), Each 95mg, Excipients Q.S.<br />\n<br />\nINDICATION Cough associated with laryngitis, tracheitis, bronchitis, emphysema &amp; bronchiectasis<br />\n<br />\nDOSAGE Adult &ndash; One tablespoonful 3-5 times a day Children &ndash; One teaspoonful 3-5 times a day<br />\n<br />\nUse under medical supervision.</p>\n",
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 128,
                                    "product_id": 12,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg"
                                }
                            ],
                            "additional_image": [
                                {
                                    "id": 129,
                                    "product_id": 12,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-7f0e3b33-33cf-4492-9125-7dbb8b61d012.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7f0e3b33-33cf-4492-9125-7dbb8b61d012.jpg"
                                },
                                {
                                    "id": 130,
                                    "product_id": 12,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-f5d42d68-516d-45c6-875f-3b78a2fe90e0.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-f5d42d68-516d-45c6-875f-3b78a2fe90e0.jpg"
                                }
                            ],
                            "selling_price": 0,
                            "godown_itemId": 808,
                            "default_value": 0
                        }
                    ]
                },
                {
                    "fsi_item_id": 8,
                    "fsi_uid": 13,
                    "item_group_id": 13,
                    "item_name": "Himalaya Koflet Lozenges",
                    "brand_name": "Himalaya",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "packet",
                    "isMedicine": 0,
                    "fsi_displaylabel": "",
                    "fsi_def_itemmaster_id": 13,
                    "item_master": [
                        {
                            "stit_ID": 13,
                            "stit_fsiuid": 13,
                            "quantity": "10 lozenges",
                            "itemId": 13,
                            "short_description": "Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx.",
                            "long_description": "<p>Information about Himalaya Koflet Lozenges</p>\n\n<p>Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx. In addition, the anti-allergic, antimicrobial and immune-resistance building properties provide relief from cough of varied etiology. It contains Trikatu, Yashtimadhu and Clove.<br />\n&nbsp;<br />\nRole of Key ingredients:</p>\n\n<ul>\n\t<li>Trikatu a polyherbal composition containing Long Pepper, Black Pepper and Ginger, treats chest congestion by reducing mucus. It also works as an anti microbial, thereby helping in managing upper respiratory infections.</li>\n\t<li>Lavanga (Clove) is useful in coughs due to its antitussive activity and other upper respiratory disorders due to its antimicrobial activity. It has been used in Ayurveda as a popular remedy for sore throat.</li>\n\t<li>Licorice (Yashtimadhu) has antitussive, expectorant and immune-enhancing properties that are helpful in relieving cough.</li>\n</ul>\n\n<p><br />\n&nbsp;<br />\nDirections for use:<br />\nOne lozenge three-four times a day, or as directed by the physician.</p>\n",
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 263,
                                    "product_id": 13,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
                                }
                            ],
                            "additional_image": [
                                {
                                    "id": 264,
                                    "product_id": 13,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-cb2f47af-cbf8-44ac-947d-1fc7a4dfad60.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-cb2f47af-cbf8-44ac-947d-1fc7a4dfad60.jpg"
                                }
                            ],
                            "selling_price": 0,
                            "godown_itemId": 511,
                            "default_value": 0
                        },
                        {
                            "stit_ID": 14,
                            "stit_fsiuid": 13,
                            "quantity": "200 lozenges",
                            "itemId": 14,
                            "short_description": "Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx.",
                            "long_description": "<p>Information about Himalaya Koflet Lozenges</p>\n\n<p>Koflet lozenge is beneficial in both productive and dry cough. Lozenges reduces the bronchial mucosal irritation and inflammation in the upper respiratory tract, especially in the pharynx and larynx. In addition, the anti-allergic, antimicrobial and immune-resistance building properties provide relief from cough of varied etiology. It contains Trikatu, Yashtimadhu and Clove.<br />\n&nbsp;<br />\nRole of Key ingredients:</p>\n\n<ul>\n\t<li>Trikatu a polyherbal composition containing Long Pepper, Black Pepper and Ginger, treats chest congestion by reducing mucus. It also works as an anti microbial, thereby helping in managing upper respiratory infections.</li>\n\t<li>Lavanga (Clove) is useful in coughs due to its antitussive activity and other upper respiratory disorders due to its antimicrobial activity. It has been used in Ayurveda as a popular remedy for sore throat.</li>\n\t<li>Licorice (Yashtimadhu) has antitussive, expectorant and immune-enhancing properties that are helpful in relieving cough.</li>\n</ul>\n\n<p><br />\n&nbsp;<br />\nDirections for use:<br />\nOne lozenge three-four times a day, or as directed by the physician.</p>\n",
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 261,
                                    "product_id": 14,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg"
                                }
                            ],
                            "additional_image": [
                                {
                                    "id": 262,
                                    "product_id": 14,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-d6222d6f-9dd1-4fcd-b154-e0dc3ce7b179.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-d6222d6f-9dd1-4fcd-b154-e0dc3ce7b179.jpg"
                                }
                            ],
                            "selling_price": 0,
                            "godown_itemId": 968,
                            "default_value": 0
                        }
                    ]
                }
            ],
            "item_count": 3
        }
    ]
}
```


