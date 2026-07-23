<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" Title="Manage ledger" CodeBehind="LedgerManagement.aspx.cs" Inherits="RetalineProAgent.Finance.LedgerCreation" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
        <a href="/Finance/Navigations/Costallocationandautoposting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Manage Ledger</h6>
     <p class="mb-0">You can see Manage Ledger here</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <section class="content">  
            <div class="row">
                <div class="col-12 col-lg-7 pb-3">
                    <div class="card m-0 h-100">

                        <div class="card-header shadow_top">
                            <div class="row row-sm">
                                <div class="col-12 col-lg-5">
                                    <div class="d-inline-block mb-2 mb-lg-0 mr-lg-3">
                                        <asp:Button runat="server" ID="btncreatenew" OnClick="btncreatenew_Click" CssClass="btn btn-primary AddVoucherBTN" Text="Create New"></asp:Button>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-7 d-flex">
                                    <div class="input-group input_search_box">
                                        <input type="text" style="display:none" />
                                    <input type="password" style="display:none" />
                                       <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton id="lbnSearch" runat="server" CssClass="input-group-append">
                                        <div class="btn bd bd-l-0 tx-gray-600" >
                                          <i class="fa fa-search"></i>
                              </div>
                                          </asp:LinkButton>
                                    </div>
                                    <script type="text/javascript">
                                        document.addEventListener("DOMContentLoaded", function () {
                                            document.getElementById('<%= txtSearch.ClientID %>').addEventListener("keydown", function (event) {
                                                if (event.key === "Enter") {
                                                    event.preventDefault(); // Prevent form submission
                                                    __doPostBack('<%= lbnSearch.UniqueID %>', '');
                                                }
                                            });
                                        });
                                    </script>
                                    <div class="btn-group ml-3">
                                        <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false" >
                                            <!-- Flilter -->
                                            <i class="fa fa-sliders"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="LedgerManagement?ntid=1">Asset</a></li>
                                            <li><a class="dropdown-item" href="LedgerManagement?ntid=2">Liability</a></li>
                                            <li><a class="dropdown-item" href="LedgerManagement?ntid=3">Income</a></li>
                                            <li><a class="dropdown-item" href="LedgerManagement?ntid=4">Expenditure</a></li>
                                            <li><a class="dropdown-item" href="LedgerManagement?ntid=5">Branch/Divisions</a></li>
                                            <li><a class="dropdown-item" href="LedgerManagement?">All Items</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">                            
                            <div class="table-responsive">
                                <table class="table table-bordered gridview_table" rules="all" id="cpMainContent_gvGroup"
                                    style="border-style: Solid; border-collapse: collapse;" cellspacing="0" border="1">
                                    <tbody>
                                        <tr class="border-top">
                                            <th width="50%" align="left">Ledger</th>
                                            <th width="25%" align="left">Parent Group</th>
                                            <th width="25%" align="right" style="text-align:right;">Closing Balance</th>
                                        </tr>
                                        <asp:ListView ID="lvledger" runat="server" DataSourceID="SDSledgerCreation" OnDataBound="lvledger_DataBound">
                                            <ItemTemplate>
                                                <tr>
                                                    <td align="left">
                                                        <asp:LinkButton ID="btnhide" dataid='<%# Eval("id") %>' Text='<%# Eval("name")%>' OnClick="btnhide_Click"  runat="server"></asp:LinkButton></td>
                                                    <td align="left"><%# Eval("groupname")%></td>
                                                    <td align="right"><%# (Eval("closing","{0:n}"))%></td>
                                                </tr>
                                            </ItemTemplate>
                                        </asp:ListView>                                       
                                    </tbody>
                                </table>
                                <asp:SqlDataSource runat="server" ID="SDSledgerCreation" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"
                                    SelectCommand="Select l.id,l.isSystem,l.name,g.name as groupname,(isnull((select top 1 closingBalance from transactions tr where l.id=tr.ledger_id  order by tr.id desc),0)) as closing,l.groups_id,(case when parent_id=0 then 'Primary Group' when parent_id in(select [id] from  [groups] where parent_id =0 )
                                    then 'Direct Parent Group' else 'Sub Group' end ) as GroupType,
                                    ac.nature from [ledger] l inner join groups g  on g.id=l.groups_id inner join [account_types] ac on g.account_types_id=ac.id where  (trim(@search) like '' or l.name like CONCAT('%', @search, '%')) AND (@nature <= 0 or account_types_id=@nature) order by name">
                                   <SelectParameters>
                                        <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                                        <asp:QueryStringParameter QueryStringField="ntid" Name="nature" DefaultValue="0" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
                            </div>
                              <div class="pagenation_listview p-3">
                        <asp:DataPager ID="DataPager1" runat="server" PageSize="10"
                            PagedControlID="lvledger">
                            <Fields>
                                <asp:NextPreviousPagerField PreviousPageText="<" FirstPageText="<<" ShowPreviousPageButton="false"
                                    ShowFirstPageButton="false" ShowNextPageButton="false" ShowLastPageButton="false"
                                    ButtonCssClass="btn btn-default" RenderNonBreakingSpacesBetweenControls="false" RenderDisabledButtonsAsLabels="false" />
                                <asp:NumericPagerField ButtonType="Link" CurrentPageLabelCssClass="btn btn-primary disabled" RenderNonBreakingSpacesBetweenControls="false"
                                    NumericButtonCssClass="btn btn-default" ButtonCount="5" NextPageText="..." NextPreviousButtonCssClass="btn btn-default" />
                                <asp:NextPreviousPagerField NextPageText=">" LastPageText=">>" ShowNextPageButton="false"
                                    ShowLastPageButton="false" ShowPreviousPageButton="false" ShowFirstPageButton="false"
                                    ButtonCssClass="btn btn-default" RenderNonBreakingSpacesBetweenControls="false" RenderDisabledButtonsAsLabels="false" />
                            </Fields>
                        </asp:DataPager>
                    </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-5 pb-3">

                    <div class="card m-0 h-100 overflow-hidden" style="overflow:hidden;">
                        <asp:PlaceHolder runat="server" Visible="false" ID="pnlnewledgers">
                            <div class="card-header border-0 d-flex align-items-center" style="min-height: 69px;">
                                <h5 class="mb-0">Create New Ledger</h5>
                            </div>
                            <div class="card-body pt-0" style="overflow:hidden;">
                                 <ul class="nav nav-tabs position-relative border-0 ledger-tab p-2 px-3" id="ledger-tab" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active p-0 border-0 mr-2 bg-transparent" id="details-tab" data-toggle="pill" href="#tab-content-details" role="tab" aria-controls="tab-content-details" aria-selected="true">Details</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link p-0 border-0 mx-2 bg-transparent" id="statutory-tab" data-toggle="pill" href="#tab-content-statutory" role="tab" aria-controls="tab-content-statutory" aria-selected="false">Statutory</a>
                      </li>
                      <asp:PlaceHolder ID="pnldebitor_update" Visible="false" runat="server">
                      <li class="nav-item">
                        <a class="nav-link p-0 border-0 mx-2 bg-transparent" id="address-tab" data-toggle="pill" href="#tab-content-address" role="tab" aria-controls="tab-content-address" aria-selected="false">Address</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link p-0 border-0 mx-2 bg-transparent" id="banking-tab" data-toggle="pill" href="#tab-content-banking" role="tab" aria-controls="tab-content-banking" aria-selected="false">Banking</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link p-0 border-0 mx-2 mr-0" id="taxation-tab" data-toggle="pill" href="#tab-content-taxation" role="tab" aria-controls="tab-content-taxation" aria-selected="false">Taxation</a>
                      </li>  
                                     </asp:PlaceHolder>
                    </ul>
                                <!--nav-tabs-->
                                <div class="tab-content" id="ledger-tabContent">
                                    <div class="tab-pane p-3 pb-0 fade show active" id="tab-content-details" role="tabpanel" aria-labelledby="details-tab">
                                        <div class="form-group mb-2">
                                            <label class="mb-0">Name of Ledger</label>
                                             <input type="text" style="display:none" />
                                    <input type="password" style="display:none" />
                                             <asp:TextBox runat="server" CssClass="form-control" ID="txtledgerName" autocomplete="off"></asp:TextBox>
                                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtledgerName" ForeColor="Red" ErrorMessage="Please give a ledger Name"></asp:RequiredFieldValidator>

                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="mb-0 w-100">Select Group</label>
                                             <asp:DropDownList ID="ddlgroup" DataSourceID="SDSgroupnew" CssClass="form-control select2" DataTextField="name" DataValueField="id" OnSelectedIndexChanged="ddlgroup_SelectedIndexChanged" AutoPostBack="true"  AppendDataBoundItems="true" runat="server">
                                                <asp:ListItem Text=" Select Group" Value=""></asp:ListItem>
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlgroup" ForeColor="Red" ErrorMessage="Please select group"></asp:RequiredFieldValidator>
                                            <asp:SqlDataSource ID="SDSgroupnew" runat="server" SelectCommand="select id,name from groups" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                        </div>                                        
                                        <div class="d-flex">
                                            <div class="form-group mr-1 mb-2">
                                                <label class="mb-0">Opening Balance</label>
                                                <asp:DropDownList ID="dlentrytpeupdate" CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="Debit" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Credit" Value="2"></asp:ListItem>
                                            </asp:DropDownList>
                                                 <asp:RequiredFieldValidator runat="server" ControlToValidate="dlentrytpeupdate" ForeColor="Red" ErrorMessage="Please select group"></asp:RequiredFieldValidator>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="mb-0"></label>
<%--                                                <input type="text" value="100000000000.00" class="form-control text-right" placeholder="Enter Amount" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                                                 <asp:TextBox runat="server" CssClass="form-control text-right" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"  ID="txtamount"></asp:TextBox>

                                            </div>
                                        </div>
                                         <h6 class="bold mb-2 d-none" style="text-decoration:none;">Cost Centre</h6>
                                         <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">Cost Centre Enable</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input navas" type="radio" runat="server" id="costcentreyes" name="EcommerceRadio">
                                                    <label for="<%= costcentreyes.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" runat="server" id="costcentreno" name="EcommerceRadio"> 
                                                    <label for="<%= costcentreno.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <asp:PlaceHolder ID="plcshow" runat="server" Visible="false">
                                        <div class="form-group mb-2 mt-3">
                          <label class="mb-0">Group Details</label>
                          <ol class="breadcrumb bg-white pl-0 pt-0">
                            <li class="breadcrumb-item"><asp:Label ID="ltlnatureofgroup" runat="server"></asp:Label></li>
                            <li class="breadcrumb-item ion-arrow-right-c"><asp:Label ID="ltlprimarygroup" runat="server"></asp:Label></li>
                            <li class="breadcrumb-item ion-arrow-right-c"><asp:Label ID="ltlmaingroup" runat="server"></asp:Label></li>
                            <li class="breadcrumb-item ion-arrow-right-c"><asp:Label ID="ltlsub" runat="server"></asp:Label></li>
                          </ol>
                          
                        </div>  
                         </asp:PlaceHolder>
                                    </div>
                                    <!--tab-pane-->
                                    <div class="tab-pane p-3 pb-0 fade" id="tab-content-statutory" role="tabpanel" aria-labelledby="statutory-tab">
                                        <h6 class="bold mb-3">GST (Goods and Service Tax)</h6>
                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">Applicable</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" runat="server"  type="radio" id="gstyes" name="GSTRadio">
                                                    <label for="<%= gstyes.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" runat="server" id="gstno" name="GSTRadio">
                                                    <label for="<%= gstno.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="mb-0">Type of Supply</label>
                                            <asp:DropDownList ID="dplsupplyservice" CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="Goods" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Service" Value="2"></asp:ListItem>
                                            </asp:DropDownList>
                                       <asp:RequiredFieldValidator runat="server" ControlToValidate="dplsupplyservice" ForeColor="Red" ErrorMessage="Please select group"></asp:RequiredFieldValidator>

                                        </div>
                                        <!-- <hr class="my-4"> -->
                                        <h6 class="mt-4 mb-3">IT-TDS (Income Tax Deducted at Source)</h6>

                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">Applicable</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">                                                   
                                                    <input class="custom-control-input" type="radio" runat="server" id="TDSyes" name="TDSRadio">
                                                    <label for="<%= TDSyes.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" runat="server" id="TDSno" name="TDSRadio">
                                                    <label for="<%= TDSno.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label class="mb-0">Nature of Payment</label>
                                             <asp:DropDownList ID="dplnaturofpayment" Enabled="false" CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="CHECK/DD" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="NEFT/RGS" Value="2"></asp:ListItem>
                                                 <asp:ListItem Text="Others" Value="3"></asp:ListItem>
                                                
                                            </asp:DropDownList>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="dplnaturofpayment" ForeColor="Red" ErrorMessage="Please select group"></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">TDS Deductible</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" type="radio" runat="server" id="TDSDeductibleyes" name="TDSDeductibleRadio">
                                                    <label for="<%= TDSDeductibleyes.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" id="TDSDeductibleno" runat="server" name="TDSDeductibleRadio">
                                                    <label for="<%= TDSDeductibleno.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Deductee Type</label>
                                            <asp:DropDownList ID="dpldeductee" CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="Artificial juridical Person" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Association of Persons" Value="2"></asp:ListItem>
                                                 <asp:ListItem Text="Body of individuals" Value="3"></asp:ListItem>
                                                <asp:ListItem Text="Company-Non Resident" Value="4"></asp:ListItem>
                                                <asp:ListItem Text="Company Resident" Value="5"></asp:ListItem>
                                                <asp:ListItem Text="Co-Operative Society-Non Resident" Value="6"></asp:ListItem>
                                                <asp:ListItem Text="Co-Operative Society-Resident" Value="7"></asp:ListItem>
                                                <asp:ListItem Text="Government" Value="8"></asp:ListItem>
                                                 <asp:ListItem Text="Individual/HUF-Non Resident" Value="9"></asp:ListItem>
                                                <asp:ListItem Text="Individual/HUF- Resident" Value="10"></asp:ListItem>
                                                <asp:ListItem Text="Local Authority" Value="11"></asp:ListItem>
                                                <asp:ListItem Text="Partnership firm" Value="12"></asp:ListItem>
                                            </asp:DropDownList>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="dpldeductee" ForeColor="Red" ErrorMessage="Please select group"></asp:RequiredFieldValidator>
                                        </div>


                                    </div>
                                    <!--tab-pane-->
                                    <asp:PlaceHolder Visible="false" runat="server" ID="plccreditor">
                                    <div class="tab-pane pt-3 fade" id="tab-content-address" role="tabpanel" aria-labelledby="address-tab">

                                        <div class="form-group">
                          <label class="mb-0">Legal Name</label>
                           <asp:TextBox runat="server" ID="txtlegalname" CssClass="form-control"></asp:TextBox> 
                         <asp:RequiredFieldValidator runat="server" ControlToValidate="txtlegalname" ForeColor="Red" ErrorMessage="Please give the Name"></asp:RequiredFieldValidator>
                        </div>

                        <div class="form-group">
                          <label class="mb-0">Address</label>
                         <asp:TextBox runat="server" ID="txtaddress" CssClass="form-control"></asp:TextBox> 
                          <asp:RequiredFieldValidator runat="server" ControlToValidate="txtaddress" ForeColor="Red" ErrorMessage="Please give a address"></asp:RequiredFieldValidator>
                        </div>

                        <div class="d-flex row">
                          <div class="form-group col-md-6">
                            <label class="mb-0">State</label>
                            <asp:DropDownList ID="ddlstate" DataSourceID="SDSstate" CssClass="form-control select2" DataTextField="stateName" DataValueField="stateId"  AutoPostBack="true"  AppendDataBoundItems="true" runat="server">
                                <asp:ListItem Text=" Select state" Value=""></asp:ListItem>
                            </asp:DropDownList>
                              <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlgroup" ForeColor="Red" ErrorMessage="Please select group"></asp:RequiredFieldValidator>
                              <asp:SqlDataSource ID="SDSstate" runat="server" SelectCommand="select stateId,stateName from state_or_union_territory" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                          </div>
  
                          <div class="form-group col-md-6">
                            <label class="mb-0">Country</label>
                            <asp:DropDownList ID="ddlcountry"  CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="India" Value="1"></asp:ListItem>                                               
                                            </asp:DropDownList>
                          </div>
                        </div>

                        <div class="d-flex row">
                          <div class="form-group col-md-6">
                            <label class="mb-0">Pincode</label>
                            <asp:TextBox runat="server" ID="txtpincode" CssClass="form-control"></asp:TextBox>
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtpincode" ForeColor="Red" ErrorMessage="Please give a Pincode"></asp:RequiredFieldValidator>
                          </div>
                          <div class="form-group col-md-6">
                            <label class="mb-0">Website</label>
                           <asp:TextBox runat="server" ID="txtwebsite" CssClass="form-control"></asp:TextBox> 
                           <asp:RequiredFieldValidator runat="server" ControlToValidate="txtwebsite" ForeColor="Red" ErrorMessage="Please give a website"></asp:RequiredFieldValidator>
                          </div>
                        </div>                       

                      <div class="form-group">
                          <label class="mb-0">Phone No</label>
                           <asp:TextBox runat="server" ID="txtPhoneNo" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"  CssClass="form-control"></asp:TextBox> 
<%--                        <input type="text" value="" class="form-control" placeholder="Phone No" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                        </div> 

                        <div class="form-group">
                          <label class="mb-0">Contact Person</label>
                         <asp:TextBox runat="server" ID="txtcontactperson" CssClass="form-control"></asp:TextBox> 
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtcontactperson" ForeColor="Red" ErrorMessage="Please give a contact Person"></asp:RequiredFieldValidator>
                        </div>

                        <div class="d-flex row">
                          <div class="form-group col-md-6">
                            <label class="mb-0">Mobile No</label>
                              <asp:TextBox runat="server" ID="txtmobile_no" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"  CssClass="form-control"></asp:TextBox> 
                              <asp:RequiredFieldValidator runat="server" ControlToValidate="txtmobile_no" ForeColor="Red" ErrorMessage="Please give a mobile number"></asp:RequiredFieldValidator>
                          </div>
                          <div class="form-group col-md-6">
                            <label class="mb-0">Email</label>
                             <asp:TextBox runat="server" ID="txtEmail"   CssClass="form-control"></asp:TextBox> 
                               <asp:RequiredFieldValidator runat="server" ControlToValidate="txtEmail"  ForeColor="Red" ErrorMessage="Please give a email"></asp:RequiredFieldValidator>
                          </div>
                          
                        </div>
                                    </div>
                                    <!--tab-pane-->
                                    <div class="tab-pane pt-3 fade" id="tab-content-banking" role="tabpanel" aria-labelledby="banking-tab">

                                        <div class="form-group">
                                            <label class="mb-0">Transaction Type</label>
                                             <asp:DropDownList ID="ddltranscationtype"  CssClass="form-control" runat="server">                                              
                                                <asp:ListItem Enabled="true" Text="Enter type" Value=""></asp:ListItem>
                                                <asp:ListItem Text="CHECK/DD" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="NEFT/RGS" Value="2"></asp:ListItem>
                                                 <asp:ListItem Text="Others" Value="3"></asp:ListItem>
                                            </asp:DropDownList>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Account Holder</label>
                              <asp:TextBox runat="server" ID="txtaccountholder" CssClass="form-control"></asp:TextBox>
                             <asp:RequiredFieldValidator runat="server" ControlToValidate="txtaccountholder"  ForeColor="Red" ErrorMessage="Please give accountholderName"></asp:RequiredFieldValidator>

                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Account Number</label>
                                         <asp:TextBox runat="server" ID="txtaccountnumber" CssClass="form-control"></asp:TextBox> 
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtaccountnumber"  ForeColor="Red" ErrorMessage="Please give accountnumber"></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">IFSC Code</label>
                                          <asp:TextBox runat="server" ID="txtIFSC" CssClass="form-control"></asp:TextBox> 
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="txtIFSC"  ForeColor="Red" ErrorMessage="Please give a IFSC"></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Bank Name</label>
                                   <asp:TextBox runat="server" ID="txtbankname" CssClass="form-control"></asp:TextBox> 
                                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtbankname"  ForeColor="Red" ErrorMessage="Please give a bankname"></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Branch</label>
                                             <asp:TextBox runat="server" ID="txtbranch" CssClass="form-control"></asp:TextBox> 
                                              <asp:RequiredFieldValidator runat="server" ControlToValidate="txtbranch"  ForeColor="Red" ErrorMessage="Please give a Branch"></asp:RequiredFieldValidator>
                                        </div>

                                    </div>
                                    <!--tab-pane-->
                                    <div class="tab-pane pt-3 fade" id="tab-content-taxation" role="tabpanel" aria-labelledby="taxation-tab">

                                        <div class="form-group">
                                            <label class="mb-0">PAN/IT No</label>
                                            <asp:TextBox runat="server" ID="txtPAN" CssClass="form-control"></asp:TextBox>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtPAN"  ForeColor="Red" ErrorMessage="Please give a PAN/IT No "></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">GST Registration Type</label>
                                           <asp:DropDownList ID="ddlgstregistrationtype" CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="Unknown" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Composition" Value="2"></asp:ListItem>
                                                <asp:ListItem Text="Consumer" Value="3"></asp:ListItem>
                                               <asp:ListItem Text="Unregistered" Value="4"></asp:ListItem>
                                            </asp:DropDownList>
                                           <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlgstregistrationtype" ForeColor="Red" ErrorMessage="Please select group"></asp:RequiredFieldValidator>

                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">GSTIN/UIN</label>
                                             <asp:TextBox runat="server" ID="txtGSTIN" CssClass="form-control"></asp:TextBox> 
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="txtGSTIN"  ForeColor="Red" ErrorMessage="Please give a GSTIN/UIN No "></asp:RequiredFieldValidator>

                                        </div>

                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">Assessee of Other Territory</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" type="radio" runat="server" id="Territoryyes" name="TerritoryRadio">
                                                    <label for="<%= Territoryyes.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" runat="server" id="Territoryno" name="TerritoryRadio">
                                                    <label for="<%= Territoryno.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">e-Commerce Operator</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" type="radio" runat="server" id="Ecommerceyes" name="EcommerceRadio">
                                                    <label for="<%= Ecommerceyes.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" runat="server" id="Ecommerceno" name="EcommerceRadio">
                                                    <label for="<%= Ecommerceno.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">Deemed exporter for purchases</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" type="radio" runat="server" id="purchasesyes" name="purchasesRadio">
                                                    <label for="<%= purchasesyes.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" id="purchasesno" runat="server" name="purchasesRadio">
                                                    <label for="<%= purchasesno.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Party Type</label>
                                            <asp:DropDownList ID="ddlpartytype" CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value=""></asp:ListItem>
                                                <asp:ListItem Text="Not Applicable" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Deemed Export" Value="2"></asp:ListItem>
                                                <asp:ListItem Text="Embassy/UN Body" Value="3"></asp:ListItem>
                                               <asp:ListItem Text="SEZ" Value="4"></asp:ListItem>
                                            </asp:DropDownList>
                                              <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlpartytype" ForeColor="Red" ErrorMessage="Please select group"></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">Whether Transporter</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" type="radio" runat="server" id="Transporteryes" name="TransporterRadio">
                                                    <label for="<%= Transporteryes.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" id="Transportereno" runat="server" name="TransporterRadio">
                                                    <label for="<%= Transportereno.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Transporter ID</label>
                                           <asp:TextBox runat="server" ID="txttranspoter_id" CssClass="form-control"></asp:TextBox>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txttranspoter_id"  ForeColor="Red" ErrorMessage="Please give a Transporter ID "></asp:RequiredFieldValidator>
                                        </div>

                                    </div>
                                    <!--tab-pane-->
                                      </asp:PlaceHolder>
                                </div>
                                <!--"tab-content-->
                            </div>
                            <!--card body-->
                            <div class="card-footer bg-white">
                                <div class="form-group mb-0 float-right">
                                   <%-- <a class="btn btn-success " href="">Save</a>--%>
                                    <asp:LinkButton runat="server" ID="btnsave" CssClass="btn btn-primary mx-1 py-1" OnClick="btnsave_Click" >Save</asp:LinkButton>
                                     <asp:LinkButton runat="server" ID="btncanel_update" CssClass="btn btn-secondary mx-1 py-1" CausesValidation="false" OnClick="btncanel_update_Click">Cancel</asp:LinkButton>
                                    
                                </div>
                                 <asp:Label ID="lbledgermgmt" runat="server"></asp:Label>
                            </div>
                        </asp:PlaceHolder>
                        <asp:PlaceHolder runat="server" Visible="true" ID="pnlledgerdetailes">
                            <div class="card-header shadow_top border-0 d-flex align-items-center" style="min-height: 69px;">
                                <h5 class="mb-0">Create New Ledger</h5>
                            </div>

                            <div class="card-body pt-0">


                                <ul class="nav nav-tabs position-relative border-0 ledger-tab p-2 px-3" id="ledger-tab_edit" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active p-0 border-0 mr-2 bg-transparent" id="details-tab_edit" data-toggle="pill" href="#tab-content-details_edit" role="tab" aria-controls="tab-content-details_edit" aria-selected="true">Details</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link p-0 border-0 mx-2 bg-transparent" id="statutory-tab_edit"  data-toggle="pill" href="#tab-content-statutory_edit" role="tab" aria-controls="tab-content-statutory_edit" aria-selected="false">Statutory</a>
                      </li>
                      <asp:PlaceHolder runat="server" Visible="false" ID="pnlcredit_update">
                      <li class="nav-item">
                        <a class="nav-link p-0 border-0 mx-2 bg-transparent" id="address-tab_edit"  data-toggle="pill" href="#tab-content-address_edit" role="tab" aria-controls="tab-content-address_edit" aria-selected="false">Address</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link p-0 border-0 mx-2 bg-transparent" id="banking-tab_edit" data-toggle="pill" href="#tab-content-banking_edit" role="tab" aria-controls="tab-content-banking_edit" aria-selected="false">Banking</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link p-0 border-0 mx-2 mr-0" id="taxation-tab_edit" data-toggle="pill" href="#tab-content-taxation_edit" role="tab" aria-controls="tab-content-taxation_edit" aria-selected="false">Taxation</a>
                      </li>
                        </asp:PlaceHolder>
                    </ul><!--nav-tabs-->
                                <!--nav-tabs-->

                                <div class="tab-content" id="ledger-tabContentu">
                                    <div class="tab-pane pt-3 fade show active" id="tab-content-details_edit" role="tabpanel" aria-labelledby="details-tab">
                                         <asp:HiddenField ID="hidledger" ClientIDMode="Static" Value="0" runat="server" />
                                        <div class="table-responsive border-top">
                                            <table class="table table-bordered gridview_table">
                                                <tbody>
                                                    <tr class="border-top" >
                                                        <td width="140px" class="font-weight-bold">Name of Ledger</td>
                                                        <td><asp:Literal ID="ltrnameledger" runat="server"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="140px" class="font-weight-bold">Group</td>
                                                        <td><asp:Literal ID="ltrgroup" runat="server"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="140px" class="font-weight-bold">Opening Balance</td>
                                                        <td><asp:Literal ID="ltrOpening" runat="server"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="140px" class="font-weight-bold">Cost Centre</td>
                                                        <td><asp:Literal ID="ltrcostcentre" runat="server"></asp:Literal></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                    <!--tab-pane-->


                                    <div class="tab-pane pt-3 fade"  id="tab-content-statutory_edit" role="tabpanel" aria-labelledby="statutory-tab">

                                        <div class="table-responsive border-top">
                                            <table class="table table-bordered gridview_table">
                                                <tbody class="border-top">
                                                    <tr >
                                                        <th colspan="2" >GST (Goods and Service Tax)</th>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Applicable</td>
                                                        <td><asp:Literal runat="server" ID="ltrapplicable"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Type of Supply</td>
                                                        <td><asp:Literal runat="server" ID="ltrtypeofsupply"></asp:Literal></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="table-responsive border-top">
                                            <table class="table table-bordered gridview_table">
                                                <tbody class="border-top">
                                                    <tr>
                                                        <th colspan="2" >IT-TDS (Income Tax Deducted at Source)</th>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Applicable</td>
                                                        <td><asp:Literal runat="server" ID="ltrlapplicable"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Nature of Payment</td>
                                                        <td><asp:Literal runat="server" ID="ltrnatureofpayment"></asp:Literal> </td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">TDS Deductible</td>
                                                        <td><asp:Literal runat="server" ID="ltrTDSdeductible"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Deductee Type</td>
                                                        <td><asp:Literal runat="server" ID="ltrdeducteetypr"></asp:Literal></td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>


                                    </div>
                                    <!--tab-pane-->
                                    <asp:PlaceHolder runat="server" Visible="false" ID="pnlde_update">
                                    <div class="tab-pane pt-3 fade" id="tab-content-address_edit" role="tabpanel" aria-labelledby="address-tab">

                                        <div class="table-responsive border-top">
                                            <table class="table table-bordered gridview_table">
                                                <tbody class="border-top">
                                                    <tr>
                                                        <th colspan="2" >Name and Address</th>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Name</td>
                                                        <td><asp:Literal runat="server" ID="ltrname"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Address</td>
                                                        <td><asp:Literal runat="server" ID="ltradress"></asp:Literal><br>
                                                            <asp:Literal runat="server" ID="ltrState"></asp:Literal>
                                                            <br>
                                                            <asp:Literal runat="server" ID="ltrPincode"></asp:Literal><br>
                                                            <asp:Literal runat="server" ID="ltrCountry"></asp:Literal>
                                                      </td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Phone No</td>
                                                        <td><asp:Literal runat="server" ID="ltrphoneno"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Website</td>
                                                        <td><asp:Literal runat="server" ID="ltrwebsite"></asp:Literal></td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="table-responsive border-top">
                                            <table class="table table-bordered gridview_table">
                                                <tbody class="border-top">
                                                    <tr>
                                                        <th colspan="2">Contact Details</th>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Contact Person</td>
                                                        <td><asp:Literal runat="server" ID="ltrcontactdetails"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Mobile No</td>
                                                        <td><asp:Literal runat="server" ID="ltrmobileno"></asp:Literal></td>
                                                    </tr>                                                    
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Email</td>
                                                        <td><asp:Literal runat="server" ID="ltremail"></asp:Literal></td>
                                                    </tr>                                                    

                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                    <!--tab-pane-->
                                    <div class="tab-pane pt-3 fade" id="tab-content-banking_edit" role="tabpanel" aria-labelledby="banking-tab">

                                        <div class="table-responsive border-top">
                                            <table class="table table-bordered">
                                                <tbody class="border-top">
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Transaction Type</td>
                                                        <td><asp:Literal runat="server" ID="ltrtransactiotype"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Account Holder</td>
                                                        <td><asp:Literal runat="server" ID="ltraccountholder"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Account Number</td>
                                                        <td><asp:Literal runat="server" ID="ltraccountnumber"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">IFSC Code</td>
                                                        <td><asp:Literal runat="server" ID="ltrifsccode_update"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Bank Name</td>
                                                        <td><asp:Literal runat="server" ID="ltrbankname"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Branch</td>
                                                        <td><asp:Literal runat="server" ID="ltrbranch"></asp:Literal></td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                    <!--tab-pane-->
                                    <div class="tab-pane pt-3 fade" id="tab-content-taxation_edit" role="tabpanel" aria-labelledby="taxation-tab">

                                        <div class="table-responsive border-top">
                                            <table class="table table-bordered">
                                                <tbody class="border-top">
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">PAN/IT No</td>
                                                        <td><asp:Literal runat="server" ID="ltrpan"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">GST Registration Type</td>
                                                        <td><asp:Literal runat="server" ID="ltrgsttype"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">GSTIN/UIN</td>
                                                        <td><asp:Literal runat="server" ID="ltrgstin"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Assessee of Other Territory</td>
                                                        <td><asp:Literal runat="server" ID="ltrassessofother"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">e-Commerce Operator</td>
                                                        <td><asp:Literal runat="server" ID="ltrcommerce"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Deemed exporter for purchases</td>
                                                        <td><asp:Literal runat="server" ID="ltrdeemed"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Party Type</td>
                                                        <td><asp:Literal runat="server" ID="ltrpartype"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Whether Transporter</td>
                                                        <td><asp:Literal runat="server" ID="ltrwhethertranspoter"></asp:Literal></td>
                                                    </tr>

                                                    <tr>
                                                        <td width="170px" class="font-weight-bold">Transporter ID</td>
                                                        <td><asp:Literal runat="server" ID="ltrtranspoterid"></asp:Literal></td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                    <!--tab-pane-->
                                        </asp:PlaceHolder>
                                </div>
                                <!--"tab-content-->
                            </div>
                            <!--card body-->
                            <div class="card-footer bg-white">
                                <div class="form-group mb-0 float-right">
                                   <asp:LinkButton runat="server" ID="btnedit" Enabled="false"  OnClick="btnedit_Click" CssClass="btn btn-primary text-white" CommandName="Edit">Edit</asp:LinkButton>

                                </div>
                            </div>
                        </asp:PlaceHolder>
                        <asp:PlaceHolder runat="server" Visible="false" ID="pnlledger_updetes">
                           <div class="card-header border-0 d-flex align-items-center">
                                <h5 class="mb-0">Create New Ledger</h5>
                            </div>
                            <div class="card-body pt-0" >
                                 <ul class="nav nav-tabs position-relative border-0 ledger-tab p-2 pb-0" id="ledger-tab_update" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active p-0 border-0 mr-2" id="details-tab_update" data-toggle="pill" href="#tab-content-details_update" role="tab" aria-controls="tab-content-details" aria-selected="true">Details</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link p-0 border-0 mx-2" id="statutory-tab_update" data-toggle="pill" href="#tab-content-statutory_update" role="tab" aria-controls="tab-content-statutory" aria-selected="false">Statutory</a>
                      </li>
                        <asp:PlaceHolder ID="pnlvisib_update" Visible="false" runat="server">
                      <li class="nav-item">
                        <a class="nav-link p-0 border-0 mx-2" id="address-tab_update" data-toggle="pill" href="#tab-content-address_update" role="tab" aria-controls="tab-content-address" aria-selected="false">Address</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link p-0 border-0 mx-2" id="banking-tab_update" data-toggle="pill" href="#tab-content-banking_update" role="tab" aria-controls="tab-content-banking" aria-selected="false">Banking</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link p-0 border-0 mx-2 mr-0" id="taxation-tab_update" data-toggle="pill" href="#tab-content-taxation_update" role="tab" aria-controls="tab-content-taxation" aria-selected="false">Taxation</a>
                      </li>
                       </asp:PlaceHolder>
                    </ul>
                                <!--nav-tabs-->
                                <div class="tab-content" id="ledger-tabConten_updatet">
                                    <div class="tab-pane p-3 pb-0 fade show active" id="tab-content-details_update" role="tabpanel" aria-labelledby="details-tab">
                                        <div class="form-group">
                                            <label class="mb-0">Name of Ledger</label>
                                          <asp:TextBox runat="server" CssClass="form-control" ID="txtledgerupdate"></asp:TextBox>
                                           <asp:RequiredFieldValidator runat="server" ControlToValidate="txtledgerupdate" ForeColor="Red" ErrorMessage="Please give a ledger name"></asp:RequiredFieldValidator>

                                        </div>
                                        <div class="form-group">
                                            <label class="mb-0 w-100">Select Group</label>                                           
                                            <asp:DropDownList ID="selGroup" DataSourceID="SDSgroup" CssClass="form-control select2" DataTextField="name" DataValueField="id" OnSelectedIndexChanged="selGroup_SelectedIndexChanged"  AppendDataBoundItems="true" runat="server">
                                                <asp:ListItem Text=" Select Group" Value=""></asp:ListItem>
                                            </asp:DropDownList>
                                           <asp:RequiredFieldValidator runat="server" ControlToValidate="selGroup" ForeColor="Red" ErrorMessage="Please select group"></asp:RequiredFieldValidator>

                                            <asp:SqlDataSource ID="SDSgroup" runat="server" SelectCommand="select id,name from groups" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                        </div>                                        
                                        <div class="d-flex">
                                            <div class="form-group mr-1">
                                                <label class="mb-0">Opening Balance</label>
                                                <asp:DropDownList ID="ddlnaturetupeupdate" Enabled="false" CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="Debit" Value="1"></asp:ListItem>                                                
                                                <asp:ListItem Text="Credit" Value="2"></asp:ListItem>
                                            </asp:DropDownList>
                                                <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlnaturetupeupdate" ForeColor="Red" ErrorMessage="Please select group"></asp:RequiredFieldValidator>

                                            </div>
                                            <div class="form-group ">
                                                <label class="mb-0"></label>
                                                  <asp:TextBox runat="server" Enabled="false" CssClass="form-control text-right" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"  ID="txtAmountUpdate"></asp:TextBox>
<%--                                                <input type="text" value="100000000000.00" class="form-control text-right" placeholder="Enter Amount" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                                            </div>
                                        </div>
                                         <h6 class="bold mb-3 d-none" style="text-decoration:none;" >Cost Centre</h6>
                                         <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">Cost Centre Enable</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input navas" type="radio" runat="server" id="costcentreyes_update" name="EcommerceRadio">
                                                    <label for="<%= costcentreyes_update.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" runat="server" id="costcentreno_update" name="EcommerceRadio"> 
                                                    <label for="<%= costcentreno_update.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-0 mt-3">
                                            <label class="mb-0">Group Details</label>
                                            <ol class="breadcrumb bg-white pl-0 pt-0">
                                                <li class="breadcrumb-item"><asp:Literal ID="ltrnature" runat="server"></asp:Literal></li>
                                                <li class="breadcrumb-item ion-arrow-right-c"><asp:Literal ID="ltrprimary" runat="server"></asp:Literal></li>
                                                <li class="breadcrumb-item ion-arrow-right-c"><asp:Literal ID="ltrmain" runat="server"></asp:Literal></li>
                                                <li class="breadcrumb-item ion-arrow-right-c"><asp:Literal ID="ltrsubgroup" runat="server"></asp:Literal></li>
                                            </ol>
                                        </div>
                                    </div>
                                    <!--tab-pane-->
                                    <div class="tab-pane p-3 pb-0 fade" id="tab-content-statutory_update" role="tabpanel" aria-labelledby="statutory-tab">
                                        <h6 class="bold mb-3">GST (Goods and Service Tax)</h6>
                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">Applicable</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" type="radio" runat="server" id="gstyes_upadete" name="GSTRadio">
                                                    <label for="<%= gstyes_upadete.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" runat="server" id="gstno_updete" name="GSTRadio">
                                                    <label for="<%= gstno_updete.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="mb-0">Type of Supply</label>
                                             <asp:DropDownList ID="ddltypeofsupply" CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="Goods" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Service" Value="2"></asp:ListItem>
                                            </asp:DropDownList>
                                         <asp:RequiredFieldValidator runat="server" ControlToValidate="ddltypeofsupply"  ForeColor="Red" ErrorMessage="Please Select a Type of Supply "></asp:RequiredFieldValidator>

                                        </div>
                                        <!-- <hr class="my-4"> -->
                                        <h6 class="mt-4 mb-3">IT-TDS (Income Tax Deducted at Source)</h6>

                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">Applicable</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" type="radio" runat="server" id="TDSyes_update" name="TDSRadio">
                                                    <label for="<%= TDSyes_update.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" runat="server" id="TDSno_update" name="TDSRadio">
                                                    <label for="<%= TDSno_update.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group  mb-0">
                                            <label class="mb-0">Nature of Payment</label>
                                            <asp:DropDownList ID="ddlnature_update" Enabled="false" CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="test" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="test" Value="2"></asp:ListItem>
                                                 <asp:ListItem Text="test" Value="3"></asp:ListItem>                                                
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlnature_update"  ForeColor="Red" ErrorMessage="Please Select a Nature of Payment"></asp:RequiredFieldValidator>

                                        </div>

                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">TDS Deductible</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" type="radio" runat="server" id="TDSDeductibleyes_update" name="TDSDeductibleRadio">
                                                    <label for="<%= TDSDeductibleyes_update.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" runat="server" id="TDSDeductibleno_update" name="TDSDeductibleRadio">
                                                    <label for="<%= TDSDeductibleno_update.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Deductee Type</label>
                                           <asp:DropDownList ID="ddlducteetype" CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="Artificial juridical Person" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Association of Persons" Value="2"></asp:ListItem>
                                                 <asp:ListItem Text="Body of individuals" Value="3"></asp:ListItem>
                                                <asp:ListItem Text="Company-Non Resident" Value="4"></asp:ListItem>
                                                <asp:ListItem Text="Company Resident" Value="5"></asp:ListItem>
                                                <asp:ListItem Text="Co-Operative Society-Non Resident" Value="6"></asp:ListItem>
                                                <asp:ListItem Text="Co-Operative Society-Resident" Value="7"></asp:ListItem>
                                                <asp:ListItem Text="Government" Value="8"></asp:ListItem>
                                                 <asp:ListItem Text="Individual/HUF-Non Resident" Value="8"></asp:ListItem>
                                                <asp:ListItem Text="Individual/HUF- Resident" Value="9"></asp:ListItem>
                                                <asp:ListItem Text="Local Authority" Value="10"></asp:ListItem>
                                                <asp:ListItem Text="Partnership firm" Value="11"></asp:ListItem>
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlducteetype"  ForeColor="Red" ErrorMessage="Please Select a Deductee Type"></asp:RequiredFieldValidator>
                                        </div>


                                    </div>
                                    <!--tab-pane-->
                                    <asp:PlaceHolder ID="pnlvisible_up" Visible="false" runat="server">
                          <div class="tab-pane p-3 pb-0 fade" id="tab-content-address_update" role="tabpanel" aria-labelledby="address-tab">
                            <div class="form-group">
                          <label class="mb-0">Legal Name</label>
                         <asp:TextBox runat="server" ID="txtlegalname_update" CssClass="form-control"></asp:TextBox>
                         <asp:RequiredFieldValidator runat="server" ControlToValidate="txtlegalname_update"  ForeColor="Red" ErrorMessage="Please give a Legal Name "></asp:RequiredFieldValidator>
                        </div>

                        <div class="form-group">
                          <label class="mb-0">Address</label>
                          <asp:TextBox runat="server" ID="txtaddress_update" CssClass="form-control"></asp:TextBox>
                           <asp:RequiredFieldValidator runat="server" ControlToValidate="txtaddress_update"  ForeColor="Red" ErrorMessage="Please give a Legal Address "></asp:RequiredFieldValidator>
                        </div>

                        <div class="d-flex row">
                          <div class="form-group col-md-6">
                            <label class="mb-0">State</label>
                            <asp:DropDownList ID="ddlstete_update" DataSourceID="SDSstate_update" CssClass="form-control select2" DataTextField="stateName" DataValueField="stateId"  AutoPostBack="true"  AppendDataBoundItems="true" runat="server">
                                <asp:ListItem Text=" Select state" Value=""></asp:ListItem>
                            </asp:DropDownList>
                              <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlstete_update" ForeColor="Red" ErrorMessage="Please select state"></asp:RequiredFieldValidator>
                              <asp:SqlDataSource ID="SDSstate_update" runat="server" SelectCommand="select stateId,stateName from state_or_union_territory" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                          </div>
                         
                          <div class="form-group col-md-6">
                            <label class="mb-0">Country</label>
                           <asp:DropDownList ID="ddlcountry_update"  CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="India" Value="1"></asp:ListItem>                                               
                                            </asp:DropDownList>
                              <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlcountry_update"  ForeColor="Red" ErrorMessage="Please Select a Country"></asp:RequiredFieldValidator>
                          </div>
                            </div>
                                        <div class="d-flex row">
                          <div class="form-group col-md-6">
                            <label class="mb-0">Pincode</label>
                           <asp:TextBox runat="server" ID="txtpincode_update" CssClass="form-control"></asp:TextBox>
                              <asp:RequiredFieldValidator runat="server" ControlToValidate="txtpincode_update"  ForeColor="Red" ErrorMessage="Please give a Legal Address "></asp:RequiredFieldValidator>
                          </div>
                          <div class="form-group col-md-6">
                            <label class="mb-0">Website</label>
                            <asp:TextBox runat="server" ID="txtwebsite_update" CssClass="form-control"></asp:TextBox>
                              <asp:RequiredFieldValidator runat="server" ControlToValidate="txtwebsite_update"  ForeColor="Red" ErrorMessage="Please give a Website "></asp:RequiredFieldValidator>
                          </div>
                               </div>  
                               <div class="form-group">
                          <label class="mb-0">Phone No</label>
                           <asp:TextBox runat="server" ID="txtphonenumber_update" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"  CssClass="form-control"></asp:TextBox> 
                     <%--  <input type="text" value="" class="form-control" placeholder="Phone No" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtphonenumber_update"  ForeColor="Red" ErrorMessage="Please give a Phone No "></asp:RequiredFieldValidator>
                               </div> 
                              <div class="form-group">
                          <label class="mb-0">Contact Person</label>
                          <asp:TextBox runat="server" ID="txtcontactperson_update" CssClass="form-control"></asp:TextBox>
                              <asp:RequiredFieldValidator runat="server" ControlToValidate="txtcontactperson_update"  ForeColor="Red" ErrorMessage="Please give a Contact Person "></asp:RequiredFieldValidator>
                        </div>
                                            <div class="d-flex row">
                          <div class="form-group col-md-6">
                            <label class="mb-0">Mobile No</label>
                              <asp:TextBox runat="server" ID="txtmobileno_update" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"  CssClass="form-control"></asp:TextBox>
                              <asp:RequiredFieldValidator runat="server" ControlToValidate="txtmobileno_update"  ForeColor="Red" ErrorMessage="Please give a Mobile No"></asp:RequiredFieldValidator>
                          </div>
                          <div class="form-group col-md-6">
                            <label class="mb-0">Email</label>
                            <asp:TextBox runat="server" ID="txtemail_update" CssClass="form-control"></asp:TextBox>
                              <asp:RequiredFieldValidator runat="server" ControlToValidate="txtemail_update"  ForeColor="Red" ErrorMessage="Please give a Email "></asp:RequiredFieldValidator>
                          </div>
                          
                        </div>
                       
                        </div>
                                   
                                    <!--tab-pane-->
                                    <div class="tab-pane p-3 pb-0 fade" id="tab-content-banking_update" role="tabpanel" aria-labelledby="banking-tab">
                                        <div class="form-group">
                                            <label class="mb-0">Transaction Type</label>
                                             <asp:DropDownList ID="ddltransactiontype"  CssClass="form-control" runat="server">                                              
                                                <asp:ListItem Enabled="true" Text="Enter type" Value=""></asp:ListItem>
                                                <asp:ListItem Text="CHECK/DD" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="NEFT/RGS" Value="2"></asp:ListItem>
                                                 <asp:ListItem Text="Others" Value="3"></asp:ListItem>
                                            </asp:DropDownList>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="ddltransactiontype"  ForeColor="Red" ErrorMessage="Please Select a Transaction Type "></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Account Holder</label>
                                             <asp:TextBox runat="server" ID="txtaccountholder_update" CssClass="form-control"></asp:TextBox>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="txtaccountholder_update"  ForeColor="Red" ErrorMessage="Please give a Email "></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Account Number</label>
                                            <asp:TextBox runat="server" ID="txtaccountnumber_update" CssClass="form-control"></asp:TextBox>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="txtaccountnumber_update"  ForeColor="Red" ErrorMessage="Please give a Email "></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">IFSC Code</label>
                                             <asp:TextBox runat="server" ID="txtifsc_update" CssClass="form-control"></asp:TextBox>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="txtifsc_update"  ForeColor="Red" ErrorMessage="Please give a Email "></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Bank Name</label>
                                            <asp:TextBox runat="server" ID="txtbankname_update" CssClass="form-control"></asp:TextBox>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="txtbankname_update"  ForeColor="Red" ErrorMessage="Please give a Bank Name "></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Branch</label>
                                            <asp:TextBox runat="server" ID="txtbranch_update" CssClass="form-control"></asp:TextBox>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="txtbranch_update"  ForeColor="Red" ErrorMessage="Please give a Branch "></asp:RequiredFieldValidator>
                                        </div>

                                    </div>
                                    <!--tab-pane-->
                                    <div class="tab-pane p-3 pb-0 fade" id="tab-content-taxation_update" role="tabpanel" aria-labelledby="taxation-tab">

                                        <div class="form-group">
                                            <label class="mb-0">PAN/IT No</label>
                                          <%--  <input type="text" class="form-control" placeholder="PAN/IT No">--%>
                                            <asp:TextBox ID="txtpan_update" CssClass="form-control" runat="server"></asp:TextBox>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="txtpan_update"  ForeColor="Red" ErrorMessage="Please give a PAN/IT No "></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">GST Registration Type</label>
                                             <asp:DropDownList ID="ddlGstregistrationtype_update" CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value=""></asp:ListItem>
                                                <asp:ListItem Text="Unknown" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Composition" Value="2"></asp:ListItem>
                                                <asp:ListItem Text="Consumer" Value="3"></asp:ListItem>
                                               <asp:ListItem Text="Unregistered" Value="4"></asp:ListItem>
                                            </asp:DropDownList>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlGstregistrationtype_update"  ForeColor="Red" ErrorMessage="Please Select a GST Registration Type "></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">GSTIN/UIN</label>
<%--                                            <input type="text" class="form-control" placeholder="GSTIN/UIN">--%>
                                            <asp:TextBox ID="txtgstin_update" runat="server" CssClass="form-control"></asp:TextBox>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="txtgstin_update"  ForeColor="Red" ErrorMessage="Please give a GSTIN/UIN "></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">Assessee of Other Territory</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" type="radio" runat="server" id="Territoryyes_update" name="TerritoryRadio">
                                                    <label for="<%= Territoryyes_update.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" runat="server" id="Territoryno_update" name="TerritoryRadio">
                                                    <label for="<%= Territoryno_update.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">e-Commerce Operator</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" type="radio" runat="server" id="Ecommerceyes_update" name="EcommerceRadio">
                                                    <label for="<%= Ecommerceyes_update.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio"> 
                                                    <input class="custom-control-input" type="radio" runat="server" id="Ecommerceno_update" name="EcommerceRadio">
                                                    <label for="<%= Ecommerceno_update.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">Deemed exporter for purchases</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" type="radio" runat="server" id="purchasesyes_update" name="purchasesRadio">
                                                    <label for="<%= purchasesyes_update.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" runat="server" id="purchasesno_update" name="purchasesRadio">
                                                    <label for="<%= purchasesno_update.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Party Type</label>
                                            <asp:DropDownList ID="ddlpartytype_update" CssClass="form-control" runat="server">
                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="Not Applicable" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Deemed Export" Value="2"></asp:ListItem>
                                                <asp:ListItem Text="Embassy/UN Body" Value="3"></asp:ListItem>
                                               <asp:ListItem Text="SEZ" Value="4"></asp:ListItem>
                                            </asp:DropDownList>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlpartytype_update"  ForeColor="Red" ErrorMessage="Please Select a Party Type "></asp:RequiredFieldValidator>
                                        </div>

                                        <div class="form-group d-flex">
                                            <label class="mb-0 mr-4">Whether Transporter</label>
                                            <div class="d-flex">
                                                <div class="custom-control custom-radio mr-3">
                                                    <input class="custom-control-input" type="radio" id="Transporteryes_update" runat="server" name="TransporterRadio">
                                                    <label for="<%= Transporteryes_update.ClientID %>" class="custom-control-label font-weight-normal">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" id="Transportereno_update" runat="server" name="TransporterRadio">
                                                    <label for="<%= Transportereno_update.ClientID %>" class="custom-control-label font-weight-normal">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Transporter ID</label>
                                            <asp:TextBox runat="server" ID="txttransporterid_update" CssClass="form-control"></asp:TextBox>
                                             <asp:RequiredFieldValidator runat="server" ControlToValidate="txttransporterid_update"  ForeColor="Red" ErrorMessage="Please give a Transporter ID "></asp:RequiredFieldValidator>
                                        </div>

                                    </div>
                                        </asp:PlaceHolder>
                                    <!--tab-pane-->
                                </div>
                                 </div>
                                <!--"tab-content-->
                            <%--</div>--%>
                            <!--card body-->
                            <div class="card-footer bg-white">
                                <div class="form-group mb-0 float-right">
                                    <%--<a class="btn btn-success " href="">Save</a>--%>
                                 <asp:LinkButton runat="server" ID="btnupdate" OnClick="btnupdate_Click" CssClass="btn btn-primary mx-1 py-1">Save</asp:LinkButton>
                                <asp:LinkButton runat="server" ID="btncancel" CssClass="btn btn-secondary mx-1 py-1" CausesValidation="false" OnClick="btncancel_Click">Cancel</asp:LinkButton>
                                </div>
                            </div>
                           
                    </asp:PlaceHolder>                       
                    </div>
                </div>
            </div>      
    </section>
    <style>
        .ledger-tab::after {
            content: '';
            width: calc(100vw + 20vw);
            height: 1px;
            background: #dee2e6;
            left: -20px;
            bottom: 0px;
            position: absolute;
        }

        .ledger-tab li > a.nav-link {
            position: relative;
            font-size: 14px;
            color: #495057;
        }

        .ledger-tab li > a.active.nav-link {
            color: #0069d9;
        }

            .ledger-tab li > a.active.nav-link::after {
                content: '';
                width: 100%;
                height: 4px;
                background: #0069d9;
                left: 0px;
                bottom: -6px;
                position: absolute;
            }

        .tab-content .card-header {
            padding-left: .75rem;
            padding-right: .75rem;
        }

        .tab-content h6 {
            text-decoration: underline;
            font-weight: 600;
        }

        .form-group .breadcrumb-item + .breadcrumb-item::before {
            content: "\f10b";
            font-weight: 900;
            line-height: 22px;
            font-size: 11px;
        }
    </style>  
</asp:Content>
