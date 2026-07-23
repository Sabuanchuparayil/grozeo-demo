# Cart Details

---
Cart Details

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| GET | `/cart/{order_method}` | Yes |

order_method 
I need Delivery -1
I can Collect -2

### Response

```json
{
    "status": "ok",
    "data": {
        "cart": [
            {
                "id": 981,
                "cart_customer_id": 82,
                "cart_group_id": 6,
                "cart_product_id": 1,
                "cart_branch_id": 10,
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
                "order_method": 1,
                "item": {
                    "fsi_uid": 6,
                    "item_group_id": 6,
                    "item_name": "Inhailer",
                    "brand_name": "Vicks",
                    "category_id": 1,
                    "category_name": "Nasal Congestion",
                    "variant": "Inhaller",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 1,
                            "stit_fsiuid": 6,
                            "quantity": "50gm",
                            "itemId": 1,
                            "short_description": "The Vicks InhalerÂ opens up the blocked stuffed nose due to cold, and nasal allergies.",
                            "long_description": "<p><strong>The Vicks Inhaler</strong>&nbsp;opens up the blocked stuffed nose due to cold, and nasal allergies.<br />\n<br />\n<strong>Key Ingredients:</strong></p>\n\n<ul>\n\t<li>Kapoor</li>\n</ul>\n\n<ul>\n\t<li>Pudinah Ke Phool</li>\n</ul>\n\n<ul>\n\t<li>Wintergreen oil</li>\n</ul>\n\n<p><br />\n<strong>Key Benefits:</strong></p>\n\n<ul>\n\t<li>Economical, compact, and effective product</li>\n</ul>\n\n<ul>\n\t<li>Gives fast and temporary mobile relief</li>\n</ul>\n\n<ul>\n\t<li>Opens up clogged nose due to cold, hay fever, and upper respiratory tract allergy</li>\n</ul>\n\n<p><br />\n<strong>Directions For Use:</strong></p>\n\n<ul>\n\t<li>Inhale medicated vapors through a nostril while holding the other nostril closed</li>\n</ul>\n\n<ul>\n\t<li>Inhale deeply to make breathing free and cool</li>\n</ul>\n\n<p><br />\n<strong>Safety Information:</strong></p>\n\n<ul>\n\t<li>Read the label carefully before use</li>\n</ul>\n\n<ul>\n\t<li>Do not exceed the recommended dose</li>\n</ul>\n\n<ul>\n\t<li>Keep out of reach and sight of children</li>\n</ul>\n\n<ul>\n\t<li>Store the formulation in cool and dry place</li>\n</ul>\n",
                            "stit_displaylabel": "",
                            "prescription": null,
                            "main_image": [
                                {
                                    "id": 133,
                                    "product_id": 1,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg"
                                }
                            ],
                            "stock_available": 2,
                            "selling_prize": 99.5,
                            "godown_itemId": 45,
                            "mrp": 100,
                            "default_value": 1
                        }
                    ]
                }
            },
            {
                "id": 984,
                "cart_customer_id": 82,
                "cart_group_id": 76,
                "cart_product_id": 78,
                "cart_branch_id": 10,
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
                "order_method": 1,
                "item": {
                    "fsi_uid": 76,
                    "item_group_id": 76,
                    "item_name": "Arjuna Tea",
                    "brand_name": "Jiva",
                    "category_id": 116,
                    "category_name": "Cardiac Care",
                    "variant": "Granules",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 78,
                            "stit_fsiuid": 76,
                            "quantity": "300gm",
                            "itemId": 78,
                            "short_description": "Arjuna helps to balance the three â€œhumorsâ€\u009d: kapha, pitta, and vata\nIt may also provide aid in the case of asthma, bile duct disorders, scorpion stings, and poisonings\nArjuna acts as a digestive tonic and defends against stomach acid attacks which can lead to heartburn",
                            "long_description": "<p><strong>Arjuna Tea</strong>&nbsp;is formulated to facilitate healthy heart, liver, kidney, and digestive functions. The product contains Arjuna which contains antibacterial, anti-inflammatory, and antioxidant properties.<br />\n<br />\n<strong>Key Ingredients:</strong><br />\nArjuna<br />\n<br />\n<strong>Key Benefits:</strong></p>\n\n<ul>\n\t<li>Arjuna helps to balance the three &ldquo;humors&rdquo;: kapha, pitta, and vata</li>\n</ul>\n\n<ul>\n\t<li>It may also provide aid in the case of asthma, bile duct disorders, scorpion stings, and poisonings</li>\n</ul>\n\n<ul>\n\t<li>Arjuna acts as a digestive tonic and defends against stomach acid attacks which can lead to heartburn</li>\n</ul>\n\n<ul>\n\t<li>Contains antibacterial qualities which can provide comfort in ulcers, gastritis and other stomach infections</li>\n</ul>\n\n<ul>\n\t<li>Protects the liver and kidneys by increasing antioxidative activities</li>\n</ul>\n\n<ul>\n\t<li>Assists in achieving healthy arterial blood flow and healthy muscular contractions</li>\n</ul>\n\n<ul>\n\t<li>Helps to enhance the cardiovascular endurance</li>\n</ul>\n\n<p><br />\n<strong>Directions For Use:</strong><br />\nAs directed by the physician.<br />\n<br />\n<strong>Safety Information:</strong></p>\n\n<ul>\n\t<li>Read the label carefully before use</li>\n</ul>\n\n<ul>\n\t<li>Store in a cool dry place away from direct sunlight</li>\n</ul>\n\n<ul>\n\t<li>Keep out of reach of the children</li>\n</ul>\n\n<ul>\n\t<li>Use under medical supervision</li>\n</ul>\n",
                            "stit_displaylabel": "",
                            "prescription": null,
                            "main_image": [
                                {
                                    "id": 68,
                                    "product_id": 78,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-665ab74a-2b30-4900-9374-7ef877c0711c.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-665ab74a-2b30-4900-9374-7ef877c0711c.jpg"
                                }
                            ],
                            "stock_available": 0,
                            "selling_prize": 99.5,
                            "godown_itemId": 74,
                            "mrp": 100,
                            "default_value": 1
                        }
                    ]
                }
            }
        ],
        "price_details": [
            {
                "label": "Basket Value",
                "value": "₹ 149.3",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 1
            },
            {
                "label": "Amount Before Tax",
                "value": "",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 2
            },
            {
                "label": "Gst",
                "value": "₹ 19.91",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 3
            },
            {
                "label": "KFC",
                "value": "",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 4
            },
            {
                "label": "Delivery",
                "value": "₹ 0",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 5
            },
            {
                "label": "Round Off",
                "value": "",
                "color_code": "#858383",
                "is_bold": false,
                "is_italics": false,
                "order": 6
            },
            {
                "label": "Total",
                "value": "₹ 169.21",
                "color_code": "#000000",
                "is_bold": true,
                "is_italics": false,
                "order": 7
            }
        ],
        "wishlist": [
            {
                "id": 593,
                "customer_id": 82,
                "group_id": 6,
                "product_id": 1,
                "branch_id": 10,
                "order_method": 1,
                "item": {
                    "fsi_uid": 6,
                    "item_group_id": 6,
                    "item_name": "Inhailer",
                    "brand_name": "Vicks",
                    "category_id": 1,
                    "category_name": "Nasal Congestion",
                    "variant": "Inhaller",
                    "item_master": [
                        {
                            "stit_ID": 1,
                            "stit_fsiuid": 6,
                            "quantity": "50gm",
                            "itemId": 1,
                            "short_description": "The Vicks InhalerÂ opens up the blocked stuffed nose due to cold, and nasal allergies.",
                            "long_description": "<p><strong>The Vicks Inhaler</strong>&nbsp;opens up the blocked stuffed nose due to cold, and nasal allergies.<br />\n<br />\n<strong>Key Ingredients:</strong></p>\n\n<ul>\n\t<li>Kapoor</li>\n</ul>\n\n<ul>\n\t<li>Pudinah Ke Phool</li>\n</ul>\n\n<ul>\n\t<li>Wintergreen oil</li>\n</ul>\n\n<p><br />\n<strong>Key Benefits:</strong></p>\n\n<ul>\n\t<li>Economical, compact, and effective product</li>\n</ul>\n\n<ul>\n\t<li>Gives fast and temporary mobile relief</li>\n</ul>\n\n<ul>\n\t<li>Opens up clogged nose due to cold, hay fever, and upper respiratory tract allergy</li>\n</ul>\n\n<p><br />\n<strong>Directions For Use:</strong></p>\n\n<ul>\n\t<li>Inhale medicated vapors through a nostril while holding the other nostril closed</li>\n</ul>\n\n<ul>\n\t<li>Inhale deeply to make breathing free and cool</li>\n</ul>\n\n<p><br />\n<strong>Safety Information:</strong></p>\n\n<ul>\n\t<li>Read the label carefully before use</li>\n</ul>\n\n<ul>\n\t<li>Do not exceed the recommended dose</li>\n</ul>\n\n<ul>\n\t<li>Keep out of reach and sight of children</li>\n</ul>\n\n<ul>\n\t<li>Store the formulation in cool and dry place</li>\n</ul>\n",
                            "main_image": [
                                {
                                    "id": 133,
                                    "product_id": 1,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg"
                                }
                            ],
                            "stock_available": 2,
                            "selling_prize": 99.5,
                            "mrp": 100,
                            "godown_itemId": 80,
                            "default_value": 1
                        }
                    ]
                }
            },
            {
                "id": 597,
                "customer_id": 82,
                "group_id": 7,
                "product_id": 3,
                "branch_id": 10,
                "order_method": 1,
                "item": {
                    "fsi_uid": 7,
                    "item_group_id": 7,
                    "item_name": "Stobal Cough Syrup",
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
                            "main_image": [
                                {
                                    "id": 140,
                                    "product_id": 3,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg"
                                }
                            ],
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "godown_itemId": 23,
                            "default_value": 0
                        }
                    ]
                }
            }
        ],
        "approval": 1
    }
}
```
