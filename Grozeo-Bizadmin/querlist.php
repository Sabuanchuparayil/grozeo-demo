<?php

echo $listQuery = "SELECT category_id,(ms.parent_category),category_name AS cat_name,(mc.status) AS status,IF(mc.image_url IS NOT NULL,'present','nil') AS image,IF((mc.isHome=1),'Yes','No') AS isHome,IF((mc.isInCategory=1),'Yes','No') AS isInCategory, mc.image_url AS image_url
     FROM mypha_productcategory mc INNER JOIN mypha_productparent_category ms ON ms.parent_category_id=mc.parent_category";