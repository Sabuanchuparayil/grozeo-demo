<%@ Page Language="C#" AutoEventWireup="true"  CodeBehind="Tickets.aspx.cs" Inherits="RetalineProAgent.Support.Tickets" %>
<script src="/Content/js/custom/master.js"></script>
<div class="topic_lstwrap mb-4">
  <div class="suprttitle">
    <h3 class="mb-2 tx-dark tx-16">Tickets</h3>
    <a class="mb-2 d-none" href="">View All Tickets</a>
    <a class="mb-2 btnsupportview" id="btnsupportview3"  href="javascript:void(0)">Create New Ticket</a>
  </div>
  <ul class="ticket_link m-0 p-0">
        <asp:Repeater ID="rptTickets" DataSourceID="SDSTickets" runat="server">
            <itemtemplate>
                <li>
                    <a class="ticketsload"  data-href="/support/TicketDetalisView?ticketId=<%#Eval("ticketId") %>">
                        <h6 class="mb-0"><%# Eval("ticketRemarks") %></h6>
                        <div class="ticket_shot_info">
                            <div class="tickets_status">
                                <span class="ticket_date">Date: <strong><%# Eval("createdOn") %></strong></span>
                                <span class="status">Status: <strong><%# Eval("STATUS") %></strong></span>
                            </div>
                            <span class="support_unit">Support Unit: <strong><%# Eval("suName") %></strong></span>
                        </div>
                    </a>
                </li>
            </itemtemplate>
             <footertemplate>
                    <asp:Label ID="defaultItem" CssClass="noitem" runat="server"
                        Visible='<%# rptTickets.Items.Count == 0 %>' Text="No items found" />
                </footertemplate>
        </asp:Repeater>
    <asp:SqlDataSource runat="server" ID="SDSTickets" OnSelecting="SDSTickets_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
        SelectCommand="SELECT sl.id,st.ticketId,ticketType,sl.ticketStatus,sl.ticketStage,ticketRemarks,ticketSupportUnit,sl.createdBy,sl.createdOn,
        IFNULL(su.name, 'Not Assigned') AS suName,ss.name AS STATUS, IFNULL(support_ticket_stages.name, 'Created') AS tiketStage,
        CASE WHEN ticketType=1 THEN 'Internal Note' WHEN ticketType =2 THEN 'External Note'  END AS ticketTypeName,
        CONCAT(FirstName,' ',LastName) AS createdByName
        FROM support_ticket_log sl
        INNER JOIN `support_ticket` st ON st.ticketId=sl.ticketId
        LEFT JOIN support_unit  su ON su.id = ticketSupportUnit
        LEFT JOIN support_ticket_status ss ON ss.id = sl.ticketStatus 
        LEFT JOIN support_ticket_stages ON support_ticket_stages.id = sl.ticketStage
        LEFT JOIN finascop_usr_profile ON UserId = sl.createdBy  WHERE st.CreatedBy=@storegroupid">
        <SelectParameters><asp:Parameter Name="storegroupid"/></SelectParameters>
    </asp:SqlDataSource>
    </ul>
</div>

<script>
    var loader = '<div class="d-flex align-items-center justify-content-center w-100"><div class="loader"></div></div>';
     $('.backbtn').click(function(e) {
         e.preventDefault();
         $("#suportcontent").html(loader);
         $("#suportcontent").load("Support/supportLanding");
     });


    var TicketsLoadDiv = $("#suportcontent");
        $('.ticketsload').on('click', function(e) {  
            $("#suportcontent").html(loader);
            var href = $(this).data('href');
            e.preventDefault();
            TicketsLoadDiv.load(href);
            console.log("clicked");
            // console.log(loader);        
            console.log(href);
            console.log(TicketsLoadDiv);        
      });

     
</script>
