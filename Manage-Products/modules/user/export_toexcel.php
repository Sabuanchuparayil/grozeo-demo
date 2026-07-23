<?php
/*
 * Created on 17-Sep-08
 * @author : Anju P <anju@saturn.in>
 *
 * To create excel of Admin Users
 */

include("includes/Spreadsheet/Writer.php");

/* Title Settings */
$cTitles['sd'] = array("User Name","First Name","Last Name","Address","Phone Number","Office","Email");


/* Settings used for subtitle*/

$subTitle['sd']= 'User Details';

$excel = new Spreadsheet_Excel_Writer();

$sheet =& $excel->addWorksheet('IGHR');
$rtype = 'sd';
$title		= "User Details";
$excel->send('ighr_'.$title.date('_Y_m_d').'.xls');

$query		= "SELECT au.admin_username,ap.admin_fname,ap.admin_lname,ap.admin_address,ap.admin_telephone,ap.admin_office,au.admin_email from admin_users au ,admin_profile ap,admin_role ar where ap.uidnr_admin=au.uidnr_admin and ar.id_admin_role=au.id_admin_role and admin_super='0' order by au.admin_username ";
$rs			= $db->query($query);

$titleA		= array('align'=> 'center','bold' => 1,'size' => 13,'font-name' => 'Helvetica','color'=>'blue','fgcolor' => 'white');

$sheet->setMerge(0,0,1,count($cTitles[$rtype])-1);
$sheet->write(0,0,'IGHR -'.$title,$excel->addFormat($titleA));


$rowCount 	= 4;
$sheet->setColumn(0,count($cTitles[$rtype]),25);

$firstRow =& $excel->addFormat();
$firstRow->setBold();
$firstRow->setColor('red');

foreach($cTitles[$rtype] as $key=>$val)
	$sheet->write($rowCount,$key,$val,$firstRow);

$rowCount++;

$rowStart = $rowCount;
$curOrder = '';
if($db->num_rows($rs)>0){
	while($rd =$db->fetch_row($rs)){

		/* repeated order no for rows eliminate */
		if($curOrder == $rd[0]){
			if(is_array($repeatedRows[$rtype])){
				foreach ($repeatedRows[$rtype] as $key =>$value)
					$rd[$key]=$value;
			}
		}
		else
			$curOrder = $rd[0];

		foreach($rd as $key=>$val)
			$sheet->write($rowCount,$key,stripslashes($val));
		$rowCount++;
	}

}
$excel->close();
exit();
?>
