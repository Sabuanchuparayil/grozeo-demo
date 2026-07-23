<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/AgentMaster.Master" CodeBehind="AppView.aspx.cs" Inherits="RetalineProAgent.AppView" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Apps</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"> Apps View</h6>
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
        <style>
.appviewrap .card {
  border: 0;
  align-items: center;
  background: #e7e9ee;
  margin: 10px 5px;
  padding: 10px;
  border-radius: 10px;
  overflow: hidden;
  border:1px solid #e7e9ee;
}
.appviewrap .card:hover,
.appviewrap .card.active {
  border: 1px solid #7cbf21;
  background: #ebecee;
}

.appviewrap .card img {
  max-width: 130px;
}

.appviewrap .card h5 {
  font-size: 14px;
  font-weight: 500;
  text-align: center;
  color: #343a40;
  margin: 10px 0px 10px;
  text-transform: capitalize;
}

.mobileappview {
  display: flex;
  flex-wrap: wrap;
  justify-content: right;
}

.mobileappview h5 {
  display: none;
  font-size: 14px;
  font-weight: 500;
  text-align: center;
  color: #343a40;
  width: 100%;
  margin: 10px 0px 20px;
  text-transform: capitalize;
}

.device {
  position: relative;
  width: 353px;
  height: 714px;
  margin-top: -20px;
  -webkit-transform: scale(0.95);
  -moz-transform: scale(0.95);
  -ms-transform: scale(0.95);
  transform: scale(0.95);
}

.device-border {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 353px;
  z-index: 1;
  pointer-events: none;
}

.screen {
  top: 18px;
  left: 24px;
  cursor: pointer;
  width: 310px;
  height: 684px;
  position: absolute;
  z-index: 0;
  overflow: hidden;
}

.device_content {
  width: 100%;
  height: 100%;
  position: relative;
}

.device_content iframe {
  width: 100%;
  height: 100%;
  border: 0;
  border-radius: 30px;
}
.device_content.loader::after {
  position: absolute;
  content: '';
  width: 100%;
  height: 100%;
  background: #f0f2f7;
  top: 0;
  left: 0;
  z-index: 0;
}
.loader::before {
  content: '';
  position: absolute;
  border: 8px solid #BBB;
  border-top: 8px solid #717171;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  top: -50%;
  bottom: -50%;
  margin: auto;
  left: -50%;
  right: -50%;
  z-index: 1;
  -webkit-animation: spin 1s linear infinite;
  animation: spin 1s linear infinite;
}
/* Safari */
@-webkit-keyframes spin {
  0% {
      -webkit-transform: rotate(0deg);
  }

  100% {
      -webkit-transform: rotate(360deg);
  }
}

@keyframes spin {
  0% {
      transform: rotate(0deg);
  }

  100% {
      transform: rotate(360deg);
  }
}
.loadapp{
  cursor: pointer;
  padding: 5px;
  text-align: center;
  font-size: 10px;
  background: #839aae;
  margin: 2px;
  line-height: 100%;
  border-radius: 5px;
  color: #FFF;
  min-width:50px;
}
.loadapp:hover, .loadapp.active{
  background: #4199e6;
}
.device.apponlyview{
  transform: none;
  width: 100%;
  height: 100%;
  margin-top: 10px;
  position: static;
}
.device.apponlyview .screen {
  position:static;
  width: 100%;
  height: auto;
}
.device.apponlyview .iphonex{
  display: none;
}
.device.apponlyview iframe {
  height: 650px;
  padding-left: 20px;
}


    </style>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="appviewrap">
          <div class="row row-xs">
          
            <div class="col-12  col-lg-8">
              <div class="row row-xs">
                <div class="col-12 col-lg-4">
                  <div class="card justify-content-center " >
                    <h5>Grozeo Front</h5>
                    <img src="/content/images/devices/Mobile_View_Tenant.png" alt="Customer App">
                    <div class="d-flex mt-2 mb-1">
                      <div class="loadapp" appurl="https://demo.grozeo.in">Site</div>
                      <div class="loadapp apponly" appurl="https://appetize.io/embed/ek7yohbyhcmfgyfs4kyn6rz4oq?device=pixel4&osVersion=12.0&scale=75">Android App</div>
                      <div class="loadapp apponly" appurl="https://appetize.io/embed/6wgdemy23aweqjgvrvnnrn3mte?device=iphonex">iOS App</div>
                    </div>
                  </div>
                </div><!--col-lg-4 -->
      
                <div class="col-12 col-lg-4">
                  <div class="card justify-content-center" appurl="https://manage.demo.grozeo.in">
                    <h5>Partner Front</h5>
                    <img src="/content/images/devices/Mobile_View_Partner.png" alt="Admin App">                    
                    <div class="d-flex mt-2 mb-1">
                      <div class="loadapp" appurl="https://themarket-097.demo.grozeo.in">Site</div>
                      <%--<div class="loadapp apponly" appurl="https://appetize.io/embed/ek7yohbyhcmfgyfs4kyn6rz4oq?device=pixel4&osVersion=12.0&scale=75">Android App</div>
                      <div class="loadapp apponly" appurl="https://appetize.io/embed/6wgdemy23aweqjgvrvnnrn3mte?device=iphonex">iOS App</div>--%>
                    </div>
                  </div>
                </div><!--col-lg-4 -->
      
                <div class="col-12 col-lg-4">
                  <div class="card justify-content-center" appurl="https://demo.grozeo.in">
                    <h5>Partner Admin</h5>
                    <img src="/content/images/devices/Mobile_View_AdminApp.png" alt="App view list 3">
                    <div class="d-flex mt-2 mb-1">
                      <div class="loadapp" appurl="http://partner.demo.grozeo.in/Tenant">Site</div>
                    </div>
                  </div>
                </div><!--col-lg-4 -->
      
                <div class="col-12 col-lg-4">
                  <div class="card justify-content-center" appurl="https://partner.demo.grozeo.in">
                    <h5>Grozeo Admin</h5>
                    <img src="/content/images/devices/Mobile_View_finascop.png" alt="App view list 4">
                    <div class="d-flex mt-2 mb-1">
                      <div class="loadapp" appurl="http://partner.demo.grozeo.in/Finance/">Finance</div>
                      <%--<div class="loadapp" appurl="https://backoffice.demo.grozeo.in/">Back Office</div>--%>
                    </div>
                  </div>
                </div><!--col-lg-4 -->
      
                <div class="col-12 col-lg-4">
                  <div class="card justify-content-center " appurl="https://appetize.io/embed/wxqypriwnsjwykbbwg36fefbqy?device=iphone8">
                    <h5>PackSure</h5>
                    <img src="/content/images/devices/Mobile_View_PacksureApp.png" alt="App view list 5">
                    <div class="d-flex mt-2 mb-1">
                      <div class="loadapp apponly" appurl="https://play.google.com/store/apps/details?id=com.grozeo.grozeopack">Android App</div>
                      <div class="loadapp apponly" appurl="https://apps.apple.com/in/app/grozeo-packsure/id1658485053">iOS App</div>
                    </div>
                  </div>
                </div><!--col-lg-4 -->
      
                <div class="col-12 col-lg-4 " appurl="https://appetize.io/embed/2tbnm6bxud7wsjarpnjokv7b7q?device=pixel4">
                  <div class="card justify-content-center">
                    <h5>Drive</h5>
                    <img src="/content/images/devices/Mobile_View_DriveApp.png" alt="App view list 6">
                    <div class="d-flex mt-2 mb-1">
                      <div class="loadapp apponly" appurl="https://play.google.com/store/apps/details?id=com.grozeo.grozeodrive">Android App</div>
                      <div class="loadapp apponly" appurl="https://apps.apple.com/in/app/grozeo-drive/id6451487290">iOS App</div>
                    </div>
                  </div>
                </div><!--col-lg-4 -->
              </div>
            </div><!--col-8-->
  
            <div class="col-12 col-lg-4">
              <div class="mobileappview">
                <div class="device">
                  <div class="iphonex">
                    <div class="device-border-wrap">
                      <img alt="device border" src="/content/images/devices/iPhone_X.png" class="device-border">
                    </div>
                  </div>
                  <div class="screen">
                    <div class="device_content" tabindex="0">
                      <iframe id="frmmobileview" src=""></iframe>
                    </div>
                  </div>
                  
                </div>
                <h5>Admin App - Responsive</h5>
              </div>
              
            </div><!--col-lg-4-->
  
          </div><!--row-->
        </div><!--appviewrap-->
    <script type="text/javascript">
        $('div.loadapp').unbind('click').on('click', function () {
            $('div').closest('div.card').removeClass('active');
            $('div.loadapp').removeClass('active')
            $(this).addClass('active');
            $('.device_content').addClass('loader');
            $(this).closest('div').closest('div.card').addClass('active');
            var targerurl = $(this).attr('appurl');
            if (targerurl && targerurl.length > 1) {
                //$('#frmmobileview').html('loading');
                $('#frmmobileview').attr('src', '');
                $('#frmmobileview').attr('src', targerurl);
                $('#frmmobileview').focus();
                $('div.device').removeClass('apponlyview')
                //$('div.loadapp').closest('div.d-flex').removeClass('xxxxxxxx');
            }
            setTimeout(function () {
                if (targerurl && targerurl.length > 1) {
                    $('.device_content').removeClass('loader');
                }
            }, 3500);
        });

        $('div.apponly').on('click', function () {
            $('div.device').addClass('apponlyview')
        });


    </script>

</asp:Content>