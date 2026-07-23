<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="Subscription.aspx.cs" Inherits="RetalineProAgent.Tenant.Subscription" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Navigations/Accounts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
            <asp:Literal ID="ltrSubscription" runat="server" Text="Subscriptions"></asp:Literal>
        </h6>
        <p class="mb-0">Discover endless possibilities, exclusive perks, and the freedom to explore with our enhanced subscription plans.</p>
    </div>
</asp:Content>


<asp:Content runat="server" ContentPlaceHolderID="head">
    
  <script>
      $(document).ready(function () {
          $('[data-toggle="tooltip"]').tooltip();
      });

      $(".sidebar-back").click(function () {
          $(".collapse").removeClass("show");
      });

      function closeWelcomemsg() {
          $('.close_Welcome_msg').closest('.Welcome_msg_poup').hide();
      }

      function closeribbon() {
          $('.closeribbon').closest('.ribbon-corner').hide();
      }
  </script>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

          <div class="row row-sm">
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body shadow_top p-3">
                        <div class="row row-sm">
                            <div class="col-12 mb-4">
                             <h5 class="tx-dark mb-4">Plans and Packages Available for Subscription</h5>


                                  
<asp:Repeater ID="rptPlans" runat="server" DataSourceID="SDSPlans">
    <HeaderTemplate><div class="plans_button_sec d-flex justify-content-between"> </HeaderTemplate>
    <ItemTemplate>


                                    <div class="plans_button_wrap text-center">
                                      <a href="javascript:void(0)" class="viewplantrger mb-2" planId="<%# Eval("Id") %>">Whats included?</a>

                                      <a href="javascript:void(0)" class="plans_button growth_plan <%# (Eval("curType").ToString() == "2" || (Eval("Id").ToString() == "1" && Eval("CurPlanId").ToString() == "0") ? "active" : "") %> 
                                          <%# ((Eval("CurGroupId") != DBNull.Value && Eval("GroupId") != DBNull.Value && Convert.ToInt32(Eval("GroupId")) < Convert.ToInt32(Eval("CurGroupId"))) || (Eval("MonthlyPricing") == DBNull.Value && Eval("YearlyPricing") == DBNull.Value) || (Eval("YearlyPriceId") != DBNull.Value && Eval("YearlyPriceId").ToString() == Eval("CurPriceId").ToString()) ?"":"subscribe_btn_action") %> " sbr_id='<%# Eval("Id") %>'>
                                        <div class="d-flex justify-content-center flex-wrap position-relative overflow-hidden">
                                            <asp:Label runat="server" CssClass="plans_ribbon" Text="popular" Visible='<%# Eval("Id").ToString() == "2" %>'></asp:Label>
                                          <p class="m-0"><%# (Eval("curType").ToString() == "2" || (Eval("Id").ToString() == "1" && Eval("CurPlanId").ToString() == "0") ? "Current plan" : (Eval("curType").ToString() == "1" ? "Subscription plan" : "Upgrade to")) %></p>
                                          <h6 class="w-100 text-center"><%# Eval("Description") %></h6>
                                          <p class="m-0">

                                            <asp:PlaceHolder runat="server" Visible='<%# (Eval("CurPlanId") != DBNull.Value && Eval("CurPlanId").ToString() == Eval("Id").ToString()) %>'>
    <asp:Literal runat="server" Text="Renews for " Visible='<%# Convert.ToBoolean(Eval("AutoRenew")) %>'></asp:Literal>
    <asp:Literal runat="server" Text="Ends " Visible='<%# !Convert.ToBoolean(Eval("AutoRenew")) %>'></asp:Literal>
                
    <asp:Label runat="server" CssClass="greencolor" Visible='<%# (Eval("MonthlyPricing") != DBNull.Value && Eval("MonthlyPriceId").ToString() == Eval("CurPriceId").ToString()) %>' Text='<%# String.Format(" {0}{1} ", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("MonthlyPricing")) %>'></asp:Label>
    <asp:Label runat="server" CssClass="greencolor" Visible='<%# (Eval("YearlyPriceId")  != DBNull.Value && Eval("YearlyPriceId").ToString() == Eval("CurPriceId").ToString()) %>' Text='<%# String.Format("{0}{1} ", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("YearlyPricing")) %>'></asp:Label>
    <asp:Literal runat="server" Visible='<%# (Eval("ExpiryDate") != DBNull.Value) %>' Text='<%# String.Format("on {0}", (Eval("ExpiryDate") == DBNull.Value ? "" : Convert.ToDateTime(Eval("ExpiryDate")).ToString("d-M-yy"))) %>'></asp:Literal>
    <asp:PlaceHolder runat="server" Visible='<%# Convert.ToBoolean(Eval("AutoRenew")) %>'><i class="fa-light fa-trash-can ml-2 cancelSubAutoRenew" curPriceId='<%# Eval("CurPriceId") %>'></i></asp:PlaceHolder> 
    <asp:PlaceHolder runat="server" Visible='<%# !Convert.ToBoolean(Eval("AutoRenew")) %>'><br /><span class="enableSubAutoRenew tx-warning" curPriceId='<%# Eval("CurPriceId") %>'><i class="fa-light fa-square mr-2"></i>Enable Auto Renew</span></asp:PlaceHolder> 

                                                <asp:Literal runat="server" Visible='<%# ( Convert.ToBoolean(Eval("AutoRenew")) && Eval("YearlyPriceId") != DBNull.Value && Eval("MonthlyPriceId").ToString() == Eval("CurPriceId").ToString()) %>' Text="<br/><b>Upgrade to Annual</b>"></asp:Literal>

                                            </asp:PlaceHolder>

                                            <asp:PlaceHolder runat="server" Visible='<%# (Eval("CurPlanId") == DBNull.Value || Eval("CurPlanId").ToString() != Eval("Id").ToString()) %>'>
                                              <asp:Literal runat="server" Visible='<%# Eval("MonthlyPricing") != DBNull.Value %>' Text='<%# String.Format("{0}{1}/ Month", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("MonthlyPricing")) %>'></asp:Literal>
                                              <asp:Literal runat="server" Visible='<%# Eval("MonthlyPricing") != DBNull.Value && Eval("YearlyPricing") != DBNull.Value %>' Text=" or "></asp:Literal>

                                              <asp:Literal runat="server" Visible='<%# Eval("YearlyPricing") != DBNull.Value %>' Text='<%# String.Format("{0}{1}/ Annum", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("YearlyPricing")) %>'></asp:Literal>
                                              <asp:Literal runat="server" Visible='<%# Eval("MonthlyPricing") == DBNull.Value && Eval("YearlyPricing") == DBNull.Value %>' Text="FREE"></asp:Literal>
                                            </asp:PlaceHolder>

                                          </p>
                                        </div>
                                      </a>
                                    </div><!--plans_button_wrap-->
    </ItemTemplate><FooterTemplate></div></FooterTemplate>
</asp:Repeater>
    <asp:SqlDataSource runat="server" ID="SDSPlans" OnSelecting="SDSSubscriptions_Selecting" ConnectionString="<%$ ConnectionStrings:localConnection %>"
        SelectCommand="select s.*, p.GroupId, p.MonthlyPricing, p.YearlyPricing, p.MonthlyPriceId, p.YearlyPriceId ,isnull(a.CurPlanId, 0) as CurPlanId, ISNULL(a.CurPlanOrder, 0) as CurPlanOrder, isnull( p.[Order], 0) as [order], CurGroupId, 
(case when Id = CurPlanId then 2 when Id <> CurPlanId and CurPlanOrder > isnull( p.[Order], 0) then 1 else 3 end ) as curType, CurPriceId, StartDate, ExpiryDate, isnull(AutoRenew, 1) as AutoRenew from S_SubscriptionPlans s 
left join (select g.[Order], p1.PlanID, p1.GroupId, MAX(CASE WHEN BillingCycle = 'Monthly' THEN PricePerCycle ELSE NULL END) AS MonthlyPricing, MAX(CASE WHEN BillingCycle = 'Annual'  THEN PricePerCycle ELSE NULL END) AS YearlyPricing,
	MAX(CASE WHEN BillingCycle = 'Monthly' THEN PlanPricingID ELSE NULL END) AS MonthlyPriceId, MAX(CASE WHEN BillingCycle = 'Annual' THEN PlanPricingID ELSE NULL END) AS YearlyPriceId from S_PlanPricing p1 inner join S_PlanGroup g on p1.GroupId = g.Id group by g.[Order], p1.PlanID, p1.GroupId) p on p.PlanID=s.Id  
left join(select top 1 p1.PlanID as CurPlanId, g.[Order] as CurPlanOrder, p1.GroupId as CurGroupId, p1.PlanPricingID as CurPriceId, ms.StartDate, ms.ExpiryDate, isnull(ms.AutoRenew,1) as AutoRenew from S_PlanPricing p1 inner join S_SubscriptionPlans s1 on p1.PlanID=s1.Id 
inner join S_PlanGroup g on p1.GroupId= g.Id inner join S_MerchantSubscriptions ms on ms.PlanID=p1.PlanID and ms.PriceID=p1.PlanPricingID and ms.MerchantID=@storegroupid where ms.Status=1 and s1.[Type]=0 and ms.PaymentStatus='Paid' order by p1.GroupId desc
)a on 1=1 where s.[Type] = 0 order by p.[Order];">
                                   <SelectParameters><asp:Parameter Name="storegroupid" DefaultValue="0" /></SelectParameters>
                               </asp:SqlDataSource>
                             
                            </div>
                            <!--col-12-->

                            <div class="col-12">
                              <h5 class="tx-dark mb-3 tx-14">Tailor your plan for greater convenience and customer presentability</h5>

                              <div class="subscription_table">

                                  <asp:Repeater ID="rptPlanSubscriptions" runat="server" DataSourceID="SDSSubscriptions">
                                      <ItemTemplate>
                                        <div class="subscription_list d-flex w-100">
                                          <div class="subscription_img d-flex justify-content-center align-items-center">
                                            <img class="img-fluid" src="<%# LogoImage(Eval("Key").ToString()) %>"">
                                          </div>
                                          <div class="subscription_name">
                                            <p class="mb-1"><%# Eval("Description").ToString() %></p>
                                              <asp:PlaceHolder runat="server" Visible='<%# (Eval("MerchantID") == DBNull.Value) %>'>
     <div class="subscription_info"><h6 class="mr-1 mb-0">Subscribe Now for 
    <asp:Label runat="server" style="text-decoration: line-through;" Visible='<%# (Eval("MonthlyPricing") != DBNull.Value && Eval("MonthlyMinPrice") != DBNull.Value && Eval("MonthlyMinPrice").ToString() != Eval("MonthlyPricing").ToString()) %>' Text='<%# String.Format("{0}{1}", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("MonthlyMinPrice")) %>'></asp:Label>
    <asp:Label runat="server" CssClass="greencolor" Visible='<%# (Eval("MonthlyPricing") != DBNull.Value) %>' Text='<%# String.Format(" {0}{1} / Month ", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("MonthlyPricing")) %>'></asp:Label>
    <asp:Label runat="server" Visible='<%# (Eval("MonthlyPricing") != DBNull.Value) %>' Text=" or "></asp:Label>
    <asp:Label runat="server" style="text-decoration: line-through;" Visible='<%# (Eval("YearlyPricing") != DBNull.Value && Eval("YearlyMinPrice") != DBNull.Value && Eval("YearlyPricing").ToString() != Eval("YearlyMinPrice").ToString()) %>' Text='<%# String.Format("{0}{1} ", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("YearlyMinPrice")) %>'></asp:Label>
    <asp:Label runat="server" CssClass="greencolor" Visible='<%# (Eval("YearlyPricing") != DBNull.Value) %>' Text='<%# String.Format("{0}{1} / Annum", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("YearlyPricing")) %>'></asp:Label>
         </h6></div>
</asp:PlaceHolder>
<asp:PlaceHolder runat="server" Visible='<%# (Eval("MerchantID") != DBNull.Value && Eval("CurPriceID") != DBNull.Value && Eval("PaymentStatus").ToString() == "Paid") %>'>
    <div class="subscription_info"><h6 class="mr-1 mb-0">Renews for 
    <asp:Label runat="server" style="text-decoration: line-through;" Visible='<%# (Eval("MonthlyPricing") != DBNull.Value && Eval("MonthlyMinPrice") != DBNull.Value && Eval("MonthlyMinPrice").ToString() != Eval("MonthlyPricing").ToString()) %>' Text='<%# String.Format("{0}{1}", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("MonthlyMinPrice")) %>'></asp:Label>
    <asp:Label runat="server" CssClass="greencolor" Visible='<%# (Eval("MonthlyPricing") != DBNull.Value) %>' Text='<%# String.Format(" {0}{1} ", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("MonthlyPricing")) %>'></asp:Label>

    <asp:Label runat="server" style="text-decoration: line-through;" Visible='<%# (Eval("YearlyPricing") != DBNull.Value && Eval("YearlyMinPrice") != DBNull.Value && Eval("YearlyPricing").ToString() != Eval("YearlyMinPrice").ToString()) %>' Text='<%# String.Format("{0}{1} ", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("YearlyMinPrice")) %>'></asp:Label>
    <asp:Label runat="server" CssClass="greencolor" Visible='<%# (Eval("YearlyPricing") != DBNull.Value) %>' Text='<%# String.Format("{0}{1} ", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("YearlyPricing")) %>'></asp:Label>

    <asp:Literal runat="server" Visible='<%# (Eval("ExpiryDate") != DBNull.Value) %>' Text='<%# String.Format("on {0:d}", Eval("ExpiryDate")) %>'></asp:Literal>                                                        
    </h6></div><%--<asp:LinkButton ID="lbtnCancelRenew" priceid='<%# Eval("CurPriceID") %>' runat="server" CssClass="remove_subscription lh-1"><i class="fa-light fa-trash-can"></i></asp:LinkButton>--%>
    <asp:PlaceHolder runat="server" Visible='<%# Convert.ToBoolean(Eval("AutoRenew")) %>'><a href="javascript:void(0)" class="cancelSubAutoRenew" curPriceId='<%# Eval("CurPriceId") %>'><i class="fa-light fa-trash-can ml-2 "></i></a></asp:PlaceHolder> 
<asp:PlaceHolder runat="server" Visible='<%# !Convert.ToBoolean(Eval("AutoRenew")) %>'><br /><a href="javascript:void(0)" class="enableSubAutoRenew tx-warning" curPriceId='<%# Eval("CurPriceId") %>'><i class="fa-light fa-square mr-2"></i>Enable Auto Renew</a></asp:PlaceHolder> 

</asp:PlaceHolder>

                                          </div>
                                          <div class="subscription_action">
                                              <asp:LinkButton ID="lnkSubscribe" runat="server" CssClass="btn btn-primary subscribe_btn_action" sbr_id='<%# Eval("Id") %>' UseSubmitBehavior="false" 
    Visible='<%# (Eval("MerchantID") == DBNull.Value || Eval("MerchantID").ToString() != this.CurrentUser.StoreGroupId.ToString() ? true: false) %>'> Subscribe </asp:LinkButton>
                                            <%--<input class="btn btn-primary" type="submit" value="Subscribe" data-toggle="modal" data-target="#PlanModalPopup">--%>
                                              <asp:Panel runat="server" CssClass="subscription_action_info" Visible='<%# (Eval("MerchantID").ToString() == this.CurrentUser.StoreGroupId.ToString() ? true: false) %>'>
                                                   <h6 class="m-0" runat="server" Visible='<%# Eval("PaymentStatus").ToString() == "Paid" && Eval("CurStatus").ToString() == "1" %>'><span class="greencolor">Active</span></h6>
                                                  <h6 class="m-0" runat="server" Visible='<%# Eval("CurStatus").ToString() != "1" %>'><span class="greencolor">Subscription Ended</span></h6>

                                                  <asp:PlaceHolder runat="server" Visible='<%# (Eval("CurStatus").ToString() == "1" && Eval("CurPriceID") != DBNull.Value && Eval("CurPriceID").ToString() != Eval("YearlyPriceID").ToString()) %>'>
<a href="javascript:void(0)" class="text-decoration subscribe_btn_action" sbr_id='<%# Eval("Id") %>' priceid='<%# Eval("YearlyPriceID") %>' style="text-decoration: underline">Upgrade to Annual</a></asp:PlaceHolder>

<asp:PlaceHolder runat="server" Visible='<%# Eval("CurStatus").ToString() != "1" %>'><a href="javascript:void(0)" class="text-decoration subscribe_btn_action" style="text-decoration: underline">Renew now to save the service active</a></asp:PlaceHolder>

                                              </asp:Panel>
                                          </div>
                                        </div><!--subscription_list-->

                                      </ItemTemplate>
                                  </asp:Repeater>

                               <asp:SqlDataSource runat="server" ID="SDSSubscriptions" OnSelecting="SDSSubscriptions_Selecting" ConnectionString="<%$ ConnectionStrings:localConnection %>"
                                    SelectCommand="declare @groupId int=(select top 1 isnull(p.GroupId, 1) as GroupId from S_MerchantSubscriptions ms inner join S_PlanPricing p on ms.PriceID=p.PlanPricingID inner join S_SubscriptionPlans s on ms.PlanID=s.Id where MerchantID=@storegroupid and ms.Status=1 and ms.PaymentStatus='Paid' and s.[Type]=0 order by p.GroupId desc);
                        select Id, PlanName, [Description], [Key] 
	                        ,MAX(CASE WHEN BillingCycle = 'Monthly' and GroupId=CurGroupId THEN PlanPricingID ELSE NULL END) AS MonthlyPriceID
                            ,MAX(CASE WHEN BillingCycle = 'Monthly' and GroupId=CurGroupId THEN PricePerCycle ELSE NULL END) AS MonthlyPricing
	                        ,MAX(CASE WHEN BillingCycle = 'Monthly' and GroupId=CurGroupId THEN DurationInDays ELSE NULL END) AS MonthlyDurationInDays
	                        ,MAX(CASE WHEN BillingCycle = 'Monthly' and GroupId=CurGroupId THEN Discount ELSE NULL END) AS MonthlyDiscount
	                        ,MAX(CASE WHEN BillingCycle = 'Annual'  and GroupId=CurGroupId THEN PlanPricingID ELSE NULL END) AS YearlyPriceID
	                        ,MAX(CASE WHEN BillingCycle = 'Annual'  and GroupId=CurGroupId THEN PricePerCycle ELSE NULL END) AS YearlyPricing
	                        ,MAX(CASE WHEN BillingCycle = 'Annual'  and GroupId=CurGroupId THEN DurationInDays ELSE NULL END) AS YearlyDurationInDays
	                        ,MAX(CASE WHEN BillingCycle = 'Annual'  and GroupId=CurGroupId THEN Discount ELSE NULL END) AS YearlyDiscount
	                        ,MAX(CASE WHEN BillingCycle = 'Monthly' and GroupId=1 THEN PricePerCycle ELSE NULL END) AS MonthlyMinPrice
	                        ,MAX(CASE WHEN BillingCycle = 'Annual' and GroupId=1 THEN PricePerCycle ELSE NULL END) AS YearlyMinPrice
	                        , CurGroupId , MerchantID, StartDate, ExpiryDate, PaymentStatus, CurPriceID, CurStatus, AutoRenew
                        from(
                        select s.Id,s.PlanName, s.[Description], s.[Key], p.PlanPricingID, p.PricePerCycle, p.BillingCycle, p.DurationInDays, p.Discount, p.GroupId
                        , ms.MerchantID, ms.StartDate, ms.ExpiryDate, ms.PaymentStatus, ms.PriceID as CurPriceID, ms.[Status] as CurStatus, isnull(@groupId, 1) as CurGroupId, isnull(ms.AutoRenew, 1) as AutoRenew
                        from S_subscriptionPlans s left join S_PlanPricing p on p.PlanID=s.Id 
                         left join S_MerchantSubscriptions ms on  ms.Status=1 and ms.PlanID= s.Id and ms.MerchantID=@storegroupid and ms.PaymentStatus not like 'Initialized'
                        where s.[Type] > 0 and p.GroupId = isnull(@groupId, 1)
                        )tmp group by Id, CurGroupId, PlanName, [Description], [Key] , MerchantID, StartDate, ExpiryDate, PaymentStatus, CurPriceID, CurStatus, AutoRenew
                        having max(GroupId)=isnull(@groupId, 1)">
                                   <SelectParameters><asp:Parameter Name="storegroupid" DefaultValue="0" /></SelectParameters>
                               </asp:SqlDataSource>

                              </div><!--subscription_table-->

                            </div>

                            <!--col-12-->
                        </div>
                    </div>
                    <!--card body-->
                </div>
                <!--card-->
            </div>


            <div class="col-12 col-lg-6 pt-3 pt-lg-0">
                <div class="card h-100">
                    <div class="card-body shadow_top p-0 position-relative">

                      <div class="viewplansec">
                        <div class="titlewrap mb-3 d-flex justify-content-between">
                          <h5 class="mr-4 mb-0 tx-dark tx-16">Subscription Plan</h5>
                          <a class="close-viewplan" href="javascript:void(0)">
                            <i class="fa-regular fa-circle-xmark tx-20"></i>
                          </a>
                        </div>

                        <div class="viewplan_content">
                          <%--<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce egestas varius metus, vel cursus ex fringilla eu. Cras ullamcorper sit amet velit varius euismod. In consequat sollicitudin lacus, nec rhoncus ante congue eget. Vestibulum ut eros feugiat, rutrum elit quis, condimentum orci. Pellentesque vitae mattis metus. Vestibulum ipsum felis, feugiat vel augue ultricies, vehicula gravida eros. Ut vitae quam risus.</p>--%>
                        </div>
                        
                      </div>

                        <div class="subscription_tab shadow_top">
                          <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">                                
                              <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true"><i class="fa-sharp-duotone fa-solid fa-calendar-days mr-2"></i>Subscription Transactions</a>
                              <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false"><i class="fa-duotone fa-regular fa-credit-card mr-2"></i>Payment Methods</a>
                            </div>
                          </nav>
                          <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                              <div class="table-responsive subscription_Trans_table">
                                  <asp:GridView ID="gvTransactions" AutoGenerateColumns="false" DataSourceID="SDSTransactions" runat="server" CssClass="table table-bordered table-head-fixed mb-0">
                                      <Columns>
                                          <asp:TemplateField HeaderText="Subscription Type"><ItemTemplate><%# String.Format("{0} {1}", Eval("Description"), Eval("BillingCycle")) %></ItemTemplate></asp:TemplateField>
                                          <asp:BoundField HeaderText="Date" DataField="CreatedDate"/>
                                          <asp:BoundField HeaderText="Payment Status" DataField="Status" />
                                          <asp:BoundField HeaderText="Reference" DataField="Ref" />
                                          <asp:TemplateField HeaderText="Invoice"><ItemTemplate><a href=""><i class="fa-regular fa-receipt tx-18"></i></a></ItemTemplate></asp:TemplateField>
                                      </Columns><EmptyDataTemplate>No data available</EmptyDataTemplate>
                                  </asp:GridView>
                                  <asp:SqlDataSource ID="SDSTransactions" SelectCommand="select l.*, '' as Ref, s.PlanName, s.Id as PlanId, s.[Description], p.BillingCycle, p.DurationInDays, 
                                      p.PricePerCycle from Payment_Logs l inner join S_PlanPricing p on p.PlanPricingID=l.PackageID inner join S_SubscriptionPlans s 
                                      on p.PlanID=s.Id where l.[Status] not like 'Pending' and MerchantID=@storegroupid order by CreatedDate desc;" 
                                      runat="server" OnSelecting="SDSSubscriptions_Selecting" ConnectionString="<%$ ConnectionStrings:localConnection %>">
                                      <SelectParameters><asp:Parameter Name="storegroupid" DefaultValue="0" /></SelectParameters>
                                  </asp:SqlDataSource>
                                
                              </div>
                            </div>

                            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                
                                <div class="table-responsive subscription_Trans_table">
                                    <asp:GridView ID="gvPaymentMethod" AutoGenerateColumns="false" DataSourceID="ODSPaymentMethods" runat="server" CssClass="table table-bordered table-head-fixed mb-0">
                                        <Columns>
                                            <asp:BoundField HeaderText="Added on" DataField="Created"/>
                                            <asp:TemplateField HeaderText="Subscription"><ItemTemplate><%# string.Format("{0} {1}", Eval("PlanName"), Eval("BillingCycle")) %></ItemTemplate></asp:TemplateField>
                                            <asp:TemplateField HeaderText="Payment type"><ItemTemplate><%# String.Format("{0} {1} {2} ***{3}", Eval("Brand"), Eval("Funding"), Eval("Type"), Eval("Last4")) %></ItemTemplate></asp:TemplateField>
                                            <asp:TemplateField HeaderText="Valid till"><ItemTemplate><%# String.Format("{0}/{1}", Eval("ExpMonth"), Eval("ExpYear")) %></ItemTemplate></asp:TemplateField>
                                        </Columns><EmptyDataTemplate>No data available</EmptyDataTemplate>
                                    </asp:GridView>
                                    <asp:ObjectDataSource ID="ODSPaymentMethods" runat="server" TypeName="RetalineProAgent.Core.Services.PaymentGateway.StripeService" 
                                        OnSelecting="ODSPaymentMethods_Selecting" SelectMethod="PaymentMethods">
                                        <SelectParameters><asp:Parameter Name="merchantId" Type="Int32" /></SelectParameters>
                                    </asp:ObjectDataSource>
  
                                </div>

                            </div>

                          </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--col-lg-6-->

        </div>


          <!-- Modal -->
          <div class="modal fade" id="PlanModalPopup" tabindex="-1" role="dialog" aria-labelledby="PlanModalPopupTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title text-uppercase tx-dark" id="select_payment_title">SCALE PLAN</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="row">
                    <div class="col-12">

                      <div class="scheme_wrap" id="row_subscription_price">


                      </div>

                    </div>

                    <div class="col-12">
                      <div class="form-group">
                          <asp:TextBox ID="txtSubscriptionRefCode" ClientIDMode="Static" runat="server" CssClass="form-control" autocomplete="off" placeholder="Discount Code"></asp:TextBox>
                      </div>
                      <div class="btnsec d-flex justify-content-between align-items-center">
                        <p class="m-0 tx-12 tx-dark">You will be paying <strong class="you_llb_paying"></strong></p>
                        <%--<input class="btn btn-primary" type="button" id="selectPrice" value="Subscribe">--%>
                          <asp:HiddenField ID="hidPriceId" runat="server" />
                          <asp:Button ID="btnSubscribe" runat="server" Text="Subscribe" OnClick="btnSubscribe_Click" OnClientClick="selectPrice()" CssClass="btn btn-primary" />
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>


<style>
    .subscription_Trans_table {
        max-height: 500px;
    }
    .viewplansec {
      opacity: 0;
      pointer-events: none;
      position: absolute;
      width: 100%;
      height: 100%;
      z-index: 11;
      background: white;
      padding: 20px;
      border-radius: 10px;
      overflow: hidden;
      -webkit-box-shadow: inset 0px 3px 0px 0px rgb(219, 217, 217);
      -moz-box-shadow: inset 0px 3px 0px 0px rgb(219, 217, 217);
      box-shadow: inset 0px 3px 0px 0px rgb(219, 217, 217);
      transition: all 0.2s ease-in-out;
    }
    .viewplansec.open-viewplan {
      opacity: 1;
      pointer-events: auto;
    }
    .viewplan_content {
      height: 100%;
      max-height: calc(100% - 40px);
      overflow-y: auto;
    }
    .subscription_tab {
      margin-top: 3px;
    }
    .subscription_tab .nav-tabs{
      background: #f0f2f7;
    }
    .subscription_tab .nav-link {
      border: 0;
      padding: 0.8rem 1rem;
      font-size: 14px;
    }
    .subscription_tab .nav-link.active{
      background: #FFF;
    }
    .subscription_alert {
        color: #2c483c;
        background-color: #f8fbcc;
        border-radius: 0;
        padding: 5px 20px;
        position: relative;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
    }
    .subscription_alert p {
      margin: 0;
      font-size: 14px;
      font-weight: 500;
    }

    .plans_button_sec {
      gap: 20px;
    }
    .plans_button_wrap {
      width: 100%;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
    }
    .plans_button {
      position: relative;
      border: 1px solid #13977f;
      width: 100%;
      border-radius: 10px;
      margin-bottom: 5px;
      min-height: calc(100% - 20px);
      -webkit-box-shadow: 0px 2px 3px 0px rgba(0,0,0,0.29);
      -moz-box-shadow: 0px 2px 3px 0px rgba(0,0,0,0.29);
      box-shadow: 0px 2px 3px 0px rgba(0,0,0,0.29);
    }
    .plans_button > div{
      overflow: hidden;
      padding:10px 10px;
      height: 100%;
      width: 100%;
    }
    .plans_button h6{
      font-weight: 600;
      line-height: 100%;
      margin: 7px 0px;
      color: #2c483b;
    }
    .plans_button p, .plans_button_wrap .viewplantrger {
      font-size: 12px;
      color: #2c483b;
      line-height: 100%;
    }
    .plans_ribbon {
      text-transform: uppercase;
      position: absolute;
      top: 15px;
      left: -22px;
      transform: rotate(-45deg);
      width: 90px;
      height: 15px;
      background: #13977f;
      font-size: 10px;
      color: #FFF;
      font-weight: 500;
    }
    .plans_button.active {
      background: #13977f;
      border: 1px solid #13977f;
      box-shadow:none;
    }
    .plans_button.active::after {
      position: absolute;
      bottom: -13px;
      left: 50%;
      margin-left: -10px;
      content: "";
      display: block;
      border-left: 13px solid transparent;
      border-right: 13px solid transparent;
      border-top: 13px solid #13977f;
    }
    .plans_button.active *{
      color: #FFF!important;
    }
    .plans_button.active .plans_ribbon{
      background: #0f7562;
    }
    .plans_button_wrap .viewplantrger {
      text-decoration: underline;
      line-height: 100%;
    }
    .subscription_table {
      border-radius: 5px;
      border: 1px solid #dee2e6;
      -webkit-box-shadow: 0px 2px 0px 0px rgba(0,0,0,0.15);
      -moz-box-shadow: 0px 2px 0px 0px rgba(0,0,0,0.15);
      box-shadow: 0px 2px 0px 0px rgba(0,0,0,0.15);
    }
    .subscription_list {
      gap: 20px;
      align-items: center;
      padding:20px 15px;
      border-bottom: 1px solid #dee2e6;
    }
    .subscription_list:last-child {
      border: 0;
    }
    .subscription_img {
      width: 60px;
      height: 40px;
    }
    .subscription_img img{
      max-height: 100%;
    }
    .subscription_name {
      width: 50%;
    }
    .subscription_name p{
      line-height: 100%;
      font-weight:500;
      color: #353a3e;
      font-size: 13px;
    }
    .subscription_info {
      display: inline;
    }
    .subscription_name h6{
      font-weight: 600;
      font-size: 13px;
      color: #1a1c1e;
      display: inline;
    }
    .remove_subscription, .remove_subscription:hover{
      color: #353a3e;
    }
    .subscription_action {
      width: 40%;
      text-align: right;
    }
    .subscription_action .btn{
      padding: 0.243rem 1rem;
      font-size: 13px;
      font-weight: 500;
    }
    .subscription_action_info h6{
      font-weight: 600;
      font-size: 15px;
      color: #131415;
    }
    .subscription_action_info a{
      font-size: 12px;
      text-decoration: underline;
    }
    .greencolor {
      color: #169880;
    }

    /*--*/
    .scheme_wrap {
      gap: 15px;
      width: 100%;
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    
    .scheme_wrap .rdiobox {
      width:100%;
      min-height: 120px;
      position: relative;
      display: grid;
      cursor: pointer;
      margin: 0;
    }
    .scheme_wrap .rdiobox input[type="radio"] {
      opacity: 1;
      margin: 0 5px 0 0;
      position: absolute;
      right: 10px;
      top: 10px;
    }
    .scheme_amout_sect {
      width: 100%;
      height: 100%;
      border-radius:5px;
      border: 1px solid #13977f;
      padding: 0 !important;
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }
    .yearly_scheme .scheme_amout_sect {
      border: 1px solid #fec97d;
    }
    .scheme_wrap .rdiobox input[type="radio"]:checked + .scheme_amout_sect {
      background: #13977f;
      color: #FFF;
    }
    .scheme_wrap .rdiobox.yearly_scheme input[type="radio"]:checked + .scheme_amout_sect {
      border-color: #13977f;
    }
    .scheme_wrap .rdiobox input[type="radio"]:checked + .scheme_amout_sect::before {
      background-color: #ffffff;
    }
    .scheme_wrap .rdiobox input[type="radio"]:checked + .scheme_amout_sect::after {
      background-color: #13977f;
    }
    .scheme_wrap .rdiobox span{
      padding: 0px;
    }
    .scheme_wrap .rdiobox > span::before {
      width: 20px;
      height: 20px;
      top: 8px;
      right: 10px;
      left: auto;
    }
    .scheme_wrap .rdiobox > span::after {
      width: 12px;
      height: 12px;
      top: 12px;
      right: 14px;
      left: auto;
    }
    .scheme_wrap .rdiobox span > span::before{
      display: none;
    }

    .scheme_amout_wrap  {
      line-height: 100%;
      display: inline-block;
      text-align: right;
    }
    .currency_symbols {
      font-size: 16px;
      vertical-align: top;
      line-height: 100%;
      right: -2px;
      position: relative;
      top: -1px;
      font-weight: 600;
    }
    .scheme_amout {
      font-size: 26px;
      font-weight: bold;
      line-height: 100%;
    }
    .scheme_info{
      display: block;
      font-size: 13px;
      text-align: right;
      text-transform: uppercase;
      font-weight: 600;
    }
    .scheme_offer{
      display: flex;
      position: absolute;
      top: 35px;
      left: -16px;
      transform: rotate(-45deg);
      background-color: #fec97d;
      color: #333;
      font-size: 12px;
      font-weight: bold;
      text-align: center;
      border-radius: 0px;
      transform-origin: top left;
      width: 70px;
      height: 40px;
      justify-content: center;
      align-items: end;
      padding: 7px 5px 7px 10px !important;
      line-height: 100%;
    }
    .scheme_wrap .scheme_offer::before, .scheme_wrap .scheme_offer::after{
      content: '';
      top: -15px;
      left: -25px;
      display: block !important;
      position: absolute;
      background: #fec97d;
      border-radius: 0;
      z-index: -1;
      width: calc(100% + 60px);
      height: calc(100% + 15px);
    }

    .scheme_wrap .rdiobox.yearly_scheme input[type="radio"]:checked + .scheme_amout_sect .scheme_offer::before, 
    .scheme_wrap .rdiobox.yearly_scheme input[type="radio"]:checked + .scheme_amout_sect .scheme_offer::after {
      background: #fff;
    }
    .scheme_offer span{
      line-height: 100%;
      text-align: left;
    }
    .cout {
      font-size: 20px;
      font-weight: 600;
      margin-right: 3px;
    }
    .cont_mnt{
      font-weight: 400;
    }
    .scheme_note {
      bottom: 0;
      position: absolute;
      font-size: 10px;
    }
    .scheme_note strong{
      font-size: 11px;
    }
    /*--*/
    @media (max-width: 767px) {
      .modal-dialog {
        max-width: 550px;
      }
      #PlanModalPopup .modal-dialog{
        transform: none;
        vertical-align: top;
        margin: auto;
        margin-top: 20vh;
      }
    }
    @media (min-width: 768px) {
      .modal-dialog {
        max-width: 550px;
      }
    }
    @media (max-width: 567px) {
      .plans_button.active::after {
        display: none;
      }
      .plans_button_sec {
       flex-wrap: wrap;
      }
      .subscription_list {
        flex-wrap: wrap;
        gap: 10px;
        padding: 15px 15px;
      }
      .subscription_action {
        width: 100%;
        margin-top: 5px;
      }
      .subscription_name {
        width: calc(100% - 70px);
      }
      .modal-dialog {
        max-width: 90%;
      }
      .scheme_wrap {
        flex-wrap: wrap;
      }
    }
  </style> 

  <script>

      jQuery(document).ready(function ($) {
          $('.viewplantrger').on("click", function (event) {
              event.preventDefault();
              $('.viewplansec').addClass('open-viewplan');
              $('.viewplan_content').html('<div class="processing_loader"> ..loading..</div>');
              var planId = $(this).attr('planId');
              onSuccess = function (data) {
                  $('.processing_loader').removeClass('processing_loader');
                  $('.viewplan_content').html('');
                  if (data && data.data) {
                      var template = '<table class="table table-bordered table-head-fixed mb-0" cellspacing="0" ><thead><tr><th>Feature</th><th>Availability</th></tr></thead><tbody>[TR]</tbody></table>';
                      var trdata = '';
                      for (var i = 0; i < data.data.length; i++) {
                          trdata += '<tr><td>' + data.data[i].Description + '</td><td>' + data.data[i].Value + '</td></tr>'
                      }
                      $('.viewplan_content').append(template.replace('[TR]', trdata));
                  }
                  else {
                      $('.viewplan_content').html('<p>Failure! No data loaded.</p>');
                  }
              };

              onError = function (data) {
                  $('.processing_loader').removeClass('processing_loader');
                  $('.viewplan_content').html('<p>Failure! No data loaded.</p>');
              };
              retMaster.ajax.JSONRequest('/api/Upgrade/SubscriptionFeatures?subscriptionId=' + planId, 'GET', null, onSuccess, onError);

          });

          $('.close-viewplan').on("click", function (event) {
              event.preventDefault();
              $('.viewplansec').removeClass('open-viewplan');
          });
      });
      $('.cancelSubAutoRenew').on('click', function (e) {
          e.stopPropagation();
          var prid = $(this).attr('curPriceId');
          $('#<%= hidCanelPrId.ClientID %>').val(prid);
          $('#modalcancelAutoRenew').modal('show');
          return false;
      });

      $('.enableSubAutoRenew').on('click', function (e) {
          e.stopPropagation();
          var prid = $(this).attr('curPriceId');
          $('#<%= hidEnablePrId.ClientID %>').val(prid);
          $('#modalEnableAutoRenew').modal({ backdrop: 'static' });
          return false;
      });
      function selectPrice() {
          $('#<%= hidPriceId.ClientID %>').val($('#row_subscription_price').find('input[type=radio]:checked').attr('priceid'));
      }
      retMaster.properties.currency = '<%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %>';
  </script>


<%if (ConfigurationManager.AppSettings.Get("PaymentGateway").Contains(".revolut.com"))
    { %>

<script src="<%= String.Format("{0}/embed.js", ConfigurationManager.AppSettings.Get("PaymentGateway")) %>"></script>
<script type="text/javascript">
    retMaster.properties.paymentGateway = 'revolut';
</script>

<% }
    else if (ConfigurationManager.AppSettings.Get("PaymentGateway").Contains(".stripe.com"))
    { %>

<script src="https://js.stripe.com/v3/"></script>
<script type="text/javascript">
    retMaster.properties.paymentGateway = 'Stripe';
</script>
    <%}
    else if (ConfigurationManager.AppSettings.Get("PaymentGateway").Contains("razorpay"))
    { %>

<script src = "https://checkout.razorpay.com/v1/checkout.js"></script>
<script type="text/javascript">
    retMaster.properties.paymentGateway = 'Razorpay';
</script>
    <%} %>

      <div id="modalpayment" class="modal fade">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-header pd-x-20">
            <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">Upgrade subscription - Add Card</h6>
          </div>
          <div class="modal-body pd-20">
            <h5 class=" lh-3 mg-b-20"><a href="" class="tx-inverse hover-primary">Add payment method for activating the subscription</a></h5>
            <p class="mg-b-5">Add your card to set up your subscription payment method. Once added, your card will be charged for the selected plan or feature and automatically billed upon subscription renewal. This ensures seamless access to premium features as soon as your subscription becomes active.</p>



            <div class="section-wrapper upgrade_otp p-3">
                <div class="form-layout form-layout-4 otp_input_sec">
                    <div class="row">
                        <div class="col-12 d-flex flex-wrap align-items-end justify-content-center justify-content-sm-start mg-t-10 mg-sm-t-0">
                            <div class="divOuter">
                                <div class="divInner" id="upgrademodelcontent">
                                    
                                </div>
                            </div>
                            
                        </div>


                    </div>
                    <!-- row -->

                </div>
                                <asp:Label ID="lblPaymentResult" ClientIDMode="Static" runat="server" ForeColor="Red"></asp:Label>

            </div><!-- section-wrapper -->

          </div><!-- modal-body -->
          <div class="modal-footer">

  <button type="button" class="btn btn-primary" id="upgradebtn_savecard">Submit</button>
  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>

        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->

      <div id="modalcancelAutoRenew" class="modal fade">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-header pd-x-20">
            <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">Cancel Auto Renew</h6>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body pd-20">
            <p class="mg-b-5">This action will be impacting your store functions and the features activated, after the current subscription period. Are you sure you want to stop auto renew?</p>


          </div>
          <div class="modal-footer"><asp:HiddenField ID="hidCanelPrId" runat="server" />
    <asp:LinkButton runat="server" Text="Submit" CssClass="btn btn-primary" ID="btnCancelRenew" OnClick="btnCancelRenew_Click"></asp:LinkButton>
  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>

        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->    


      <div id="modalEnableAutoRenew" class="modal fade">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-header pd-x-20">
            <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">Enable Auto Renew</h6>
          </div>
          <div class="modal-body pd-20">
            <p class="mg-b-5">The subscription will be auto renewed next time on expiry date which will keep the site continued with the exisitng functions. Are you ok to proceed with enable auto renew?</p>

          </div>
          <div class="modal-footer"><asp:HiddenField ID="hidEnablePrId" runat="server" />
    <asp:LinkButton runat="server" Text="Submit" CssClass="btn btn-primary" ID="btnEnableAutoRenew" OnClick="btnEnableAutoRenew_Click"></asp:LinkButton>
  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>

        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->    


</asp:Content>

