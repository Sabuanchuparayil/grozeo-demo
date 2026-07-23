<%@ Page Language="C#" AutoEventWireup="true" Async="true" Title="" MasterPageFile="~/AgentMaster.Master" CodeBehind="TeleOrders.aspx.cs" Inherits="RetalineProAgent.TeleOrders" %>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
   <style type="text/css">
.Initial
{
  display: block;
  padding: 4px 18px 4px 18px;
  float: left;
  background: url("../Images/InitialImage.png") no-repeat right top;
  color: Black;
  font-weight: bold;
}
.Initial:hover
{
  color: White;
  background: url("../Images/SelectedButton.png") no-repeat right top;
}
.Clicked
{
  float: left;
  display: block;
  background: url("../Images/SelectedButton.png") no-repeat right top;
  padding: 4px 18px 4px 18px;
  color: Black;
  font-weight: bold;
  color: White;
}
</style>
</asp:Content>


<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="row">
                  <div class="col-sm-3 input-group-sm">
                      <%--<label for="txtSearch" runat="server">Search by:</label>--%>
                      <asp:TextBox ID="txtMobile" runat="server" CssClass="form-control" placeholder="Mobile"></asp:TextBox> 
                  </div>
                   <div class="col-sm-1">
                      <%--<label runat="server">&nbsp;</label>--%>
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm" runat="server" OnClick="btnSearch_Click"><i class="fa fa-search"></i> Search</asp:LinkButton>
                  </div>
                <div class="col-sm-2 input-group-sm">
                      <%--<label for="txtCommunication" runat="server">Communication</label>--%>
                      <div runat="server" ID="dvCommunication"><asp:HyperLink runat="server" ID="hlCommunication" CssClass="btn btn-block btn-outline-primary btn-xs"><i class="fa fa-user"></i> Communication</asp:HyperLink></div>
                  </div>
                </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <div class="row">
          <!-- left column -->
        <div class="col-md-6" id="Div1" runat="server">
            <div class="card card-success">  
                <div class="card-header">
                <h3 class="card-title">Customer Details</h3>
                    <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
                      <div class="card-body p-0">
                        <table class="table table-striped">
                            <tbody>
                                      <tr>
                                        <th>Name</th>
                                        <td><asp:Literal ID="ltrName" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Mobile</th>  
                                        <td><asp:Literal ID="ltrMobile" runat="server"></asp:Literal></td>     
                                      </tr>  
                                      <tr>
                                        <th>Email</th>
                                        <td><asp:Literal ID="ltrEmail" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Alternate Phone</th>
                                        <td><asp:Literal ID="ltrAltPhone" runat="server"></asp:Literal></td>
                                      </tr>
                                     <tr>
                                        <th>Alternate Email</th>
                                        <td><asp:Literal ID="ltrAltEmail" runat="server"></asp:Literal></td>
                                      </tr>
                                    <tr>
                                        <th>Wallet Balance</th>
                                        <td><asp:Literal ID="ltrWalletBallence" runat="server"></asp:Literal></td>
                                      </tr>
                            </tbody>
                        </table>
                      </div>
                    </div>
            <div class="card card-success">  
                <div class="card-header">
                <h3 class="card-title">Customer Orders</h3>
                    <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
                      <div class="card-body">
                        <div class="table-responsive mailbox-messages">
                            <asp:HiddenField ID="hidFilterType" runat="server" />
                                <asp:GridView AutoGenerateColumns="false" ID="gvOrders" runat="server" CssClass="table table-hover table-bordered" 
                                    AllowPaging="true" PagerSettings-Visible="true" DataSourceID="SDSOrders">
                                    <Columns>
                                        <asp:TemplateField HeaderText = "Serial NO." ItemStyle-Width="100">
                                            <ItemTemplate>
                                                <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Order No"><ItemTemplate>
                                            <asp:HyperLink runat="server" Text='<%# Eval("order_order_id") %>' NavigateUrl='<%# String.Format("~/OrderDetails.aspx?id={0}", Eval("order_id")) %>'></asp:HyperLink><br />
                                            <small>Total: <b><%# Eval("total") %></b></small>
                                        </ItemTemplate></asp:TemplateField>
                                        <%--<asp:BoundField HeaderText="Order No" DataField="order_order_id" />--%>
                                        <asp:BoundField HeaderText="Date" DataField="order_created_on" />
                                        <asp:BoundField HeaderText="Amount" DataField="total" />
                                        <asp:BoundField HeaderText="Status" DataField="order_status" />
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSOrders" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT bco.order_id,bco.order_order_id,order_packedbags_count,bco.order_customer_id,order_branch_id,br_Name,total,
                                                  bco.status_id AS STATUS,DATE_FORMAT(bco.created_at, '%d-%m-%Y') AS order_created_on,TIME_FORMAT(CAST(bco.created_at AS TIME), '%r') AS ordertime, 
                                                  admin_description AS order_status,admin_description, order_payment_gateway_refid, order_payment_gateway_refid_crc32,
                                                  CASE WHEN order_method = 1 THEN 'Drive Delivery' 
                                                  WHEN order_method = 2 THEN 'Customer Collect' 
                                                  WHEN order_method = 3 THEN 'Courier Delivery' END AS order_method,
                                                  (SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS delivery_to,
                                                  (SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS cust_mobile,order_HasReturn, order_ItemsReturned, 
                                                  order_ReturnVerified, bco.created_at,order_latitude,order_longitude FROM retaline_customer_order bco 
                                                  INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id 
                                                  INNER JOIN retaline_customer_order_delivery_address bcoda ON bcoda.customer_order_id = bco.order_id 
                                                  INNER JOIN finascop_branch ON br_ID = order_branch_id inner join retaline_customer c on c.cust_id=bco.order_customer_id  WHERE c.cust_mobile = @custmobile AND bco.status_id > 0 ">
        <SelectParameters>
            <asp:ControlParameter ControlID="txtMobile" Name="custmobile" />
            <%--<asp:Parameter Name="customerId" />--%>
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
                    </div>
            
            <%--<div class="card card-success"> --%> 
                <%--<div class="card-header">
                <h3 class="card-title">Order Details</h3>
                    <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>--%>
                      <div class="card-body p-0">
                        <table class="table table-striped">
                            <tbody>
                                      <tr>
        <td>
          <asp:Button Text="Communication" BorderStyle="None" ID="Tab1" CssClass="Initial" runat="server"
              OnClick="Tab1_Click" />
          <asp:Button Text="Document" BorderStyle="None" ID="Tab2" CssClass="Initial" runat="server"
              OnClick="Tab2_Click" />
          <asp:MultiView ID="MainView" runat="server">
            <asp:View ID="View1" runat="server">
              <table style="width: 100%; border-width: 1px; border-color: #666; border-style: solid">
                <tr>
                  <td>
                    <a href="/OrderPickerSettings" type="button" class="btn btn-info">
    <i class="fas fa-plus"></i>Add Communication</a><br />
                  </td>
                </tr>
              </table>
            </asp:View>
            <asp:View ID="View2" runat="server">
              
                <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="GridView1" runat="server" CssClass="table table-hover table-bordered" 
                                    AllowPaging="true" PagerSettings-Visible="true" DataSourceID="SDSDocuments">
                                    <Columns>
                                        <asp:BoundField HeaderText="Date and Time" DataField="date_and_time" />
                                        <asp:BoundField HeaderText="Resource" DataField="resource" />
                                        <asp:BoundField HeaderText="Attachment" DataField="" />
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SqlDataSource1" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT DATE_FORMAT(crmc_Communication_Time, '%e %M %Y %I.%i %p') AS date_and_time,
                                    CONCAT(FirstName,' ',LastName) AS resource,
                                    crma_name,crmf_filepath,crmf_filename,
                                    RIGHT(crmf_filepath,3) AS fileextension
                                    FROM finascop_crm_communication_file fccf INNER JOIN finascop_crm_communication fcc ON fcc.crmc_id = fccf.crmc_id
                                    INNER JOIN finascop_usr_profile fup ON fup.UserId = fcc.UserId
                                    INNER JOIN finascop_crm_action fca ON fca.crma_id = fcc.crca_id">
        <%--<SelectParameters>
            <asp:Parameter Name="fsto_uid" />
            <asp:Parameter Name="fsto_id" />
        </SelectParameters>--%>
    </asp:SqlDataSource>
               </div>
              
            </asp:View>
          </asp:MultiView>
        </td>
      </tr>
                            </tbody>
                        </table>
                      </div>
                    <%--</div>--%>
            <%--<div class="card card-success">  
                <div class="card-header">
                <h3 class="card-title">Order Item/s</h3>
                    <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
                      <div class="card-body">
                        <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="gvItemDetails" runat="server" CssClass="table table-hover table-bordered" 
                                    AllowPaging="true" PagerSettings-Visible="true" DataSourceID="SDSItemDetails">
                                    <Columns>
                                        <asp:BoundField HeaderText="Item Name" DataField="product_name" />
                                        <asp:BoundField HeaderText="Rate" DataField="item_sales_price" />
                                        <asp:BoundField HeaderText="Quantity" DataField="item_order_qty" />
                                        <asp:BoundField HeaderText="Amount" DataField="item_price" />
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSItemDetails" ProviderName="MySql.Data.MySqlClient"
                                 SelectCommand = "SELECT item_product_id,(SELECT stit_SKU FROM `finascop_stock_itemmaster` WHERE stit_ID=item_product_id) AS product_name,
                                                  item_order_qty,item_price,item_cgst,item_amount,item_sales_price FROM retaline_customer_order_items
                                                  WHERE customer_order_id = 1">
        
    </asp:SqlDataSource>
               </div>
                </div>
                    </div>--%>

                </div>
        <!-- right column -->

          <div class="col-md-6" id="dvColStoreInfo" runat="server">
              
            <div class="card card-success"> 
            <div class="card-header">
                <h3 class="card-title">Impersonate</h3>
                </div>
                </div>
            <iframe name="impersonateIframe" id="impersonateIframe" width="600px" height="1000px" runat="server"></iframe>
                
        </div>
        <div class="col-md-6">
            <div class="card card-success">  
                <div class="card-header">
                <h3 class="card-title">Documents</h3>
                    <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
                      <div class="card-body">
                        <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="gvDocuments" runat="server" CssClass="table table-hover table-bordered" 
                                    AllowPaging="true" PagerSettings-Visible="true" DataSourceID="SDSDocuments">
                                    <Columns>
                                        <asp:BoundField HeaderText="Date and Time" DataField="date_and_time" />
                                        <asp:BoundField HeaderText="Resource" DataField="resource" />
                                        <asp:BoundField HeaderText="Attachment" DataField="" />
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSDocuments" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT DATE_FORMAT(crmc_Communication_Time, '%e %M %Y %I.%i %p') AS date_and_time,
                                    CONCAT(FirstName,' ',LastName) AS resource,
                                    crma_name,crmf_filepath,crmf_filename,
                                    RIGHT(crmf_filepath,3) AS fileextension
                                    FROM finascop_crm_communication_file fccf INNER JOIN finascop_crm_communication fcc ON fcc.crmc_id = fccf.crmc_id
                                    INNER JOIN finascop_usr_profile fup ON fup.UserId = fcc.UserId
                                    INNER JOIN finascop_crm_action fca ON fca.crma_id = fcc.crca_id">
        <%--<SelectParameters>
            <asp:Parameter Name="fsto_uid" />
            <asp:Parameter Name="fsto_id" />
        </SelectParameters>--%>
    </asp:SqlDataSource>
               </div>
                </div>
                    </div>
                </div>

            </div>
        
</asp:Content>

