<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlSupport.ascx.cs" Inherits="RetalineProAgent.Controls.ctrlSupport" %>                      
<div class="template-options-wrapper">
      <a href="javascript:void(0)" class="template-options-btn open_support">
        <i class="suport_icon fa-thin fa-headset"></i>
        <i class="clase_supot fa-light fa-xmark"></i>
      </a>
      <div class="template-options-inner pt-3 pb-5">

        <a href="javascript:void(0)" class="template-options-btn mobile_suprt_close">
          <i class="clase_supot fa-light fa-xmark"></i>
        </a>
    
       <%-- <h4 class=" tx-dark mt-2 mb-2 px-3">Welcome to Support!</h4>--%>
 

   
          
          
          <div class="suport-row-wrapper">

            <div class="supportform w-100 pt-4 pt-md-0 px-3 pb-0">
         <%--       <div class="row row-sm">
                    <div class="col-12 mb-4">
                        <h4 class=" tx-dark text-center mt-2 mb-3">Welcome to Support!</h4>
                        <div class="input-group">
                            <div class="input_search_box">
                                <div style="display: none;">
                                    <input type="text" name="name_emailField" />
                                    <input type="password" name="passwordFiele" />
                                </div>
                                <asp:TextBox runat="server" autocomplete="off" CssClass="form-control" placeholder="Describe your issue" ID="txtsearch" ></asp:TextBox>
                                <a id="searchBtn" class="btn bd bd-l-0 tx-gray-600" href="javascript:void(0)"><i class="fa fa-search"></i></a>
                            </div>
                        </div>
                    </div>
                    </div>--%>     <!-- col-12 -->
                <div class="row row-sm">
                    <div class="col-12 mb-4">
                        <div class="d-flex justify-content-between align-items-center mt-3 mt-md-2 mb-3">
                            <h4 class=" tx-dark m-0">Support Center</h4>
                            <div class="supportopnav">
                                <a class="d-none" id="btnchat" href="javascript:void(0)"><i class="fa-solid fa-headset tx-20"></i>
                                    <span>Chat</span></a>
                                <a class="d-none" href=""><i class="fa-regular fa-circle-phone tx-20"></i><span>Call Now</span></a>
                                <a href="" class="ticketsload" data-href="/support/Tickets"><i class="fa-regular fa-files tx-20"></i><span>View Tickets</span></a>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input_search_box">
                                 <asp:TextBox runat="server" autocomplete="off" CssClass="form-control" placeholder="Describe your issue" ID="txtsearch" ></asp:TextBox>
                                <a id="searchBtn" class="btn bd bd-l-0 tx-gray-600" href="javascript:void(0)"><i class="fa fa-search"></i></a>
                            </div>
                        </div>
                    </div>
                    <!-- col-12 -->
                </div>
                <!--row-->
                <asp:PlaceHolder runat="server"  ID="plcevisible">
                    <div id="suportcontent" class="row row-sm dynamical_support">
                </div>
                </asp:PlaceHolder>


                
                <!--row-->
            </div>
         <asp:PlaceHolder runat="server"  ID="plcsupport">
                <div class="row showclass" runat="server" id="support" style="display:none">
            <div class="col-12">
              <p class="mb-2">Please provide your requirements here under.</p>
              <div class="form-group mg-b-10-force">
                <label class="form-control-label">Select Support Unit</label>
                <asp:DropDownList ID="selSupportUnit" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSSupportUnit" DataTextField="name" AppendDataBoundItems="true" DataValueField="id"><asp:ListItem Text="Select support unit" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSSupportUnit" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT su.id, name FROM support_unit su
        INNER JOIN support_type_unit st ON st.unitId=su.id WHERE st.typeId=@typeId and (TRIM(@search) LIKE '' OR name LIKE CONCAT('%', @search, '%')) "  OnSelecting="SDSSupportUnit_Selecting">
                        <SelectParameters>
                            <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                            <asp:Parameter Name="typeId" />
                        </SelectParameters>
                    </asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateSupport" ControlToValidate="selSupportUnit" ForeColor="Red" ErrorMessage="Select support unit" runat="server"></asp:RequiredFieldValidator>
              </div>
            </div><!-- col-12 -->

            <div class="col-12">
              <div class="form-group mg-b-10-force">
                <label class="form-control-label">What Support you need</label>
                <input type="text" style="display: none" />
                <input type="password" style="display: none" />
                  <asp:TextBox ID="txtSupportNeeded" MaxLength="250" runat="server" CssClass="form-control" placeholder="Support you need" autocomplete="off"/>
                  <asp:RequiredFieldValidator ValidationGroup="CreateSupport" ControlToValidate="txtSupportNeeded" ForeColor="Red" ErrorMessage="Support you need" runat="server"></asp:RequiredFieldValidator>
              </div>
            </div><!-- col-12 -->

            <div class="col-12">
              <div class="form-group mg-b-10-force">
                <label class="form-control-label">Brief your requirement</label>
                <%--<textarea rows="3" class="form-control" placeholder="Brief your requirement"></textarea>--%>
                  <asp:TextBox ID="txtRequirement" runat="server" CssClass="form-control" Height="50px" TextMode="MultiLine" placeholder="Brief your requirement"/>
                  <asp:RequiredFieldValidator ValidationGroup="CreateSupport" ControlToValidate="txtRequirement" ForeColor="Red" ErrorMessage="Brief your requirement" runat="server"></asp:RequiredFieldValidator>
              </div>
            </div><!-- col-12 -->
          </div><!--row-->
          <div class="row row-sm showclass" runat="server" id="supportsave" style="display:none">
            <%--<div class="col-12 col-lg-6 position-relative">

              <!-- actual upload which is hidden -->
              <input type="file" accept="image/x-png,image/gif,image/jpeg" id="actual-btn" hidden/>
              <!-- our custom upload button -->
              <label for="actual-btn" class="filetitle mb-0">Add Attachment</label>
              <!-- name of file chosen -->
              <span id="file-chosen" class="w-100 d-inline-block position-absolute mg-t-15 text-truncate" style="left: 10px;"></span>
            </div>--%>

              <div class="col-12 col-lg-6 position-relative">
                  <!-- actual upload which is hidden -->
                  <input type="file" accept="image/x-png,image/gif,image/jpeg" id="actual_btn" runat="server" onchange="updateFileChosenText();" />
                  <!-- our custom upload button -->
                  <%--<label for="actual_btn" class="filetitle mb-0">Add Attachment</label>--%>
                  <!-- name of file chosen -->
                  <span id="file-chosen" class="w-100 d-inline-block position-absolute mg-t-15 text-truncate" style="left: 10px;"></span>
              </div>

            <div class="col-12 col-lg-12 d-flex justify-content-lg-end">
              <div class="btn-sec d-lg-inline-block">
                <%--<button class="btn btn-primary">Submit</button>--%>
                  <asp:LinkButton runat="server" ID="lbtnSubmit" Text="Submit" OnClick="lbtnSubmit_Click" CssClass="btn btn-primary " ValidationGroup="CreateSupport"></asp:LinkButton>
                 <a href="javascript:void(0)" id="btnsupportback" onclick="Supportbackbuttonview()" class="btn btn-secondary backbtn">Back</a>
              </div>
            </div>
          </div>
           </asp:PlaceHolder>
         <asp:PlaceHolder Visible="false" ID="plcsupportticket" runat="server">
         <div class="ticketsucssmsg_wrap w-100 pt-4 px-3 pb-3">
            <div class="row">
              <div class="col-12 ticketsucssmsg">
                <marquee class="tx-16" behavior="scroll" direction="left" scrollamount="3">Your support ticket has been successfully posted. Please note your Ticket ID:<strong><asp:Label runat="server" ID="ltrticket"></asp:Label></strong>. Our Support team will revert you back shortly</marquee>
                <!-- <p class="mb-0 text-center tx-14 tx-dark">Your support ticket has been successfully posted.</p>
                <p class="mb-3 text-center tx-14 tx-dark">Please note your Ticket ID:XXXXX123.</p>
                <p class="mb-3 text-center tx-14 tx-dark">Our Support team will revert you back shortly</p>
                <p class="mb-2 text-center tx-14 tx-dark">Thank you</p> -->
                <div class="d-flex justify-content-center mt-3">
<%--                  <a href="javascript:void(0)" href="#" id="" class="btn btn-primary ">Create New Ticket</a>--%>
                    <asp:LinkButton runat="server" ID="btnsupport" OnClick="btnsupport_Click"  class="btn btn-primary" >Create New Ticket</asp:LinkButton>
                </div>
              </div>

              <div class="col-12 existingticket mt-4">
                <div class="d-flex flex-wrap justify-content-between w-100 mb-2">
                  <h6 class="tx-dark mb-0 mr-4">Active Ticket</h6>
                    <a href="" class="backbtn"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
                  <asp:CheckBox ID="chksupport" runat="server" Visible="false" CssClass="showtickt" Text="Show The Resolved Tickets" />                      
                </div>
                <div class="ticket_list_wrap d-flex flex-wrap w-100 border border-bottom-0">
                  <div class="ticket_list_head d-flex w-100 bg-light border-bottom">
                    <div class="col-6 p-2 border-right font-weight-bold">ticket/ Date</div>
                    <div class="col-6 p-2 font-weight-bold">Support Request</div>
                  </div><!--ticket_list_head-->
                 <asp:HiddenField ID="hidSupportId" ClientIDMode="Static" Value="0" runat="server" />
                  <asp:Repeater ID="rptsupport" DataSourceID="SDSsupport" runat="server">
                     <ItemTemplate>
                       <div class="ticket_list d-flex w-100 border-bottom">
                    <div class="col-6 p-2 border-right">
                      <span><%# Eval("ticketNumber")%>/</span>
                      <span><%# Eval("supportdate")%></span>
                    </div>
                    <div class="col-6 p-2">
                      <span><%# Eval("ticketTitle")%>:<%# Eval("ticketDescription")%></span>
                    </div>
                  </div>  
                     </ItemTemplate>                          
                  </asp:Repeater>  
                    <asp:PlaceHolder ID="plcemptytemplate" Visible="false" runat="server">
                        <div class="container">
                            <div class="row">
                                <div class="col">
                                    <div class="text-center">
                                        <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg" />
                                        <h6 class="mb-3">No records available</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </asp:PlaceHolder>
                     <asp:SqlDataSource runat="server" ID="SDSsupport" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" 
                        SelectCommand="SELECT DATE_FORMAT(createdOn, '%d %b %Y') AS supportdate,ticketNumber,ticketTitle,ticketDescription FROM `support_ticket` WHERE createdFrom=2 AND ticketStage <> 6 And CreatedBy=@Storegroupid and (@disabled = 0 || ticketStage = 6) LIMIT 5">
                         <SelectParameters>
                             <asp:ControlParameter ControlID="hidSupportId" PropertyName="Value" Name="Storegroupid" DefaultValue="0" />
                             <asp:ControlParameter Name="disabled" ControlID="chksupport" ConvertEmptyStringToNull="false" DefaultValue="0" Type="Int32" />
                         </SelectParameters>
                     </asp:SqlDataSource>
                </div><!--ticket_list_wrap-->                
              </div>
            </div>
          </div>
         </asp:PlaceHolder>            
        </div><!-- suport-row-wrapper --> 
          <div class="footer px-3 createsupprot">
          <div class="row row-sm">
            <div class="col-12 mt-4">
              <p class="mb-0">If you can't find what you need, <a class="tx-green btnsupportview" id="btnsupportview" href="javascript:void(0)">Submit a Ticket</a> for Alternate Help</p>
            </div><!--col-12-->
          </div><!--row-->
        </div>
      </div><!-- template-options-inner -->
    </div><!-- template-options-wrapper -->
    <link rel="stylesheet" href="/content/css/custom/swiper-bundle.min.css" />
    <script src="/content/js/custom/swiper-bundle.min.js"></script>
<script>
    function updateFileChosenText() {
        var fileName = document.getElementById('actual_btn').files[0].name;
        document.getElementById('file-chosen').innerText = fileName;
    }
    function showSupport(content, obj) {
        var loader = '<div class="d-flex align-items-center justify-content-center w-100"><div class="loader"></div></div>';

        $("#suportcontent").html(loader);
        if (obj)
            $(obj).removeClass('open_support');
        if (content && content != '')
            $("#suportcontent").load("/Support/SupportLanding.aspx");
        else
            $("#suportcontent").load(content);

    }

    function Supportbackbuttonview() {
        $(".dynamical_support").css("display", "block");
        $(".showclass").css("display", "none");
        $('.input_search_box').show();
        $('.createsupprot').show();
    }

    $(document).ready(function () {
        var loader = '<div class="d-flex align-items-center justify-content-center w-100"><div class="loader"></div></div>';

        // Click event for open_support button
        $(".open_support").click(function () {
            $("#suportcontent").html(loader);
            $(this).removeClass('open_support');
            $("#suportcontent").load("/Support/SupportLanding.aspx");
            //showSupport('', $(this));
        });

        // Search button click event
        $('#searchBtn').click(function () {
            var searchValue = $('#<%= txtsearch.ClientID %>').val();
            if (searchValue) {        
                 URL = '/Support/SupportLanding.aspx?search=' + encodeURIComponent(searchValue);
                 $("#suportcontent").load(URL);
             }
        });

        // Back button click event
        $('.backbtn').click(function (e) {
            e.preventDefault();
            $("#suportcontent").html(loader);
            $("#suportcontent").load("/Support/SupportLanding.aspx");
        }); 
        
        // Show support details button click event
        $('#<%= btnsupport.ClientID %>').click(function (e) {
            $(".showclass").css("display", "block");
        });

        // View Tickets link click event
        $('.ticketsload').click(function (e) {
            e.preventDefault(); // Prevent the default anchor behavior
            var href = $(this).data('href');  // Get the URL from the data-href attribute
            if (href) {
                $("#suportcontent").html(loader); // Show the loader
                $("#suportcontent").load(href); // Load the Tickets page
            } else {
                // Default action: load the SupportLanding page
                $("#suportcontent").html(loader);
                $("#suportcontent").load("/Support/SupportLanding.aspx");
            }
        });
        <asp:Literal ID="ltrLoadScript" runat="server"/>

    });
</script>

<style>
    .showtickt {
        display:flex;
        align-items:center;
    }
    .showtickt label{
        margin-left:8px;
        margin-bottom:0px
    }

    @media (max-width: 767px) {
        .supportopnav {
            gap:20px;
        }
        .supportform .supportopnav a span {
            display: none;
        }
        .mobile_suprt_close {
            top: 5px;
            right: 10px;
            width: 30px;
            height: 30px;
            background-color: #fffdfd;
            z-index: 9;
            font-size:21px;
            -webkit-box-shadow: 0px 0px 13px -4px rgba(0,0,0,0.40);
            -moz-box-shadow: 0px 0px 13px -4px rgba(0,0,0,0.40);
            box-shadow: 0px 0px 13px -4px rgba(0,0,0,0.40);
          }
    }
     
</style>

<%--<script>
    $(document).ready(function () {
        $('#fileAttach').change(function () {
            var filename = $('#fileAttach').val().split('\\').pop();
            $('#file-chosen').text(filename);
        });

        // Trigger file selection when the custom button is clicked
        $('.filetitle').click(function () {
            $('#fileAttach').click();
        });
    });
</script>--%>
