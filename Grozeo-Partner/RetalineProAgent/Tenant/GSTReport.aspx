<%@ Page Language="C#" AutoEventWireup="true" Title="GST Report" MasterPageFile="~/Tenant/TenantMaster.master" Async="true"  CodeBehind="GSTReport.aspx.cs" Inherits="RetalineProAgent.GSTReport" %>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div class="col-sm-6">
            <h1 style="float: left;">GST Report</h1>
          </div>
    <script></script>
    
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
               <div class="card-header">
              <div class="card-tools">
                <div class="input-group input-group-sm">
                  <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="nofill"></asp:TextBox> 
                    
<div class="float-right">
                  <asp:Literal runat="server" ID="ltrPageCurStart" Text="1"></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPageCurTotal" Text="50"></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPageTotal" Text="200"></asp:Literal>
                  <div class="btn-group">
                      <asp:LinkButton ID="lbtnPagerLeft" runat="server" OnClick="lbtnPagerLeft_Click" CssClass="btn btn-default btn-sm">
                      <i class="fa fa-chevron-left"></i>
                      </asp:LinkButton>
                      <asp:LinkButton ID="lbtnPagerRight" runat="server" OnClick="lbtnPagerRight_Click" CssClass="btn btn-default btn-sm">
                          <i class="fa fa-chevron-right"></i>
                      </asp:LinkButton>
                    
                  </div>

                  <!-- /.btn-group -->
                </div>
                    
                </div>
                  
              </div>
              <br /><br />
            </div>
              <div class="card-body">

               <div class="table-responsive mailbox-messages">
                   <%--<asp:DropDownList runat="server" ID="list1" AutoPostBack="true" OnSelectedIndexChanged="lstView_Changed">
                        <asp:ListItem Value="0">Please Select</asp:ListItem>
                              <asp:ListItem Value="1">IGST</asp:ListItem>
                              <asp:ListItem Value="2">CGST</asp:ListItem>
                              <asp:ListItem Value="3">SGST</asp:ListItem>
                              <asp:ListItem Value="4">KFC</asp:ListItem>
                              <asp:ListItem Value="5">TCS</asp:ListItem>
                    </asp:DropDownList>--%>

                                <asp:GridView AutoGenerateColumns="false" ID="gvGSTReport" runat="server" CssClass="table table-hover table-striped" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvGSTReport_DataBound" DataSourceID="SDSGSTReport">
                                    <Columns>
                                        <asp:BoundField HeaderText="Date" DataField="actr_Date" SortExpression="actr_Date" />
                                        <asp:BoundField HeaderText="Particulars" DataField="bankRec_particulars" SortExpression="bankRec_particulars" />
                                        <asp:BoundField HeaderText="Voucher Type" DataField="type_name" SortExpression="type_name" />
                                        <asp:BoundField HeaderText="Voucher No" DataField="acet_NO" SortExpression="acet_NO" />
                                        <asp:BoundField HeaderText="Debit" DataField="dr" SortExpression="dr" />
                                        <asp:BoundField HeaderText="Credit" DataField="cr" SortExpression="cr" />
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSGSTReport" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT a.acet_NO, CASE WHEN a.acet_TypeId = 1 OR a.acet_TypeId = 3   THEN 'Receipt'
		                            WHEN a.acet_TypeId = 2 OR a.acet_TypeId = 4 THEN 'Payment'WHEN a.acet_TypeId = 5 THEN 'Journal Voucher'
                                    WHEN a.acet_TypeId = 6 THEN 'Contra Entry' ELSE '-' END AS  type_name,  a.actr_Date,
                                    (SELECT GROUP_CONCAT(accled_LedgerName) FROM finascop_accounts_ledger  WHERE accled_Ledger_Id IN 
                                    (SELECT fat.ledg_Id FROM finascop_accounts_transaction fat WHERE fat.acet_NO = a.acet_NO  AND 
                                    fat.ledg_Id <> a.ledg_Id  ) ) AS bankRec_particulars,if(actr_IsDebtor=1,actr_amount,-actr_amount) AS drcr_amount FROM finascop_accounts_transaction a INNER JOIN finascop_branch b ON b.br_ID=a.br_ID WHERE b.br_storeGroup = @storegroupid"
        OnSelecting="SDSGSTReport_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroupid" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
                </div>
                </div>
              </div>
            </div>
    </div>
</asp:Content>



