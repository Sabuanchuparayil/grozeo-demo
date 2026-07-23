<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="TicketDetalisView.aspx.cs" Inherits="RetalineProAgent.Support.TicketDetalisView" %>

<div class="title_sec mb-3 d-flex align-items-center justify-content-between">
  <h3 class="mb-0 tx-dark tx-14">Tickets No.<asp:Literal runat="server" ID="ltrticketNo"></asp:Literal></h3>
<a href="javascript:void(0)" class="backbtn"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</div>
  <div class="article_content_body w-100">

    <div class="ticket-comments-wrap">
    <asp:Repeater ID="rptTicketsDetalis" DataSourceID="SDSTicketsDetalis" runat="server">
        <itemtemplate>
<div class="ticket-comments-list w-100">
        <div class="ticket-comments-date"><%# Eval("createdOn") %></div>
        <div class="ticket-comments">
          <p><%# Eval("createdByName") %> : <%# Eval("ticketRemarks") %> </p>
        </div>
        <asp:PlaceHolder ID="phReopenButton" runat="server" Visible='<%# Eval("ticketStage").ToString() == "6" %>'>
            <a id="btnreponnes" href="javascript:void(0)" data-ticketid='<%# Eval("ticketid") %>' data-unitid='<%# Eval("ticketSupportUnit") %>'  class="reopenbtn">Reopen Ticket</a>
        </asp:PlaceHolder>
      </div>
        </itemtemplate>
    </asp:Repeater>
        <asp:SqlDataSource runat="server" ID="SDSTicketsDetalis" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
        SelectCommand="SELECT sl.id,st.ticketId AS ticketid,ticketNumber,ticketType,sl.ticketStatus,sl.ticketStage,ticketRemarks,ticketSupportUnit,sl.createdBy,sl.createdOn,
        IFNULL(su.name, 'Not Assigned') AS suName,ss.name AS STATUS, IFNULL(support_ticket_stages.name, 'Created') AS tiketStage,
        CASE WHEN ticketType=1 THEN 'Internal Note' WHEN ticketType =2 THEN 'External Note'  END AS ticketTypeName,
        CONCAT(FirstName,' ',LastName) AS createdByName
        FROM support_ticket_log sl
        INNER JOIN `support_ticket` st ON st.ticketId=sl.ticketId
        LEFT JOIN support_unit  su ON su.id = ticketSupportUnit
        LEFT JOIN support_ticket_status ss ON ss.id = sl.ticketStatus 
        LEFT JOIN support_ticket_stages ON support_ticket_stages.id = sl.ticketStage
        LEFT JOIN finascop_usr_profile ON UserId = sl.createdBy  WHERE st.ticketId=@ticketId ORDER BY ticketStage DESC">
        <selectparameters>
            <asp:QueryStringParameter Name="ticketId" QueryStringField="ticketId" DefaultValue="0" />
        </selectparameters>
    </asp:SqlDataSource>
      <div class="ticket-info-action w-100 d-none">
        <div class="ticket-attachement mb-3 ">
          <label for="fileUpload" class="w-100 mb-0">Attach your file</label>
          <input type="file" id="fileUpload" />
        </div>
        <div class="post_comments mb-3 d-none">
          <div class="input-group">
            <textarea class="form-control w-100" placeholder="Enter your comment here" id="" rows="3"></textarea>
          </div>
          <div class="input-group">
            <button type="submit" class="btn btn-primary mt-2">Add Comment</button>
            <button type="button" class="btn btn-outline-primary ml-2 mt-2">Cancel</button>
          </div>
        </div>
      </div>

    </div>
  </div>

<script>
  var loader = '<div class="d-flex align-items-center justify-content-center w-100"><div class="loader"></div></div>';
     $('.backbtn').click(function(e) {
         e.preventDefault();
         $("#suportcontent").html(loader);
         $("#suportcontent").load("/Support/Tickets");
     });

    $(document).on('click', '.reopenbtn', function () {
        var ticketId = $(this).data('ticketid'); // Retrieves data-Ticketid
        console.log(ticketId)
        var unitId = $(this).data('unitid');     // Retrieves data-unitid
        var requestData = {
            ticketId: ticketId,
            unitId: unitId
        };
        console.log(requestData);
        try {

            onSuccess = function (response) {
                if (response.status == 'Success') {
                    displayResults(response.data); // Populate the table with results

                    alert('Ticket has been reopened successfully!'); // Show the alert message
                }
            }
            onError = function (data) {
                alert('Operation failed');
                console.error('AJAX Request Failed:', error);
            };
            retMaster.ajax.JSONRequest('/api/Support/SupportReopen', 'POST', { input: requestData }, onSuccess, onError);
        } catch {

        }
    });    
</script>
<style>
    .title_sec {
        width:100%;
    }  
 </style>