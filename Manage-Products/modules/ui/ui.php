<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
    <HEAD>
        <TITLE><?= SITE_TITLE ?></TITLE>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="./resources/mypharmacy/img/favicon.ico" type="image/png" />
        <!-- Include Ext stylesheets here: -->
        <!--<link rel="stylesheet" type="text/css" href="./ext-3.3.1/resources/css/ext-all-notheme.css"> -->
        <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="./ext-3.4.0/resources/css/ext-all.css">
        <!-- Include custom Style to support Layout: -->    
        <!--   <link rel="stylesheet" type="text/css" href="./resources/themes/xtheme-vistablack/css/xtheme-vistablack.css">-->
        <!--  <link rel="stylesheet" type="text/css" href="./resources/css/icons.css"> -->
        <link rel="stylesheet" type="text/css" href="./ext-3.4.0/resources/css/xtheme-gray.css">
        <!-- <link rel="stylesheet" type="text/css" href="./ext-3.3.1/resources/css/xtheme-access.css">--> 
        <link rel="stylesheet" type="text/css" href="./resources/css/icons.css">
        <link rel="stylesheet" type="text/css" href="./resources/css/finascop_icons.css">
        <!-- <link rel="stylesheet" type="text/css" href="./resources/themes/xtheme-vistablack/css/xtheme-vistablack.css">-->
        <link rel="stylesheet" type="text/css" href="./resources/ux/css/ux_VerticalTabPanel.css">
        <link rel="stylesheet" type="text/css" href="./resources/css/Ext.ux.form.LovCombo.css">
        <link rel="stylesheet" type="text/css" href="./resources/ux/css/Ext.ux.grid.RowActions.css">
        <link rel="stylesheet" type="text/css" href="./resources/ux/css/GroupSummary.css">
        <link rel="stylesheet" type="text/css" href="./resources/ux/css/rowactions.css">
        <link rel="stylesheet" type="text/css" href="./resources/ux/css/MultiSelect.css">
        <link rel="stylesheet" type="text/css" href="./resources/ux/css/colorpicker.css">
        <link rel="stylesheet" type="text/css" href="./resources/css/Ext.ux.UploadDialog.css">
        <link rel="stylesheet" type="text/css" href="./resources/ux/css/file-upload.css">
        <link rel="stylesheet" type="text/css" href="./resources/ux/css/tab-scroller-menu.css">
        <link rel="stylesheet" type="text/css" href="./resources/css/explorerview.css">
        <link rel="stylesheet" type="text/css" href="./resources/css/AwesomeUploader.css">
        <link rel="stylesheet" type="text/css" href="./js/ux/treegrid/treegrid.css">
        <!-- Include custom Style to support Layout: -->
        <link rel="stylesheet" type="text/css" href="./resources/css/ui-styles.css">
        <link rel="stylesheet" type="text/css" href="./resources/css/grid-filter-style.css">
        <link rel="stylesheet" type="text/css" href="./resources/css/Ext.ux.PasswordField.css">
        <link rel="stylesheet" type="text/css" href="./resources/css/media-queries.css">
        <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="./resources/ux/gridfilters/css/GridFilters.css">
        <link rel="stylesheet" type="text/css" href="./resources/ux/gridfilters/css/RangeMenu.css">
        <style>
            .x-grid3-row td,
            .x-grid3-summary-row td,
            .x-grid3-cell-text,
            .x-grid3-hd-text,
            .x-grid3-hd,
            .x-grid3-row {
                -webkit-user-select: text;
                -moz-user-select: text;
                -khtml-user-select: text;
                -ms-user-select: text;
                cursor: text;
            }
        </style>
        <script src="https://sdk.amazonaws.com/js/aws-sdk-2.7.20.min.js"></script>
        <!-- Include EXTJS Core Components: -->

        <script type="text/javascript" src="./ext-3.4.0/adapter/ext/ext-base.js"></script>

        <!--<script type="text/javascript" src="./ext-3.3.1/ext-all.js"></script>-->
        <script type="text/javascript" src="./ext-3.4.0/ext-all-debug.js"></script>

<!--<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=ABQIAAAAJg8G86ZODRLJEnutvIblmhQY4Q3hCgcSH0L1yqEIYb5eLg91BBQCn2QEvZGkwdhHm0r-9xy64xsR6A" type="text/javascript"></script>-->
<!--<script src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>-->
<!--<script src="https://maps.googleapis.com/maps/api/js?key=" . GOOGLE_MAP_API_KEY . "&sensor=false"> </script>--> 
        <!-- Include Custom Supporting Components to Build Layout: -->
        <script src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAP_API_KEY; ?>"></script> 
        <script type="text/javascript" src="./javascript.php?TODO=initdata&rand=<?= rand(); ?>"></script>
        <script type="text/javascript" src="./javascript.php"></script>

        <!-- comment or delete the following line if you don't want to use HttpProvider -->
        <!-- deleted one line from here to exclude init-state-provider.php -->
    </HEAD>
    <BODY scroll="no" bgcolor="silver" rightmargin="0" leftmargin="0" topmargin="0" bottommargin="0">
        <div id="loading-mask" style=""></div>
        <div id="loading">
            <div class="loading-indicator">
                <div style="float:left; height: 50px; margin-right: 5px; margin-top: 1px;"><img height="24" width="24" src="./resources/images/default/loading.gif" style=""/></div>
                <div style="float:left;"><span style="background: #ed1f24; color: #FFF;"> <?php //echo ONLOAD_THROBBER_TEXT;     ?></span><br/><span id="loading-msg">Loading UI Components...</span></div>
            </div>
        </div>
        <!-- Define Top Header Area: -->
        <div id="topheader" class="headermainlogo" style="max-height:60px;">

            <div id="headerlogo" class="headerlogoleft" style="max-height:60px;"><img src="./resources/mypharmacy/admin-logo.png"  alt="Admin logo"  height="60"/> 
                    <!--<img src="./resources/images/default/carego-demo.jpg"  alt="Admin logo" />-->

            </div>

            <!--<div class="logined">
                    <span class="welcomTXT">Welcome</span><br>
                    <?
                            $qry = "SELECT CONCAT(FirstName,' ',LastName) FROM  " . FINASCOP_DB . "finascop_usr_profile WHERE UserId =" . $_SESSION['admin']->Finascop_UserId;
                            $name = $db->getItemFromDB($qry);
                    ?>
                    <span class="loggedNameTXT"><?= $name ?></span>
            </div> -->
            <div style="float: left; width:72%; padding: 0px 0 0 0px;" id="top_module_container">

            </div>
            <div style="width:10%;float:right; text-align:right;padding:6px 15px 0px 0; position:relative;">
                <div class="profile_men_ct" id="profile_men_ct" style ="
                <?php
                if (isset($_SESSION['admin']->IsApplicationLogin)) {
                    if ($_SESSION['admin']->IsApplicationLogin == 1) {
                        ?>display:none<?php } else { ?> display:block  <?php
                         }
                     }
                     ?>">
                    <a href="#" id="profile_menu" onBlur="hideProfileMenu();" onClick="showProfileMenu();
                            return false;" alt="<?php echo $name; ?>" title="<?php echo $name; ?>">
                           <?php
                           if (!empty($_SESSION['admin']->finascop_current_branch_id)) {
                               $branchName = $db->getItemFromDB("SELECT br_Name from finascop_branch where br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
                           }
                           if (!empty($_SESSION['admin']->finascop_current_company_id > 0)) {
                               $compName = $db->getItemFromDB("SELECT comp_name from finascop_company where comp_id = {$_SESSION['admin']->finascop_current_company_id}");
                           }
                           $qry = "SELECT CONCAT(FirstName,' ',LastName) FROM  " . FINASCOP_DB . "finascop_usr_profile WHERE UserId =" . $_SESSION['admin']->Finascop_UserId;
                           $name = $db->getItemFromDB($qry);
                           $words = explode(" ", $name);
                           $acronym = "";
                           foreach ($words as $w) {
                               $acronym .= $w[0];
                           }
                           ?>
                        <div id="show" class="user-circle"> <?php echo $acronym; ?></div><i class="fa fa-user"></i>  <i class="fa fa-caret-down"></i></a>

                    <span id="current_settings" class="stylesett"> <b><?php
                            if (!empty($_SESSION['admin']->current_branch))
                                echo $_SESSION['admin']->current_branch
                                . ' - ' . $_SESSION['admin']->finascop_current_company;
                            ?></b> </span> 
                </div>

                <div id="profile_menu_popup">
                    <div id="uparrow"></div>
                    <ul class="x-menu-list">
                        <!--<li class="x-menu-list-item"><a class="x-menu-item" href="javascript:Application.Users.updateProfile('Profile');" unselectable="on" hidefocus="true">
    <img class="x-menu-item-icon menu-my-profile" src="./resources/images/default/icons/report_edit.png" alt="">
    <span class="x-menu-item-text">My Profile</span></a></li>
                        -->
                        <li class="x-menu-list-item"><span class="x-menu-item-text"><?php
                                if (!empty($_SESSION['admin']->current_branch))
                                    echo $branchName
                                    . ' - ' . $compName;
                                ?></span></li>
                        <li class="x-menu-list-item"><a class="x-menu-item" href="javascript:Application.Users.changePassword();" unselectable="on" hidefocus="true">
                                <img class="x-menu-item-icon menu-change-password" src="./resources/images/default/icons/lock_edit.png" alt="">
                                <span class="x-menu-item-text">Change Password</span></a></li>

                        <?php if ($_SESSION['admin']->Finascop_ActiveAcctsSwitch == true && ($_SESSION['admin']->IsApplicationLogin != 1)) { ?>  
                            <li class="x-menu-list-item"><a class="x-menu-item" href="javascript: Application.Finascop_checkSession.switchFromMenu();" unselectable="on" hidefocus="true">
                                    <img class="x-menu-item-icon " src="./resources/images/default/icons/switch_branch.png" alt="">
                                    <span class="x-menu-item-text">Switch Active Company</span></a></li>
                        <?php } ?>
                        <li class="x-menu-list-item"><a class="x-menu-item" href="javascript: Application.Dashboard.init();" unselectable="on" hidefocus="true">
                                    <img class="x-menu-item-icon " src="./resources/images/default/icons/reset.png" alt="">
                                    <span class="x-menu-item-text">Dashboard</span></a></li>


                    </ul> 
                    <div class="bbar">
                        <button onClick="javascript:Application.Users.logout();">Logout</button>
                    </div>
                </div></div>

        </div>
        <script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Loading Core API...';</script>
        <script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Loading UI Components...';</script>
        <script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Initializing...';</script>
        <script>
            var flagMenuSlide = true;
            Ext.get(document.body).on('click', function () {
                hideProfileMenu();
            });
            var disp = document.getElementById("profile_men_ct").style.display;
            if (disp == 'block')
            {
                Ext.get('profile_menu').on('click', function (e) {
                    e.stopPropagation();
                });
            }

            Ext.get('profile_menu_popup').on('click', function (e) {
                e.stopPropagation();
            });

            function showProfileMenu() {
                Ext.get('profile_menu_popup').show();
            }
            function hideProfileMenu() {
                setTimeout(function () {
                    Ext.get('profile_menu_popup').hide();
                }, 300);
            }
            function slideIN() {
                Ext.get('settings-menu-ct').slideIn();
            }
            function slideOut() {
                if (!flagMenuSlide) {
                    Ext.get('settings-menu-ct').slideOut();
                }
            }
            function slideINCheck() {
                flagMenuSlide = true;
                Ext.get('settings-menu-ct').slideIn();
            }
            function slideOutCheck() {
                flagMenuSlide = false;
            }
        </script>
    </div>
    <iframe src="" id="downloader" name="downloader" ></iframe>
    <iframe
        id="downloadIframe"
        src="#" 
        name="downloadIframe" 
        style="border:none;height:0px;width:0px;"
        ></iframe>
    <iframe
        id="iframedownload"
        src="#" 
        name="iframedownload" 
        style="border:none;height:0px;width:0px;"
        ></iframe>
    <script src="//cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
    <script>
            CKEDITOR.on('instanceReady', function (ev) {
                var baseElementId = ev.editor.name.replace('editor_', '');
                Ext.getCmp(baseElementId).mceReady(ev.editor.name, baseElementId);
            });
    </script>
</BODY>
</HTML>


