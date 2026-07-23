<%@ Page Language="C#" AutoEventWireup="true" Title="" MasterPageFile="~/AgentMaster.Master" Async="true"  CodeBehind="FeedbackDetails.aspx.cs" Inherits="RetalineProAgent.FeedbackDetails" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <style type="text/css">
        div.pac-container {
    z-index: 99999999999 !important;
        }
    </style>
</asp:Content>  

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="row">
       <!-- left column -->
          <div class="col-md-6" id="dvColInfo" runat="server">
            <div class="card card-success">  
                <div class="card-header">
                <h3 class="card-title">Customer Message Details</h3>
                    <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fa fa-times"></i>
                  </button>
                </div>
              </div>
                      <div class="card-body p-0">
                        <table class="table table-hover table-striped" border="1">
                            <tbody>
                                      <tr>
                                        <th>Mobile</th>
                                        <td><asp:Literal ID="ltrMobile" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Email</th>  
                                        <td><asp:Literal ID="ltrEmail" runat="server"></asp:Literal></td>     
                                      </tr>  
                                      <tr>
                                        <th>Comments</th>
                                        <td><asp:Literal ID="ltrCmmts" runat="server"></asp:Literal></td>
                                      </tr>
                            </tbody>
                        </table>
                      </div>
              
            <%--</div>--%>
                <%--</div>--%>
            </div>
        </div>
    </div>


<br />
    
    
</asp:Content>