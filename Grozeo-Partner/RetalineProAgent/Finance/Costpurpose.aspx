<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master"  CodeBehind="Costpurpose.aspx.cs" Inherits="RetalineProAgent.Finance.Costpurpose" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
      <a href="/Finance/Navigations/Costallocationandautoposting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Manage Cost Purpose</h6>
    <p class="mb-0">You can see Manage Cost Purpose here</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <section class="content">
        <div class="row row-sm">
            <div class="col-12 pb-3">
                <div class="card m-0 h-100">
                    <div class="card-header">
                        <div class="row row-sm">
                            <div class="col-12 col-lg-5">
                                <div class="d-inline-block mb-2 mb-lg-0 mr-lg-3">
                                    <a data-toggle="modal" href="#Pupadd" class="btn btn-primary py-1 AddVoucherBTN">Create New</a>
                                </div>
                            </div>
                            <div class="col-12 col-lg-7 d-flex align-items-end">
                                <div class="input-group input_search_box">
                                    <input type="text" style="display: none" />
                                    <input type="password" style="display: none" />
                                    <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search"  autocomplete="off"></asp:TextBox>
                                    <asp:LinkButton runat="server" CssClass="input-group-append">
                                        <div class="btn bd bd-l-0 tx-gray-600" >
                                          <i class="fa fa-search"></i>
                              </div>
                                    </asp:LinkButton>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <asp:GridView ID="gvcostpurpose" OnDataBound="gvcostpurpose_DataBound" runat="server" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table" BorderStyle="Solid"
                                DataSourceID="SDSCostpurpose" OnRowUpdating="gvcostpurpose_RowUpdating" DataKeyNames="Id" AllowPaging="true" PageSize="5">
                                <Columns>
                                    <asp:TemplateField HeaderText="Cost Purpose" HeaderStyle-HorizontalAlign="Center" ItemStyle-HorizontalAlign="Left" SortExpression="cost_purpose">
                                        <ItemTemplate>
                                            <asp:Literal ID="ltrcost" runat="server" Text='<%# Eval("cost_purpose") %>'></asp:Literal>
                                        </ItemTemplate>
                                        <EditItemTemplate>
                                            <asp:TextBox ID="txt_Name" runat="server" Text='<%#Bind("cost_purpose") %>'></asp:TextBox>
                                        </EditItemTemplate>
                                    </asp:TemplateField>
                                    <asp:TemplateField>
                                        <ItemTemplate>
                                            <asp:LinkButton ID="btn_Edit" runat="server" Text="Edit" CausesValidation="false" CommandName="Edit" CommandArgument='<%# Eval("id")%>' />
                                        </ItemTemplate>
                                        <EditItemTemplate>
                                            <asp:Button ID="btn_Update" runat="server" CssClass="btn btn-sm btn-outline-primary mr-2" CausesValidation="false" Text="Update" CommandName="Update" CommandArgument='<%# Bind("id") %>' />
                                            <asp:Button ID="btn_Cancel" runat="server" CssClass="btn btn-sm btn-outline-secondary" Text="Cancel" CommandName="Cancel" />
                                        </EditItemTemplate>
                                    </asp:TemplateField>
                                </Columns>
                            </asp:GridView>
                            <asp:SqlDataSource runat="server" ID="SDSCostpurpose" ConnectionString="<%$ connectionStrings:FinascopConnection %>"
                                SelectCommand="select id,cost_purpose from  cost_purpose  where (trim(@search) like '' or cost_purpose like CONCAT('%', @search, '%')) order by cost_purpose"
                                UpdateCommand="UPDATE [cost_purpose] SET cost_purpose=@cost_purpose WHERE id=@Id" OnUpdating="SDSCostpurpose_Updating1">
                                <SelectParameters>
                                    <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                                </SelectParameters>
                                <UpdateParameters>
                                    <asp:Parameter Name="cost_purpose" />
                                </UpdateParameters>
                            </asp:SqlDataSource>
                        </div>

                    </div>

                </div>
            </div>
        </div>
        <div class="modal fade" id="Pupadd" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog w-100">
                <div class="modal-content">
                    

                    <div class="modal-header">
                        <button type="button" class="close position-absolute" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h5 class="mb-0">Create new Cost Purpose </h5>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label class="mb-0">Cost Purpose</label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <asp:TextBox runat="server" CssClass="form-control" ID="txtGroupName" autocomplete="off"></asp:TextBox>
                            <asp:RequiredFieldValidator runat="server" ValidationGroup="AddCostPurpose" ControlToValidate="txtGroupName" ForeColor="Red" ErrorMessage="Please give a Cost Purpose"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <asp:Label ID="lbgroupid" CssClass="tx-danger" runat="server"></asp:Label>
                        <asp:Label ID="lbprime" runat="server"></asp:Label>
                        <div class="form-group mb-0 float-right">
                            <asp:LinkButton runat="server" ID="btnsave" ValidationGroup="AddCostPurpose" CssClass="btn btn-primary mr-2" OnClick="btnsave_Click">Save</asp:LinkButton>
                            <a href="javascript:void(0)" class="btn btn-secondary"  data-dismiss="modal">Cancel</a>
                        </div>                            
                    </div>


                </div>
            </div>
        </div>
    </section>  
</asp:Content>

