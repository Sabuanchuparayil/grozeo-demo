<%@ Page Language="C#" AutoEventWireup="true" Async="true" Title="" MasterPageFile="~/AgentMaster.Master" CodeBehind="DeliveryAddress.aspx.cs" Inherits="RetalineProAgent.DeliveryAddress" %>

<%--<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div class="col-sm-12">
            <h1><small>Order Id:</small> <asp:Literal ID="ltrTitleOrderId" runat="server" Text=""></asp:Literal>
            </h1>
          </div>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="row">
          <!-- left column -->
             
            <div class="col-md-4">
            <div class="card card-success">  
                <div class="card-header">
                <h3 class="card-title">Delivery Address</h3>
                    <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fa fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fa fa-times"></i>
                  </button>
                </div>
              </div>
                      <div class="card-body">
                        <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="gvDelivAddress" runat="server" CssClass="table table-hover table-bordered" 
                                    AllowPaging="true" PagerSettings-Visible="true" DataSourceID="SDSDeliveryAddress">
                                    <Columns>
                                        <%--<asp:TemplateField HeaderText = "Serial NO." ItemStyle-Width="100">
                                            <ItemTemplate>
                                                <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                                            </ItemTemplate>
                                        </asp:TemplateField>--%>
                                        <asp:BoundField HeaderText="Address Type" DataField="deli_type" />
                                        <asp:BoundField HeaderText="Area" DataField="deli_house_name" />
                                        <asp:BoundField HeaderText="Assoc. Branch" DataField="br_Name" />
                                        <%--<asp:BoundField HeaderText="Action" DataField="" />--%>
                                    </Columns>
                                </asp:GridView>
                             <asp:SqlDataSource runat="server" ID="SDSDeliveryAddress" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT  deli_id,deli_name,deli_delivery_pin,deli_house_no,deli_house_name,deli_land_mark,deli_is_primary,br_Name,
                                                 deli_type,br_Name,deli_district,deli_latitude,deli_longitude FROM retaline_customer_delivery_info df
                                                 LEFT JOIN finascop_branch ON br_ID = deli_branch_id 
                                                 INNER JOIN retaline_customer rc ON rc.cust_id=df.deli_customer_id
                                                 WHERE rc.cust_id = @cust_id"
       OnSelecting="SDSDeliveryAddress_Selecting">
        <SelectParameters>
            <asp:Parameter Name="cust_id" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
                    </div>
                </div>
               

                
        <!-- right column -->

          <div class="col-md-4" id="dvColStoreInfo" runat="server">
            <div class="card card-success">  
                <div class="card-header">
                <h3 class="card-title">Address Details</h3>
                    <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fa fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fa fa-times"></i>
                  </button>
                </div>
              </div>
                
                      <div class="card-body p-0">
                        <table class="table table-striped">
                            <tbody>
                                      <tr>
                                        <th>Mobile</th>
                                        <td><asp:Literal ID="ltrMobile" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Address Type</th>  
                                        <td><asp:Literal ID="ltrAddrType" runat="server"></asp:Literal></td>     
                                      </tr>  
                                      <tr>
                                        <th>PIN</th>
                                        <td><asp:Literal ID="ltrPin" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>House Name</th>
                                        <td><asp:Literal ID="ltrHseName" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Land Mark</th>
                                        <td><asp:Literal ID="ltrLandMrk" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>District</th>
                                        <td><asp:Literal ID="ltrDist" runat="server"></asp:Literal></td>
                                      </tr>
                                     <tr>
                                        <th>State</th>
                                        <td><asp:Literal ID="ltrState" runat="server"></asp:Literal></td>
                                      </tr>
                                    <tr>
                                        <th>Latitude</th>
                                        <td><asp:Literal ID="ltrLat" runat="server"></asp:Literal></td>
                                      </tr>
                                    <tr>
                                        <th>Longitude</th>
                                        <td><asp:Literal ID="ltrLong" runat="server"></asp:Literal></td>
                                      </tr>
                                    <tr>
                                        <th>Associated Branch</th>
                                        <td><asp:Literal ID="ltrAssBranch" runat="server"></asp:Literal></td>
                                      </tr>
                                    <tr>
                                        <th>Created On</th>
                                        <td><asp:Literal ID="ltrCrDate" runat="server"></asp:Literal></td>
                                      </tr>
                            </tbody>
                        </table>
                      </div>
                    </div>
                </div>
        <div class="col-md-4">
            <div class="card card-success">  
                <div class="card-header">
                <h3 class="card-title">Wallet History</h3>
                    <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fa fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fa fa-times"></i>
                  </button>
                </div>
              </div>
                      <div class="card-body">
                        <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="gvWallHist" runat="server" CssClass="table table-hover table-bordered" 
                                    AllowPaging="true" PagerSettings-Visible="true" DataSourceID="SDSWalletHist">
                                    <Columns>
                                        <%--<asp:TemplateField HeaderText = "Serial NO." ItemStyle-Width="100">
                                            <ItemTemplate>
                                                <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                                            </ItemTemplate>
                                        </asp:TemplateField>--%>
                                        <asp:BoundField HeaderText="Order No" DataField="orderno" />
                                        <asp:BoundField HeaderText="Info" DataField="orderinfo" />
                                        <asp:BoundField HeaderText="Entry Date" DataField="date" />
                                        <asp:BoundField HeaderText="Amount" DataField="orderamount" />
                                    </Columns>
                                </asp:GridView>
                             <asp:SqlDataSource runat="server" ID="SDSWalletHist" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT (select order_order_id from retaline_customer_order where order_id=refentry_id) as  orderno, 
                                 brcw_Amount as  orderamount, brcw_AddInfo as  orderinfo, brcw_CreatedOn as date FROM retaline_customer_wallet_transaction  
                                 WHERE cust_id = @cust_id" OnSelecting="SDSDeliveryAddress_Selecting">
         <SelectParameters>
            <asp:Parameter Name="cust_id" />
        </SelectParameters>
        
    </asp:SqlDataSource>
               </div>
                </div>
                    </div>
                </div>
            </div>
        
</asp:Content>

