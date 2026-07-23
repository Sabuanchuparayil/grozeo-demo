<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlCreateOrderPicker.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlCreateOrderPicker" %>

<div class="section-wrapper">
          <label class="section-title">Create New Order Picker</label>
          <div class="form-layout">
            <div class="row mg-b-25">
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">First Name: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtFirstName" runat="server" required CssClass="form-control" placeholder="Enter First Name"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Last Name: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtLastName" runat="server" required CssClass="form-control" placeholder="Enter Second Name"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Phone: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtPhone" runat="server" required CssClass="form-control" placeholder="Enter Phone Number"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group mg-b-10-force">
                  <label class="form-control-label">Employee ID: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtEmpID" runat="server" required CssClass="form-control" placeholder="Enter Employee ID"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Employee NI Number: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtEmpNINumber" runat="server" required CssClass="form-control" placeholder="Enter Employee NI Number"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Email ID: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtEmailID" runat="server" required CssClass="form-control" placeholder="Enter Email ID"/>
                </div>
              </div><!-- col-4 -->
                  <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Address 1: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtAddress1" runat="server" required CssClass="form-control" placeholder="Enter Address 1"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Address 2: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtAddress2" runat="server" required CssClass="form-control" placeholder="Enter Address 2"/>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Post Code: <span class="tx-danger">*</span></label>
                  <asp:TextBox ID="txtPostCode" runat="server" required CssClass="form-control" placeholder="Post Code"/>
                </div>
              </div><!-- col-4 -->
                <div class="col-lg-4">
                  <label class="ckbox">
                    <input type="checkbox" id="chkManualSchedule" runat="server" Checked='<%# Eval("is_allowManualSchedule").Equals("Active") %>'><span>Allow manual schedule</span>
                  </label>
                </div><!-- col-3 -->
                    
                <div class="col-lg-4">
                  <label class="ckbox">
                    <input type="checkbox" id="chkAutoSchedule" runat="server" Checked='<%# Eval("is_allowAutoSchedule").Equals("Active") %>'><span>Allow auto schedule</span>
                  </label>
                </div><!-- col-3 -->
            </div><!-- row -->

            <div class="form-layout-footer">
              <%--<button class="btn btn-primary bd-0" id="btnSubmit" runat="server" onclick="btnAdd_Click">Submit Form</button>--%>
                <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-primary bd-0" Text="Submit Form"/>
                <asp:Button runat="server" ID="btnCancel"  CausesValidation="false" UseSubmitBehavior="false" ValidateRequestMode="Disabled" CssClass="btn btn-secondary bd-0" Text="Cancel" PostBackUrl="~/Tenant/OrderPicker"/>
              <%--<button class="btn btn-secondary bd-0" ID="btnCancel" runat="server" CausesValidation="false" ValidateRequestMode="Disabled">Cancel</button>--%>
            </div><!-- form-layout-footer -->
          </div><!-- form-layout -->
        </div>


<%--<div class="card card-info">--%>
    <%--<div class="col-md-12" id="dvColOrderPickerInfo" runat="server">--%>
              <%--<div class="card-header">
                <h3 class="card-title">Create Order Picker</h3>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-3"><label>First Name*</label>--%>
                    <%--<asp:TextBox ID="txtFirstName" runat="server" required CssClass="form-control" placeholder="Enter First Name"/>--%>
                  <%--</div>
                  <div class="col-3"><label>Last Name*</label>--%>
                    <%--<asp:TextBox ID="txtLastName" runat="server" required CssClass="form-control" placeholder="Enter Second Name"/>--%>
                  <%--</div>
                    <div class="col-3"><label>Phone*</label>--%>
                    <%--<asp:TextBox ID="txtPhone" runat="server" required CssClass="form-control" placeholder="Enter Phone Number"/>--%>
                  <%--</div>
                </div>&nbsp;&nbsp;&nbsp;
                  <div class="row">
                  <div class="col-3"><label>Employee ID*</label>--%>
                    <%--<asp:TextBox ID="txtEmpID" runat="server" required CssClass="form-control" placeholder="Enter Employee ID"/>--%>
                  <%--</div>
                  <div class="col-3"><label>Employee NI Number*</label>--%>
                    <%--<asp:TextBox ID="txtEmpNINumber" runat="server" required CssClass="form-control" placeholder="Enter Employee NI Number"/>--%>
                  <%--</div>
                  <div class="col-3"><label>Email ID*</label>--%>
                    <%--<asp:TextBox ID="txtEmailID" runat="server" required CssClass="form-control" placeholder="Enter Email ID"/>--%>
                  <%--</div>
                </div>&nbsp;&nbsp;&nbsp;
                <div class="row">
                  <div class="col-3"><label>Address 1*</label>--%>
                    <%--<asp:TextBox ID="txtAddress1" runat="server" required CssClass="form-control" placeholder="Enter Address 1"/>--%>
                  <%--</div>
                  <div class="col-3"><label>Address 2</label>--%>
                    <%--<asp:TextBox ID="txtAddress2" runat="server" required CssClass="form-control" placeholder="Enter Address 2"/>--%>
                  <%--</div>
                  <div class="col-3"><label>Post Code*</label>--%>
                    <%--<asp:TextBox ID="txtPostCode" runat="server" required CssClass="form-control" placeholder="Post Code"/>--%>
                  <%--</div>--%>
                <%--</div>&nbsp;&nbsp;
                  <div class="row">
                <div class="col-3"><label>Allow manual schedule</label>
                    <asp:CheckBox ID="chkManualSchedule" TextAlign="Right" AutoPostBack="true" runat="server" Checked='<%# Eval("is_allowManualSchedule").Equals("Active") %>'/>
                </div>
                      <div class="col-3"><label>Allow auto schedule</label>
                    <asp:CheckBox ID="chkAutoSchedule" TextAlign="Right" AutoPostBack="true" runat="server" Checked='<%# Eval("is_allowAutoSchedule").Equals("Active") %>'/>
                </div>
               </div>
              </div>
    </div>--%>

<%--<div class="row">
        <div class="col-12">
            <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-success float-right" Text="Submit" ValidationGroup="AddStore"/>&nbsp;
            <asp:Button ID="btnReset" runat="server" CausesValidation="false" ValidateRequestMode="Disabled" Text="Clear" CssClass="btn btn-secondary float-right" />&nbsp;
            <br /><asp:Label ID="lblMessage" Font-Bold="true" runat="server"/>
        </div>
      </div>--%>
