<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlCreateDeliveryBoy.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlCreateDeliveryBoy" %>

<%--<div class="card card-info">
    
              <div class="card-header">
                <h3 class="card-title">Create Order Picker</h3>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-3"><label>First Name*</label>
                    <asp:TextBox ID="txtFirstName" runat="server" required CssClass="form-control" placeholder="Enter First Name"/>
                  </div>
                  <div class="col-3"><label>Last Name*</label>
                    <asp:TextBox ID="txtLastName" runat="server" required CssClass="form-control" placeholder="Enter Second Name"/>
                  </div>
                </div>&nbsp;&nbsp;&nbsp;
                <div class="row">
                  <div class="col-3"><label>Address 1*</label>
                    <asp:TextBox ID="txtAddress1" runat="server" required CssClass="form-control" placeholder="Enter Address 1"/>
                  </div>
                  <div class="col-3"><label>Address 2</label>
                    <asp:TextBox ID="txtAddress2" runat="server" required CssClass="form-control" placeholder="Enter Address 2"/>
                  </div>
                  <div class="col-3"><label>Post Code*</label>
                    <asp:TextBox ID="txtPostCode" runat="server" required CssClass="form-control" placeholder="Post Code"/>
                  </div>
                </div>&nbsp;&nbsp;
                  <div class="row">
                  
                      <div class="form-group">
                        <label>Employee Type*</label>
                          <asp:DropDownList ID="DropDownList1" runat="server">
                              <asp:ListItem Value="0">Please Select</asp:ListItem>
                              <asp:ListItem>Own Employee</asp:ListItem>
                              <asp:ListItem>Hired Employee</asp:ListItem>
                          </asp:DropDownList>
                        
                      </div>
                  <div class="col-3"><label>Employee ID*</label>
                    <asp:TextBox ID="txtEmpID" runat="server" required CssClass="form-control" placeholder="Enter Employee ID"/>
                  </div>
                 </div>&nbsp;&nbsp;&nbsp;
                  <div class="row">
                    <div class="col-3"><label>Employee NI Number*</label>
                    <asp:TextBox ID="txtEmpNINumber" runat="server" required CssClass="form-control" placeholder="Enter Employee NI Number"/>
                  </div>
                  <div class="col-3"><label>Email ID*</label>
                    <asp:TextBox ID="txtEmailID" runat="server" required CssClass="form-control" placeholder="Enter Email ID"/>
                  </div>
                </div>&nbsp;&nbsp;&nbsp;
                  <div class="row">
                  <div class="col-3"><label>Phone*</label>
                    <asp:TextBox ID="txtPhone" runat="server" required CssClass="form-control" placeholder="Enter Address 1"/>
                  </div>
                  <div class="col-3"><label>License</label>
                    <asp:TextBox ID="txtLicense" runat="server" required CssClass="form-control" placeholder="Enter Address 2"/>
                  </div>
                  <div class="col-3"><label>License Validity</label>
                    <asp:TextBox ID="txtLicenseValidity" runat="server" TextMode="Date"/>
                  </div>
                </div>&nbsp;&nbsp;
                  <div class="row">
                  <div class="col-3"><label>Date Of Birth*</label>
                    <asp:TextBox ID="txtDOB" runat="server" TextMode="Date" />
                  </div>--%>
                  <%--<div class="col-3"><label>Branch</label>
                    <asp:DropDownList ID="selBranches" OnDataBound="selBranches_DataBound" AutoPostBack="true" style="float: left; width: 50%" DataSourceID="SDSBranches" AppendDataBoundItems="true" DataTextField="br_Name"  DataValueField="br_ID" CssClass="form-control" runat="server"><asp:ListItem Text="Select Branch" Value=""></asp:ListItem></asp:DropDownList>
                      <asp:SqlDataSource ID="SDSBranches" runat="server" 
                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup = @storegroupid"
                ProviderName="MySql.Data.MySqlClient">
                        <SelectParameters>
                            <asp:Parameter Name="storegroupid" DefaultValue="-1" /></SelectParameters>
                      </asp:SqlDataSource>
                  </div>--%>
                  <%--<div class="col-3"><label>Coverage KM</label>
                    <asp:TextBox ID="txtCoverageKM" runat="server" required CssClass="form-control" placeholder="Post Code"/>
                  </div>
                </div>&nbsp;&nbsp;
                  <div class="row">
                <div class="col-3"><label>Allow manual schedule</label>
                    <asp:CheckBox ID="chkManualSchedule" TextAlign="Right" AutoPostBack="true" runat="server" Checked='<%# Eval("is_allowManualSchedule").Equals("Active") %>'/>
                </div>
                      <div class="col-3"><label>Allow auto schedule</label>
                    <asp:CheckBox ID="chkAutoSchedule" TextAlign="Right" AutoPostBack="true" runat="server" Checked='<%# Eval("is_allowAutoSchedule").Equals("Active") %>'/>
                </div>
               </div>
              </div>
    </div>
<div class="row">
        <div class="col-12">
            <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-success float-right" Text="Submit" ValidationGroup="AddStore"/>&nbsp;
            <asp:Button ID="btnReset" runat="server" CausesValidation="false" ValidateRequestMode="Disabled" Text="Clear" CssClass="btn btn-secondary float-right" />&nbsp;
            <br /><asp:Label ID="lblMessage" Font-Bold="true" runat="server"/>
        </div>
      </div>--%>
