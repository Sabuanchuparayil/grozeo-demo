<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="ViewActivity.aspx.cs" Inherits="RetalineProAgent.Business.ViewActivity" %>

<div class="card">
    <h5 class="modal-title" id="lblTitle" runat="server">
        <asp:Literal ID="ltrTitle" runat="server" Text=""></asp:Literal>
    </h5>
    <div class="modal-body">
        <asp:PlaceHolder ID="phNoData" runat="server" Visible="false">
            <div class="text-center">
                <img src="/content/images/ban-light.svg">
                <h6 class="mb-3">No activity added</h6>
            </div>
        </asp:PlaceHolder>

        <asp:Repeater ID="rptActivities" runat="server" DataSourceID="SDSListDetails">
            <Itemtemplate>
                <div class="activity-card p-3 mb-3">
                    <!-- Date and Time -->
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold text-muted <%# string.IsNullOrEmpty(Eval("formatted_cdate")?.ToString()) || string.IsNullOrEmpty(Eval("formatted_cTime")?.ToString()) || Eval("formatted_cTime").ToString() == "00:00" ? "hidden" : "" %>">
                            <%# Eval("formatted_cdate", "{0:dd MMM yyyy}") %> <%# Eval("formatted_cTime") %>
                        </span>
                    </div>

                    <!-- Activity Name -->
                    <h5 class="mt-2 mb-1 text-red <%# string.IsNullOrEmpty(Eval("actionName")?.ToString()) ? "hidden" : "" %>">
                        <%# Eval("actionName") %>
                    </h5>

                    <!-- Notes -->
                    <p class="mb-2 text-black text-black-custom <%# string.IsNullOrEmpty(Eval("crmc_remark")?.ToString()) ? "hidden" : "" %>">
                        <%# Eval("crmc_remark") %>
                    </p>

                    <!-- Attachment -->
                    <div class="<%# string.IsNullOrEmpty(Eval("crmf_filepath")?.ToString()) ? "hidden" : "" %>">
                        <i class="fa-regular fa-file-lines mr-2"></i>
                        <a href='<%# Eval("crmf_filepath") %>' target="_blank" class="btn btn-link p-0 text-info underline">View Attachment</a>
                    </div>

                    <!-- Additional Date and Time -->
                    <div>
                        <span class="text-black-custom <%# string.IsNullOrEmpty(Eval("formatted_date")?.ToString()) || string.IsNullOrEmpty(Eval("formatted_Time")?.ToString()) || Eval("formatted_Time").ToString() == "00:00" ? "hidden" : "" %>">
                            <i class="fa-regular fa-calendar-clock mr-2"></i>
                            <%# Eval("formatted_date", "{0:dd MMM yyyy}") %> <%# Eval("formatted_Time") %>
                        </span>
                    </div>
                </div>
            </Itemtemplate>
        </asp:Repeater>
    </div>


    <asp:SqlDataSource runat="server" ID="SDSListDetails" ProviderName="MySql.Data.MySqlClient" 
        ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="SELECT cm.crmc_id, cm.crle_id, prospectId, crca_id, IF((crca_id < 16), (SELECT crma_name FROM finascop_crm_action WHERE crma_id=crca_id), activity) AS actionName, 
        crmc_remark, crmc_HasFile, crmc_Communication_Time,DATE_FORMAT(crmc_Communication_Time, '%d %b %Y') AS formatted_date, 
        DATE_FORMAT(crmc_Communication_Time, ' %H:%i') AS formatted_time, DATE_FORMAT(crmc_CreatedOn, '%d %b %Y') AS formatted_cdate, 
        DATE_FORMAT(crmc_CreatedOn, ' %H:%i') AS formatted_ctime, noteType, cmf.crmf_filepath 
        FROM finascop_crm_communication cm
        LEFT JOIN finascop_crm_communication_file cmf ON cmf.crmc_id=cm.crmc_id WHERE cm.crle_id = @leadId AND crca_id > 2 
        ORDER BY crmc_id DESC" OnSelecting="SDSListDetails_Selecting" OnSelected="SDSListDetails_Selected">
        <SelectParameters>
            <asp:Parameter Name="leadId" DefaultValue="0" />
            <%--<asp:Parameter Name="prospectId" DefaultValue="0" />--%>
        </SelectParameters>
    </asp:SqlDataSource>
    

    <style>
    .modal-title {
        color: black;
    }

    .activity-card {
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .text-red {
        color: red;
    }

    .text-black-custom {
        color: black;
    }

    .text-center img {
        opacity: 0.9;
        max-width: 150px;
    }

    .hidden {
        display: none;
    }

    .underline {
        text-decoration: underline;
    }

    .modal-body {
    max-height: 400px; 
    overflow-y: auto;  /* Enables vertical scrolling */
}
</style>

   
</div>

