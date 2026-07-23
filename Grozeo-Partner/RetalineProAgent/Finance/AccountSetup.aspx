<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="AccountSetup.aspx.cs" Inherits="RetalineProAgent.AccountSetup" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
              <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <h6 class="slim-pagetitle">Account Setup</h6>
    <p class="mb-0">You can see Account Setup here</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <div class="row">
		<div class="col-12">
       <div class="card">
		   <div class="card-header shadow_top" >
            <div class="row row-sm">
						<div class="col-12 col-lg-4 align-items-end d-flex">
							<div class="input-group input_search_box">
								<input type="text" style="display:none" />
                                  <input type="password" style="display:none" />
							<asp:TextBox ID="txtSearch" CssClass="form-control" runat="server" autocomplete="off"></asp:TextBox>							
								<asp:LinkButton ID="btnsearch" runat="server" OnClick="btnsearch_Click" CssClass="input-group-append">
									<div class="btn bd bd-l-0 tx-gray-600" border-top-left-radius: 0; border-bottom-left-radius: 0;">
										<i class="fa fa-search mt-1"></i>										
									</div>
								</asp:LinkButton>

							</div>
						</div>

						<div class="col-12 d-none col-lg-8 align-items-end d-lg-flex justify-content-end">

							<div class="d-flex align-items-center">
								<div class="d-flex align-items-center mr-3">
									<span class="mr-1 p-2" style="background-color:#CA8317 ;"></span>
									<span>Account Type</span>
								</div>
								<div class="d-flex align-items-center mr-3">
									<span class="mr-1 p-2" style="background-color:#007bff;"></span>
									<span>Group</span>
								</div>
								<div class="d-flex align-items-center">
									<span class="mr-1 p-2" style="background-color:#28a745 ;"></span>
									<span>Ledger</span>
								</div>
								
							</div>
						</div>
					</div>
		   </div>

        <div class="card-body p-3">
    <asp:TreeView ID="TreeView1" runat="server" ExpandDepth="1"  ShowExpandCollapse="true"></asp:TreeView>
        </div>
		   </div>
    
		</div>
	</div>
    <asp:SqlDataSource ID="SDSGroups" runat="server" SelectCommand="select * from(
select id, [name], parent_id, isSystem,  1 as actype, account_types_id as typeid from groups --order by parent_id
union all
select -2, [name], groups_id, isSystem,  2, -1 from [ledger]
)tmp order by parent_id
" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>

    <asp:SqlDataSource ID="SDSAccountTypes" runat="server" SelectCommand="select * from account_types" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>

	<style>
					
					.card-body > div > table td{
						padding: 2px 0px;
					}
					.card-body > div > table + div {
						display: block;
						padding: 5px 0px;
						/* border: 1px solid red; */
					}
					.card-body > div > table td .text-warning{
						color:#CA8317 !important;
					}
					
				</style>




</asp:Content>
