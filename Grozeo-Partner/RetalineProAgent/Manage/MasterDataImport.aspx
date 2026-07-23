<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/AgentMaster.Master" Title="Master data import" CodeBehind="MasterDataImport.aspx.cs" Inherits="RetalineProAgent.MasterDataImport" %>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
<small>Import master data using excel data source. Destination table name will be: tmp_master_pro2, database: grozeo_tmp, Server: Default<br />
                At present, only excel import to temp table will be executed from here. Migration of data from temp table to master tables should be triggered from the admin portal.</small>
<br />
<div class="row">

    <div class="col-md-10" >
            <div class="card card-success">  
                <div class="card-header">
                <h3 class="card-title">Import excel</h3>
                
              </div>
                <div class="card-body box-profile">
                
                    <div class="form-group">
                        <label>Select Excel* </label>
                        <asp:FileUpload ID="fupload" runat="server" CssClass="form-control" />
                        <asp:RequiredFieldValidator runat="server" ControlToValidate="fupload" ErrorMessage="Please select file"></asp:RequiredFieldValidator>
                    </div>
                    <div class="form-group">
                        <%--<label></label>--%>
                        <asp:CheckBox ID="chkCreateTable" Text="Create table if not exists?" CssClass="form-control" runat="server" />
                    </div>

                    <div class="form-group">
                  <%--<label>Clear existing data?</label>--%>
                        <asp:CheckBox ID="chckClearRecords" runat="server" Text="Clear existing data?" CssClass="form-control" />
                </div>

                  <div class="form-group">
                      <asp:CheckBox ID="chkDefaultDBServer" AutoPostBack="true" Checked="true" runat="server" Text="Default DB Server?" CssClass="form-control" />
                      <asp:PlaceHolder ID="plsDefaultDB" runat="server">
                  <label>Database (Optional. Default: grozeo_temp)</label>
                  <div class="form-group">
                      <asp:TextBox ID="txtDB" runat="server" CssClass="form-control" placeholder="Enter the DB name if a different DB in Default"></asp:TextBox>
                  </div>
                    </asp:PlaceHolder>
                      <asp:PlaceHolder ID="plcNotDefaultDB" runat="server">
                  <label>DB Connectionstring</label>
                  <div class="form-group">
                      <asp:TextBox ID="txtCon" runat="server" CssClass="form-control" placeholder="Enter the DB connectionstring"></asp:TextBox>
                  </div>
                    </asp:PlaceHolder>


                </div>

                    <div class="form-group">
                        <label>Table name: </label>
                        <asp:TextBox ID="txtTableName" runat="server" CssClass="form-control" placeholder="Enter the table name. Default: tmp_master_pro2"></asp:TextBox>
                    </div>


              </div>

            </div>

            </div>

</div>


<div class="row">
        <div class="col-10">
            <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-success float-right" Text="Submit Store Info"/>&nbsp;
            <asp:HyperLink runat="server" NavigateUrl="/" Text="Cancel" CssClass="btn btn-secondary float-right" ></asp:HyperLink>&nbsp;
            <br /><asp:Label ID="lblResult" Font-Bold="true" runat="server"/>
        </div>
      </div>
<br />


</asp:Content>
