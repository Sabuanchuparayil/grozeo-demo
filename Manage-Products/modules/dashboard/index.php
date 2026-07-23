<?php

/*
 * {
  "type": "service_account",
  "project_id": "apt-footing-286705",
  "private_key_id": "c99d8ee25af748a3831ce0fe00b767c1e8b8c723",
  "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCo1kkSKpsWWKjQ\n3LgK1XMPNh0Iwp+z5iZz5dDWIwFrv44l0LoznhxBnTEKrKt74xh26jB+NRkxu6WP\nV/D0AGuKF27ePheUQRnn6u9sj/xPsCA0bN1/w+C/3wV0sIUoQ54THMFaXt/0VlDt\n7cq5Y2U1s4wqgYb9c5exbVvZmcKgGRqtwOOgZ2vg2qgdUhTW8buQwHJBPbEBdzTD\nYyIjXS22v73ljN843T/GBoCuerEia24XD/+kuuimRVDdKwulV45nZ90TEgK05yMQ\nQULCLSMkvBlRFVaORsWK4UhfMQl/jmxe76ABTSQYqQttYrFFj3vnVECoN3J7pKnQ\ndqCzNqKVAgMBAAECggEAGRySDrEmkvrJ1RLXDslzjRIj3F8yKKw1EOzgFFFtjyyV\nBFvYrf+4vSuXlTpcYRdEVr97SfI01phNxhWy4V/EnuvgUg7TK/pI0502UiPSR7nu\n+E0u3qdwIKmXoqT0kSoJc1dGDN5qmelSrSi+i7dUYNndIZ9VSBIuwDV/QUykzXHg\nlS5W3YWOsVVgKiW4mWLBzqdT+c/mrboqqLDSrHRnA8q/ZdXxFDYnYR7SJUJVJ+yl\nOYmE46kopWY14i+HMdu9NQ5N7UYdFRVfV8YSi4qIIFWTlXLfQN4igvZaoLxkEcN6\n253Z4rj4I35I+shZCzDVWzBJPyWP+wUnMTJ1BhRhGwKBgQDQ/Qm4BQ6w/SQlt7Aq\n36KgbC/aHh6pPabo0u1Iu1KvJtnYBfFIzqtvVvoVvsb5h65IqZ7l5H9eiGnFJIq1\nXbQ5HlMGY31c52Ed5YERhZYw9oQL71NKT1xDwPl7P+HTPaLYVEYE/mE7A3CX8orq\n0PwT8Bxspu+daLYIRoi2ZTlziwKBgQDO0Q+Ese9X8itDIORGQH66quJVvYMop/c1\nXq/vCjofludFkv0/0jrcBZIJoRjVIW9CSTE3qO+XcZM8ZWaWZ384N7HkhkSFyGqh\nMUeEl8lQd//7jGxzcMQ0ko82mxmqoByYYrsBOcNFm+MEjkGAh1twKMJ/zp++rrVf\nLP5I1n6GXwKBgQDF0I1vepICY0ngzbLZkh7r/Bt7OQFKrombXXOuYWkNUxfmCxHt\nGXpaBBjplk/eh1gnfS5jalpJT5PWQBVvQIfDfPmXVXqnAngBhWga/rhAFka8yUas\nUtwBYdqDl98YRQIsA/DW7zV3V3UuzixBwZipAqDVE1pBQ4jx4lmrU7sCqwKBgQCZ\nu/XZIZtUuINIJw8I2bBaeeQ8796rPhAY8AW/ns6N5NKNeTIfWtq96rYfykx3QZ8+\nmsGnKkDPRnG4F3gmnVCILX5i7RvKhqwcnlEXUu0mj80M49lKBq0Sl081vB1cJCCd\ndeakhpeNCs+59zThobxqpyHNd35vc9cGpJ9w7WCn6QKBgQCvcSscq8u77KSe8u2b\nsKMikHX70fEDYXegg0I56Nv8GFpvJzdvba28K+DOWTbc7rZpG/W8MLhpveL3OJq6\nNwdjI1VyvCcYbA2ncdLjM2Xd+Sk/fAO1IG2DzSuOq1y/T97Otm6hXmV669befV18\nO2dIDMw2vPs0d0vezA8ylwtrFw==\n-----END PRIVATE KEY-----\n",
  "client_email": "dhanya-velosit@apt-footing-286705.iam.gserviceaccount.com",
  "client_id": "102969462092613169713",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/dhanya-velosit%40apt-footing-286705.iam.gserviceaccount.com"
  }
  function generateToken(event, context, callback) {
  var {google} = require("googleapis");
  console.log('generate token from google api');
  var googleServiceAccountKey = {
  "type": "service_account",
  "project_id": "asianet-in",
  "private_key_id": "0d5ae9652888e337fa9fa752eb3574b4dc4584d2",
  "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCoE2/Vo6ZY5fXh\npum4VG5x+iCjVlFyhZtrZG3/jkrHIUqalGZkbm1uEldZgemJMMnIDhWOOIq5giXo\nBBZRGWPowrmbFMJbFx7Hj/eq84Xg4XJgcIFJEMlWZ3CL2AY3EB+h/RoJFznBZPy1\nCpMoj1jWV16298O9rJMIoSFjR4O3aeEiE4rnVt+aqyiQ4W5SdM/y2scoH+lJIUpZ\nbVuxyjwjWfOHRQsS3sresvgHDAq3MVxyk3aBHNYzinyFeV3osFhtT6A/R4SOSond\n6heYs//0d7piu8FRyymZ500tkGyBi1YdQPBjF+kdeEw8IeBy1Vxvq3LfvDx90jsC\nlPYV6hD5AgMBAAECggEAGSBAu3YZv9fu3FM0xODgWusa1ngouF9XVuReWlImBSNJ\nsRamwPROKSKWStP0OtVfkOfkGo0C1g0qCAeWY3zRdgIoN5IQpQftisVPr890b+Qc\nEmU3OppHEwLnLQy4DyK6q993tSyy7mGfspvYWHunevF4QA+FhFUobOWTpXETNIei\nQlAfcT01TO8yCd9bTkYk38KkSSm6SMeKiV1hi6atjPZoO0iVvJkUcAF7VlddNeO0\nFVe2kqKCJ6Hc2UY0e52n5c3kP0LiiadpClmCRbphacxQaISs6le77uNXsrCTb8Hr\nO9rcdSvtBbS3q/GOArFbOoX3y+mJrpip5lytsTbJNQKBgQDQvlDN1d2zBzRqF+sV\nVXphjsOLayMlbAKrWPdTPexUv0ShYww5dEI+9OrHCrlcxkngIEVzg6xwXRT6Ze3+\nphV4+Fb5wb1Oq6DllZiZxG5MGljGQoQ9d4eJSAzw2oF9QeJvhceTXCcSOs9JUeE/\nie605JpujBx3swcIvwXvKSm2pQKBgQDOID6S8AX12ZpSCDpkNSA8tSvKLUDtDqIy\nJch34dkGgFZToeeKpyDJ1quQUXfXV/SwO8/zZP0/1ba/kgLUrCi81gQqbBTs+cuf\n/YvyerphovR7FYILorH9wbNpddl57HxlOqOWZOm9dtX/O6q8e7ifVBxDkgUSSHZh\nEXWWmis0xQKBgBENmzhVonj/u653KcNiak8SBLOdGw/xlP4+lGX+hxIdVhQBLXx8\nHPVbuNpt69rCcEKZIFNhjHLZh980+I53LwXk8+YPh9Gnf8uBvyfAvmoFNP9ta7RB\n0ZbLhhMfJrj+6urFeRp2ytJYb5rDz60LLa8lheBGHgVBYO+7+1YgfMHpAoGAHRIp\nIRofDRR6klU8vwNLH6TBn0sQnB5zO7Ved6HvtN4GztbHzCNUGYNgQQNsbn+mL/DP\nnFlC6mze0FfsdEgvmqpoff8uWFnGoTLmOPWcMccEyhM3eyKgDdTy083eNTboOHKz\nvbNPz/vhpJSquNiOKlJ3hgQjGPgFUh1fQzVNIK0CgYBrCVDbo9IaiLn+BNMnqTjh\nDlThlPSavjRJ1ps38iTxNioheUFmWLqjrG7Pl2cGTbhxWO2aGbGOuAPX4aNv2h9w\ng9/XeensQ7DSkmx88yp/VdpWtem8WeS9tX9vkaauGgkCOqvYHSJblpOw22m5dJeQ\niURW5ZkBQXFoPY9QJUB7fA==\n-----END PRIVATE KEY-----\n",
  "client_email": "dashboardviewer@asianet-in.iam.gserviceaccount.com",
  "client_id": "103358449921332152714",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/dashboardviewer%40asianet-in.iam.gserviceaccount.com"
  };

  const googleJWTClient = new google.auth.JWT(
  googleServiceAccountKey.client_email,
  null,
  googleServiceAccountKey.private_key,
  ['https://www.googleapis.com/auth/analytics.readonly'], null);

  googleJWTClient.authorize((error, access_token) => {

  if (error) {
  console.log(error);
  done(null, error, callback);
  }
  console.log(access_token);
  done(null, access_token, callback);
  // ... access_token ready to use to fetch data and return to client

  // even serve access_token back to client for use in `gapi.analytics.auth.authorize`

  });

  }
 */

switch ($op) {
    case 'schedulerGridStore':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'prlk_name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                    }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM process_lock {$search} AND prlk_isenabled = 1  ORDER BY {$sort} {$dir}";

        $listQuery = "SELECT prlk_name,prlk_status,prlk_updtime,prlk_isenabled,prlk_email,prlk_Description,prlk_interval,TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP,prlk_updtime))/60  as minuteDiff FROM process_lock {$search} AND prlk_isenabled = 1 ORDER BY {$sort} {$dir} ";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'changeStatus':
        $prlk_name = $_POST['prlk_name'];
        $prlk['prlk_updtime'] = date('Y-m-d H:i:s');
        $prlk['prlk_status'] = 0;
        $db->query('begin');
        $status = $db->perform('process_lock', $prlk, 'update', " prlk_name = '{$prlk_name}'");
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Converted '}";
        } else {
            echo "{'success':false,'valid':false,'msg': 'Error While Converting.'}";
        }
        break;
    case 'chartCombination':
        ob_start();
        include('chartdetails.php');
        $resHtml = ob_get_contents();
        ob_end_clean();
        echo $resHtml;
        exit();
        break;
    case 'overallGridStore':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 14;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'business_type_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $search .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_productsubcategory INNER JOIN mypha_productcategory ON category_id = main_category INNER JOIN mypha_productparent_category ON mypha_productparent_category.parent_category_id = mypha_productcategory.parent_category INNER JOIN finascop_business_type ON business_type_id = parent_category_businessType  
 {$search}  ORDER BY {$sort} {$dir}";

        $listQuery = "SELECT 
    bt.business_type_id,
    bt.business_type_name AS RetailCategory,
    ppc.parent_category_id,
    ppc.parent_category AS Department,
    pc.category_id,
    pc.category_name AS category,
    psc.sub_category,
    COALESCE(si.productCount, 0) AS productCount
FROM mypha_productsubcategory psc
INNER JOIN mypha_productcategory pc ON pc.category_id = psc.main_category
INNER JOIN mypha_productparent_category ppc ON ppc.parent_category_id = pc.parent_category
INNER JOIN finascop_business_type bt ON bt.business_type_id = ppc.parent_category_businessType
LEFT JOIN (
    SELECT 
        product_category,
        COUNT(stit_ID) AS productCount
    FROM finascop_stock_itemmaster
    WHERE stit_status = 1
    GROUP BY product_category
) si ON si.product_category = psc.sub_category_id  
 {$search}  ORDER BY productCount DESC LIMIT {$start},{$limit}";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'brandWisePrdctCount':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) && $limit > 0 ? $limit : 14;
        $start = is_numeric($start) && $start > 0 ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'brand_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $today = date('Y-m-d');
        $filter_qry = " WHERE 1 = 1 ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['br_id', 'date_from', 'date_to', 'order_status', 'order_type'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }

                        break;
                }
            }
        }
        

        $date = date('dd-mm-YYYY');
        $countDataQuery = "SELECT count(*) from `mypha_productbrands` INNER JOIN `mypha_productmanufacture` ON mypha_productmanufacture.`manufacture_id` = mypha_productbrands.`manufacture_id` {$filter_qry} ";
        $listQuery = "SELECT 
    pb.brand_id,
    pb.brand_name,
    pb.manufacture_id,
    pm.manufacture_name,
    COALESCE(si.productCount, 0) AS productCount
FROM mypha_productbrands pb
INNER JOIN mypha_productmanufacture pm ON pm.manufacture_id = pb.manufacture_id
LEFT JOIN (
    SELECT 
        pdt_brand,
        COUNT(stit_ID) AS productCount
    FROM finascop_stock_itemmaster
    WHERE stit_status = 1
    GROUP BY pdt_brand
) si ON si.pdt_brand = pb.brand_id {$filter_qry} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
}
