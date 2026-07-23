<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="DeligateTenant.aspx.cs" Inherits="RetalineProAgent.Tenant.DeligateTenant" MasterPageFile="~/Tenant/TenantMaster.master" %>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

    <div class="card">
        <asp:PlaceHolder ID="plcStoreList" runat="server">
            <div class="card-header shadow_top">
                <div class="row row-sm mt-2">
                    <div class="col-12 col-lg-9">
                        <h6 class="mb-1 tx-dark">Store Deligation</h6>
                        <p class="mg-b-0">The user can deligate to the selected store.</p>
                    </div>
                </div>
                
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
                  <asp:Repeater runat="server" ID="rptStores">
                      <HeaderTemplate><table class="table table-bordered mb-0">
                          <thead>
                            <tr><th>Merchant Name</th><th>Location</th><th>Contact No.</th><th width="86px">Status</th><th width="60px"></th><th width="140"></th></tr>
                          </thead><tbody>
                      </HeaderTemplate>
                      <ItemTemplate>
                          
                          Deligation

                      </ItemTemplate>
                      <FooterTemplate>

                          </tbody></table></FooterTemplate>
                  </asp:Repeater>
                  <asp:Label ID="lblResult" runat="server"></asp:Label>
          </div>
        </div><!-- card-body -->
            
        </asp:PlaceHolder>
    </div><!-- card -->

</asp:Content>
