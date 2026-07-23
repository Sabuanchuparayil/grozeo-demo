<?php
switch ($op) {
    case 'listSupportUnits':


        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'suId' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['sm_id', 'sm_name', 'sm_category', 'sm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');
if($field['field'] == 'suName')
$field['field'] = 'support_unit.name';
                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM support_unit  {$search}";
        $listQuery = "SELECT support_unit.id AS suId,support_unit.name AS suName,IF((status=1),'Active','Inactive') AS status FROM support_unit 
        " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'supportunitsdetailsView':

        $suId = isset($_POST['suId']) ? intval($_POST['suId']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($suId || $ID) {

            $data = $db->getFromDB("SELECT support_unit.id AS suId,support_unit.name AS suName,status AS status FROM support_unit  WHERE support_unit.id =" . $suId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'supportunits_form_load':

        $suId = isset($_POST['suId']) ? intval($_POST['suId']) : 0;
        if ($suId) {
            $sql = "SELECT  support_unit.id AS suId,support_unit.name AS suName,status AS comboMasterSupportUnitsStatus FROM support_unit WHERE support_unit.id =" . $suId;
            $results = $db->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;


    case 'saveSupportUnits':

        $db->query('begin');
        $data = array(
            "id" => $_POST['id'],
            "name" => $_POST['name'],
            "status" => $_POST['status']
        );
        $suId = $data['id'];
        $suName = $data['name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $suName = addslashes($suName);




        if ($data['id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;


            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from support_unit WHERE support_unit.name ='{$suName}' AND support_unit.id!='{$suId}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Support Unit already exists.'}";
                exit;
            } else {
                $status = $db->perform("support_unit", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from support_unit WHERE support_unit.name ='{$suName}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Support Unit already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('support_unit', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT support_unit.id AS suId,support_unit.name AS suName,status FROM support_unit WHERE support_unit.id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listSupportChapter':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'scId' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['sm_id', 'sm_name', 'sm_category', 'sm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM support_chapter  {$search}";
        $listQuery = "SELECT * FROM (SELECT support_chapter.id AS scId,support_chapter.name AS scName,IF((status=1),'Active','Inactive') AS status,support_chapter.unitId AS scUnitId,(SELECT name FROM support_unit WHERE id = support_chapter.unitId) as scUnitName FROM support_chapter ) AS listChapter 
        " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'SupportChapterdetailsView':
        $scId = isset($_POST['scId']) ? intval($_POST['scId']) : 0;
        if ($scId) {
            $data = $db->getFromDB("SELECT support_chapter.id AS scId,support_chapter.name AS scName,status AS status,support_chapter.unitId AS scUnitId,(SELECT name FROM support_unit WHERE id = support_chapter.unitId) as chapterSupportUnitName FROM support_chapter  WHERE support_chapter.id =" . $scId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'support_chapter_form_load':
        $scId = isset($_POST['scId']) ? intval($_POST['scId']) : 0;
        if ($scId) {
            $sql = "SELECT  support_chapter.id AS scId,support_chapter.name AS scName,status as comboMasterSupportChapterstatus,support_chapter.unitId AS scUnitId,(SELECT name FROM support_unit WHERE id = support_chapter.unitId) as chapterSupportUnitName FROM support_chapter WHERE support_chapter.id =" . $scId;
            $results = $db->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'saveSupportChapter':
        $db->query('begin');
        $data = array(
            "id" => $_POST['id'],
            "name" => $_POST['name'],
            "status" => $_POST['status'],
            "unitId" => $_POST['scUnitId'],
        );
        $scId = $data['id'];
        $scName = $data['name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $scName = addslashes($scName);




        if ($data['id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;


            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from support_chapter WHERE name ='{$scName}' AND id !='{$scId}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Support Chapter already exists.'}";
                exit;
            } else {
                $status = $db->perform("support_chapter", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from support_chapter WHERE name ='{$scName}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Support Chapter already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('support_chapter', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT support_chapter.id AS scId,support_chapter.name AS scName,IF((status=1),'Active','Inactive') AS status,support_chapter.unitId AS scUnitId,(SELECT name FROM support_unit WHERE id = support_chapter.unitId) as scUnitName FROM support_chapter WHERE support_chapter.id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'getSupportUnit':
        if ($_POST['suTypeId'] > 0) {
            $querCon = " INNER JOIN support_type_unit ON support_type_unit.unitId = support_unit.id AND support_type_unit.typeId = {$_POST['suTypeId']} ";
        } else {
            $querCon = "";
        }
        $qry = "select support_unit.id AS suId,support_unit.name AS suName from support_unit  {$querCon} WHERE status = 1 order by suName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getAllChapters':
        if ($_POST['scUnitId'] > 0) {
            $cond = " AND support_chapter.unitId = {$_POST['scUnitId']} ";
        } else {
            $cond = " ";
        }
        $qry = "select support_chapter.id AS scId,support_chapter.name AS scName from support_chapter  WHERE status = 1 {$cond} order by support_chapter.name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getChapter':
        $qry = "select support_chapter.id AS scId,support_chapter.name AS scName from support_chapter  WHERE status = 1 AND support_chapter.unitId = {$_POST['scUnitId']} order by support_chapter.name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getTopic':
        $qry = "select support_topic.id AS stId,support_topic.name AS stName from support_topic  WHERE status = 1 AND stChapterId = " . intval($_POST['stChapterId']) . " order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getSubTopic':
        $qry = "select support_sub_topic.id AS subTopicId,support_sub_topic.name AS subTopicName from support_sub_topic  WHERE status = 1 AND support_sub_topic.topicId = {$_POST['mainTopicId']} order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'saveTopic':
        $db->query('begin');
        $data = array(
            "id" => $_POST['id'],
            "name" => $_POST['name'],
            "status" => $_POST['status'],
            "chapterId" => $_POST['topicChapter'],
        );
        $stId = $data['id'];
        $stName = $data['name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $stName = addslashes($stName);




        if ($data['id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;


            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from support_topic WHERE name ='{$stName}' AND id !='{$stId}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Support Topic already exists.'}";
                exit;
            } else {
                $status = $db->perform("support_topic", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from support_topic WHERE name ='{$stName}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Support Topic already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('support_topic', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT support_topic.id AS stId,support_topic.name AS stName,IF((status=1),'Active','Inactive') AS status,chapterId,
        (SELECT name FROM support_chapter WHERE id = support_topic.chapterId) as stChapterName FROM support_topic WHERE id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'TopicdetailsView':
        $stId = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
        if ($stId) {
            $data = $db->getFromDB("SELECT support_topic.id AS stId,support_topic.name AS stName,status,IF((status=1),'Active','Inactive') AS statusName,chapterId,
            (SELECT name FROM support_chapter WHERE id = support_topic.chapterId) as stChapterName FROM support_topic WHERE id = " . $stId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'listTopic':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'stId' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['sm_id', 'sm_name', 'sm_category', 'sm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM support_topic  {$search}";
        $listQuery = "SELECT * FROM (SELECT support_topic.id AS stId,support_topic.name AS stName,IF((status=1),'Active','Inactive') AS status,chapterId,
        (SELECT name FROM support_chapter WHERE id = support_topic.chapterId) as stChapterName FROM support_topic) AS listTopic  
            " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'support_topic_form_load':
        $stId = isset($_POST['stId']) ? intval($_POST['stId']) : 0;
        if ($stId) {
            $sql = "SELECT  support_topic.id AS stId,support_topic.name AS stName,status as comboMasterSupportTopicstatus,chapterId as topicChapter,(SELECT support_chapter.unitId FROM support_chapter WHERE support_chapter.id = support_topic.chapterId) as topicSupportUnit,
            (SELECT name FROM support_chapter WHERE id = support_topic.chapterId) as stChapterName FROM support_topic WHERE support_topic.id =" . $stId;
            $results = $db->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'saveSubTopic':
        $db->query('begin');
        $data = array(
            "id" => $_POST['id'],
            "name" => $_POST['name'],
            "status" => $_POST['status'],
            "topicId" => $_POST['mainTopicId'],
        );
        $subTopicId = $data['id'];
        $subTopicName = $data['name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $subTopicName = addslashes($subTopicName);




        if ($data['id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;


            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from support_sub_topic WHERE name ='{$subTopicName}' AND id !='{$subTopicId}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Support Sub Topic already exists.'}";
                exit;
            } else {
                $status = $db->perform("support_sub_topic", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from support_sub_topic WHERE name ='{$subTopicName}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Support Sub Topic already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('support_sub_topic', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT support_sub_topic.id AS subTopicId,support_sub_topic.name AS subTopicName,IF((status=1),'Active','Inactive') AS status,support_sub_topic.topicId,
        (SELECT support_topic.name FROM support_topic WHERE support_topic.id = support_sub_topic.topicId) as mainTopicName FROM support_sub_topic WHERE id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listSubTopic':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'subTopicId' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['sm_id', 'sm_name', 'sm_category', 'sm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM support_sub_topic  {$search}";
        $listQuery = "SELECT * FROM (SELECT support_sub_topic.id AS subTopicId,support_sub_topic.name AS subTopicName,IF((status=1),'Active','Inactive') AS substatus,status,support_sub_topic.topicId,
        (SELECT support_topic.name FROM support_topic WHERE support_topic.id = support_sub_topic.topicId) as stName FROM support_sub_topic ) AS listSubTopic 
            " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'subTopicdetailsView':
        $subTopicId = isset($_POST['subTopicId']) ? intval($_POST['subTopicId']) : 0;
        if ($subTopicId) {
            $data = $db->getFromDB("SELECT support_sub_topic.id AS subTopicId,support_sub_topic.name AS subTopicName,IF((status=1),'Active','Inactive') AS substatus, status,support_sub_topic.topicId,
                (SELECT support_topic.name FROM support_topic WHERE support_topic.id = support_sub_topic.topicId) as stName FROM support_sub_topic WHERE support_sub_topic.id = " . $subTopicId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'subtopic_form_load':
        $subTopicId = isset($_POST['subTopicId']) ? intval($_POST['subTopicId']) : 0;
        if ($subTopicId) {
            $sql = "SELECT  support_sub_topic.id AS subTopicId,support_sub_topic.name AS subTopicName,IF((status=1),'Active','Inactive') AS statusName,support_sub_topic.status AS status,support_sub_topic.topicId AS mainTopicId,
            (SELECT support_topic.name FROM support_topic WHERE support_topic.id = support_sub_topic.topicId) as stName, 
            (SELECT support_topic.chapterId FROM support_topic WHERE support_topic.id = support_sub_topic.topicId) as subTopicScId,
            (SELECT name FROM support_chapter WHERE id = (SELECT support_topic.chapterId FROM support_topic WHERE support_topic.id = mainTopicId)) as subTopicScName,
            (SELECT support_chapter.unitId FROM support_chapter WHERE support_chapter.id = (SELECT support_topic.chapterId FROM support_topic WHERE support_topic.id = mainTopicId)) as subTopicSuId,
            (SELECT name FROM support_unit WHERE id = (SELECT support_chapter.unitId FROM support_chapter WHERE support_chapter.id = (SELECT support_topic.chapterId FROM support_topic WHERE support_topic.id = support_sub_topic.topicId))) as subTopicSuName FROM support_sub_topic WHERE support_sub_topic.id =" . $subTopicId;
            $results = $db->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'savearticle':
        $db->query('begin');

        $data = array(
            "id" => $_POST['articleId'],
            "name" => $_POST['articleName'],
            "typeId" => $_POST['articleStId'],
            "unitId" => $_POST['articleSuId'],
            "chapterId" => $_POST['articleChapter'],
            "topicId" => $_POST['articleTopic'],
            "subTopicId" => $_POST['articleSubTopic'],
            "content" => $_POST['articleContent'],
            "status" => $_POST['articleStatus']
        );
        $data = array_filter($data);
        if ($_POST['isFeaturedArticle'] == 'true') {
            $isFeaturedArticle = 1;
        } else {
            $isFeaturedArticle = 0;
        }
        $data["isFeaturedArticle"] = $isFeaturedArticle;
        $articleId = $data['id'];
        $articleName = $data['name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $articleName = addslashes($articleName);
        if ($data['id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;


            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from support_article WHERE name ='{$articleName}' AND id !='{$articleId}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Support Article already exists.'}";
                exit;
            } else {
                $status = $db->perform("support_article", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from support_article WHERE name ='{$articleName}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Support Article already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('support_article', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT support_article.id AS articleId,support_article.name AS articleName,IF((support_article.status=1),'Active','Inactive') AS status,support_article.chapterId AS articleChapter,support_article.topicId AS articleTopic,support_article.subTopicId AS articleSubTopic,support_article.status AS articleStatus,
        (SELECT support_topic.name FROM support_topic WHERE support_topic.id = support_article.topicId) as articleTopicName,(SELECT name FROM support_chapter WHERE id = support_article.chapterId) as articleChapterName,
        (SELECT support_sub_topic.name FROM support_sub_topic WHERE support_sub_topic.id = support_article.subTopicId) AS articleSubTopicName  FROM support_article WHERE id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listArticle':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'support_article.id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['sm_id', 'sm_name', 'sm_category', 'sm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM support_article  {$search}";
        $listQuery = "SELECT * FROM (SELECT support_article.id AS articleId,support_article.name AS articleName,support_article.content AS articleContent,IF((support_article.status=1),'Active','Inactive') AS status,support_article.chapterId AS articleChapter,support_article.topicId AS articleTopic,support_article.subTopicId AS articleSubTopic,support_article.status AS articleStatus,
        (SELECT support_topic.name FROM support_topic WHERE support_topic.id = support_article.topicId) as articleTopicName,(SELECT name FROM support_chapter WHERE id = support_article.chapterId) as articleChapterName,
        (SELECT support_sub_topic.name FROM support_sub_topic WHERE support_sub_topic.id = support_article.subTopicId) AS articleSubTopicName,support_article.unitId AS articleSuId,(SELECT name FROM support_unit WHERE id = support_article.unitId) AS articleSuName,
        support_article.typeId as articleStId,(SELECT typeName FROM support_type WHERE typeId = support_article.typeId) AS articleStName  FROM support_article) AS listArticles  
                " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'articleDetailsView':
        $articleId = isset($_POST['articleId']) ? intval($_POST['articleId']) : 0;
        if ($articleId) {
            $data = $db->getFromDB("SELECT support_article.id AS articleId,support_article.name AS articleName,support_article.content AS articleContent,IF((support_article.status=1),'Active','Inactive') AS status,support_article.chapterId AS articleChapter,support_article.topicId AS articleTopic,support_article.subTopicId AS articleSubTopic,support_article.status AS articleStatus,
            (SELECT support_topic.name FROM support_topic WHERE support_topic.id = support_article.topicId) as articleTopicName,(SELECT name FROM support_chapter WHERE id = support_article.chapterId) as articleChapterName,
            (SELECT support_sub_topic.name FROM support_sub_topic WHERE support_sub_topic.id = support_article.subTopicId) AS articleSubTopicName,support_article.typeId AS articleStId,(SELECT typeName FROM support_type WHERE typeId = support_article.typeId) AS articleStName  FROM support_article WHERE id = " . $articleId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'article_form_load':
        $articleId = isset($_POST['articleId']) ? intval($_POST['articleId']) : 0;
        if ($articleId) {
            $sql = "SELECT support_article.id AS articleId,support_article.name AS articleName,support_article.content AS articleContent,IF((support_article.status=1),'Active','Inactive') AS status,support_article.chapterId AS articleChapter,support_article.topicId AS articleTopic,support_article.subTopicId AS articleSubTopic,support_article.status AS articleStatus,support_article.unitId AS articleSuId,isFeaturedArticle,
            (SELECT support_topic.name FROM support_topic WHERE support_topic.id = support_article.topicId) as articleTopicName,(SELECT name FROM support_chapter WHERE id = support_article.chapterId) as articleChapterName,
            (SELECT support_sub_topic.name FROM support_sub_topic WHERE support_sub_topic.id = support_article.subTopicId) AS articleSubTopicName,(SELECT name FROM support_unit WHERE id = support_article.unitId) AS articleSuName,
            support_article.typeId AS articleStId,(SELECT typeName FROM support_type WHERE typeId = support_article.typeId) AS articleStName  FROM support_article WHERE id =" . $articleId;
            $results = $db->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'getQuestionarticles':
        $id = intval($_POST['questionId']);
        $edit_status = $_POST['edit_status'];
        $filter = $_POST['filter'];
        $search = " WHERE 1=1 ";
        $items = array();
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');
                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                }
            }
        }
        if ($edit_status == 1)
            $qry = "SELECT support_article.id AS articleId,support_article.name AS articleName,if(support_question_articles.articleId is null,0,1) as checked FROM support_article LEFT JOIN support_question_articles ON support_question_articles.articleId = support_article.id and support_question_articles.questionId = {$id} where support_article.status = 1 {$searchitem} ";
        else
            $qry = "SELECT support_article.id AS articleId,support_article.name AS articleName FROM support_article where support_article.status = 1 {$searchitem}";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo json_encode([]);
        break;
    case 'getParentQuestion':
        $qry = "select support_question.id AS questionId,support_question.name AS questionName from support_question  WHERE support_question.status = 1 AND isParentQuestion = 1 order by support_question.name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'saveQuestion':
        $sq_articles = $_POST['sq_articles'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $data['id'] = $_POST['questionId'];
        $data['unitId'] = $_POST['questionSuId'];
        $data['name'] = $_POST['questionName'];
        $data['status'] = $_POST['questionStatus'];
        $data['content'] = $_POST['questionContent'];
        $data['chapterId'] = ($_POST['questionChapterId']>0?$_POST['questionChapterId']:0);
        if ($_POST['isFeaturedQuestion'] == 'true') {
            $data['isFeaturedQuestion'] = 1;
        } else {
            $data['isFeaturedQuestion'] = 0;
        }
        if ($_POST['isParentQuestion'] == 'true') {

            $data['isParentQuestion'] = 1;
            $data['parentQuestionId'] = 0;
        } else {
            $data['isParentQuestion'] = 0;
            if ($_POST['parentQuestionId'] > 0) {
                $data['parentQuestionId'] = $_POST['parentQuestionId'];
            }
        }
        $db->query('begin');
        if ($data['id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;


            $packagenameUnique = $db->getItemSafe("SELECT COUNT(*) from support_question WHERE name ='?' AND id !='{$_POST['questionId']}' ", "s", [$_POST['questionName']]);
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Support Question already exists.'}";
                exit;
            } else {
                $status = $db->perform("support_question", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $packagenameUnique = $db->getItemSafe("SELECT COUNT(*) from support_question WHERE name ='?' ", "s", [$_POST['questionName']]);
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Support Question already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('support_question', $data);
                $lastId = $db->insert_id();
            }
        }

        $status = $db->query("DELETE FROM support_question_articles WHERE questionId = {$lastId}");
        $sq_articles_array = explode(',', $_POST['sq_articles']);
        if (count($sq_articles_array) > 0) {
            for ($i = 0; $i < count($sq_articles_array); $i++) {
                if ($sq_articles_array[$i] > 0) {
                    $sqaData['questionId'] = $lastId;
                    $sqaData['articleId'] = $sq_articles_array[$i];
                    $sqaData['createdOn'] = date('Y-m-d H:i:s');
                    $sqaData['createdBy'] = $userid;
                    $status = $db->perform('support_question_articles', $sqaData);
                }
            }
        }
        $return_rec = $db->getFromDb("SELECT support_question.id AS questionId,support_question.name AS questionName,IF((support_question.status=1),'Active','Inactive') AS status,support_question.status AS questionStatus,support_question.content AS questionContent,isParentQuestion,parentQuestionId,
        (SELECT name FROM support_unit WHERE id = support_question.unitId) as questionSuName,support_question.unitId AS questionSuId FROM support_question WHERE id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'listQuestion':
        $rec_limit = empty($_POST['limit']) ? 20 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'support_question.id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['sm_id', 'sm_name', 'sm_category', 'sm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM support_question  {$search}";
        $listQuery = "SELECT support_question.id AS questionId,support_question.name AS questionName,IF((support_question.status=1),'Active','Inactive') AS status,support_question.status AS questionStatus,support_question.content AS questionContent,isParentQuestion,parentQuestionId,
            (SELECT name FROM support_unit WHERE id = support_question.unitId) as questionSuName,support_question.unitId AS questionSuId,support_question.chapterId AS questionChapterId,
            (SELECT name FROM support_chapter WHERE id = support_question.chapterId) as questionChapterName FROM support_question {$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'questionDetailsView':
        $questionId = isset($_POST['questionId']) ? intval($_POST['questionId']) : 0;
        if ($questionId) {
            $data = $db->getFromDB("SELECT support_question.id AS questionId,support_question.name AS questionName,IF((support_question.status=1),'Active','Inactive') AS status,support_question.status AS questionStatus,support_question.content AS questionContent,isParentQuestion,parentQuestionId,
            (SELECT name FROM support_unit WHERE id = support_question.unitId) as questionSuName,support_question.unitId AS questionSuId,support_question.chapterId AS questionChapterId,
            (SELECT name FROM support_chapter WHERE id = support_question.chapterId) as questionChapterName FROM support_question WHERE support_question.id = " . $questionId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'question_form_load':
        $questionId = isset($_POST['questionId']) ? intval($_POST['questionId']) : 0;
        if ($questionId) {
            $sql = "SELECT support_question.id AS questionId,support_question.name AS questionName,isFeaturedQuestion,IF((support_question.status=1),'Active','Inactive') AS status,support_question.status AS questionStatus,support_question.content AS questionContent,isParentQuestion,parentQuestionId,
                (SELECT name FROM support_unit WHERE id = support_question.unitId) as questionSuName,support_question.unitId AS questionSuId,support_question.chapterId AS questionChapterId,
            (SELECT name FROM support_chapter WHERE id = support_question.chapterId) as questionChapterName FROM support_question WHERE support_question.id = " . $questionId;
            $results = $db->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'listQuestionarticles':
        $questionId = intval($_POST['questionId']);
        $edit_status = $_POST['edit_status'];
        $filter = $_POST['filter'];
        $search = " WHERE 1=1 ";
        $items = array();
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');
                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                }
            }
        }
        $qry = "SELECT support_article.id AS articleId,support_article.name AS articleName,1 as checked FROM support_article INNER JOIN support_question_articles ON support_question_articles.articleId = support_article.id where support_question_articles.questionId = {$questionId} {$searchitem}";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo json_encode([]);
        break;
    case 'listSupportTypes':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'typeId' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['sm_id', 'sm_name', 'sm_category', 'sm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM support_type  {$search}";
        $listQuery = "SELECT typeId,typeName,IF((typeStatus=1),'Active','Inactive') AS status FROM support_type 
        " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'supporttypesdetailsView':

        $typeId = isset($_POST['typeId']) ? intval($_POST['typeId']) : 0;
        if ($typeId || $ID) {

            $data = $db->getFromDB("SELECT typeId,typeName,typeStatus,IF((typeStatus=1),'Active','Inactive') AS status FROM support_type  WHERE typeId =" . $typeId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'supporttypes_form_load':

        $typeId = isset($_POST['typeId']) ? intval($_POST['typeId']) : 0;
        if ($typeId) {
            $sql = "SELECT  typeId,typeName,typeStatus FROM support_type WHERE typeId =" . $typeId;
            $results = $db->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'getSupportTypeSupportUnit':
        $id = intval($_POST['typeId']);
        $edit_status = $_POST['edit_status'];
        $filter = $_POST['filter'];
        $search = " WHERE 1=1 ";
        $items = array();
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');
                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                }
            }
        }
        if ($edit_status == 1)
            $qry = "SELECT support_unit.id AS suId,support_unit.name AS suName,if(support_type_unit,unitId is null,0,1) as checked FROM support_unit LEFT JOIN support_type_unit ON support_type_unit.unitId = support_unit.id and support_type_unit.typeId = {$id} where status = 1 {$searchitem} ";
        else
            $qry = "SELECT support_unit.id AS suId,support_unit.name AS suName FROM support_unit where status = 1 {$searchitem}";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo json_encode([]);
        break;
    case 'listSupportTypeSupportUnit':
        $typeId = intval($_POST['typeId']);
        $edit_status = $_POST['edit_status'];
        $filter = $_POST['filter'];
        $search = " WHERE 1=1 ";
        $items = array();
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');
                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                }
            }
        }
        $qry = "SELECT support_unit.id AS suId,support_unit.name AS suName,1 as checked FROM support_unit INNER JOIN support_type_unit ON support_type_unit.unitId = support_unit.id where support_type_unit.typeId = {$typeId} {$searchitem}";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo json_encode([]);
        break;
    case 'saveSupportTypes':
        $sq_articles = $_POST['supTyp_supUnit'];
        $db->query('begin');

        $data = array(
            "typeId" => $_POST['id'],
            "typeName" => $_POST['name'],
            "typeStatus" => $_POST['status']
        );
        $typeId = $data['typeId'];
        $typeName = $data['typeName'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $typeName = addslashes($typeName);




        if ($data['typeId'] > 0) {

            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $userid;


            $supTypeUnique = $db->getItemFromDB("SELECT COUNT(*) from support_type WHERE typeName ='{$typeName}' AND typeId!='{$typeId}' "); //AND store_group_id = 0
            if ($supTypeUnique > 0) {
                echo "{success: false, message:'Support Beneficiary already exists.'}";
                exit;
            } else {
                $status = $db->perform("support_type", $data, 'update', 'typeId =' . $data['typeId']);
                $lastId = $data['typeId'];
            }
        } else {

            $supTypeUnique = $db->getItemFromDB("SELECT COUNT(*) from support_type WHERE typeName ='{$typeName}'   "); //AND store_group_id = 0
            if ($supTypeUnique > 0) {
                echo "{success: false, message:'Support Beneficiary already exists.'}";
                exit;
            } else {
                unset($data['typeId']);
                $data['createdOn'] = date('Y-m-d H:i:s');
                $data['createdBy'] = $userid;
                $status = $db->perform('support_type', $data);
                $lastId = $db->insert_id();
            }
        }

        $status = $db->query("DELETE FROM support_type_unit WHERE support_type_unit.typeId = {$lastId}");
        $stype_units_array = explode(',', $_POST['supTyp_supUnit']);
        if ($stype_units_array) {
            for ($i = 0; $i < count($stype_units_array); $i++) {
                $sqaData['typeId'] = $lastId;
                $sqaData['unitId'] = $stype_units_array[$i];
                $sqaData['createdOn'] = date('Y-m-d H:i:s');
                $sqaData['createdBy'] = $userid;
                $status = $db->perform('support_type_unit', $sqaData);
            }
        }

        $return_rec = $db->getFromDb("SELECT typeId,typeName,typeStatus FROM support_type WHERE typeId = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'getSupportType':

        $qry = "select typeId,typeName from support_type WHERE typeStatus = 1 order by typeName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
}
