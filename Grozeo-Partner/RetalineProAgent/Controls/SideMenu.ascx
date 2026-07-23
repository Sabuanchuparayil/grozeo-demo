<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="SideMenu.ascx.cs" Inherits="RetalineProAgent.Controls.SideMenu" %>
<nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
            <asp:PlaceHolder ID="plcManager" runat="server" Visible="false">

                <li class="nav-item" style="border-bottom: 1px solid #4f5962;">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-copy"></i>
              <p>
                Admin
                <i class="fa fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/Manage/stores" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Stores</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/Manage/ManageStore" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>New Store</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/Manage/usertostore" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Store User</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="/masterdataimport" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Import Master Data</p>
                </a>
              </li>

            </ul>
          </li>

            </asp:PlaceHolder>


          <%--<li class="nav-item">
            <a href="/" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
                <!--<i class="right fas fa-angle-left"></i>-->
              </p>
            </a>
            
          </li>--%>
          
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-landmark"></i>
              <p>Store
                <i class="fa fa-angle-left right"></i>
                <%--<span class="badge badge-info right">2</span>--%>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/tenant/store/StoreSettings" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Manage Store</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="/Branches" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Manage Branches</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/InventoryMapping" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Select Items for Sale</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/Advertisement" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Advertisement</p>
                </a>
              </li>

              <%--<li class="nav-item">
                <a href="/Margin" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Margin Setup</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Customer Data</p>
                </a>
              </li>--%>
              
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-table"></i>
              <p>
                Products
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/ItemsForSale" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Current Stock</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/InventoryMapping" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Select item for Sale</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon nav-icon ion ion-bag"></i>
              <p>
                Orders
                <i class="fa fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                <a href="/PendingOrders" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>View Orders</p>
                </a>
              </li>
<%--              <li class="nav-item">
                <a href="/OnlineOrders" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Online Orders</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/SalesOrders" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Sales Orders</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/IncompleteOrders" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Incomplete Orders</p>
                </a>
              </li>
                <li class="nav-item">
                <a href="/CancelledOrders" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Cancelled Orders</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/OrderPacking" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Packing Order</p>
                </a>
              </li>--%>
                <li class="nav-item">
                <a href="/ScheduledJobs" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Scheduled Packing</p>
                </a>
              </li>
              
            </ul>
          </li>
            <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-truck"></i>
              <p>
                Delivery
                <i class="fa fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/DeliveryRules" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Delivery Rules</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/DeliveryJobs" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Delivery Jobs</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/ScheduleJob" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Scheduled Jobs</p>
                </a>
              </li>
                <li class="nav-item">
                <a href="/LiveVehicles" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Live Vehicles</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/VehicleHistory" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Vehicle History</p>
                </a>
              </li>
                <%--<li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Live Polls</p>
                </a>
              </li>--%>
              
            </ul>
          </li>
            <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-arrow-circle-down"></i>
              <p>
                Returns
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/SpotReturn" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Spot Return</p>
                </a>
              </li>
            </ul>
          </li>

            <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-people-arrows"></i>
              <p>
                Customers
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/Customers" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Customers</p>
                </a>
              </li>
                <li class="nav-item">
                <a href="/Feedback" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Customer Messages</p>
                </a>
              </li>
                <li class="nav-item">
                <a href="/TeleOrders" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Customer Support</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-tasks"></i>
              <p>Accounts
                <i class="fa fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Sales Income</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Affliliate Income</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Statement</p>
                </a>
              </li>
            </ul>
          </li>
<%--            <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-edit"></i>
              <p>Website
                <i class="fa fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/Advertisement" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Advertisement</p>
                </a>
              </li>
            </ul>
          </li>--%>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fa fa-question-circle"></i>
              <p>Support
                <i class="fa fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Technical Help</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Message Centre</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Video Help</p>
                </a>
              </li>
            </ul>
          </li>
            <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-print"></i>
              <p>Reports
                <i class="fa fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/SalesReport" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Sales Report</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/GSTReport" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>GST Report</p>
                </a>
              </li>
                <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Debit Note Report</p>
                </a>
              </li>
                <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Credit Note Report</p>
                </a>
              </li>
                <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>HSN / GST Report</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Stock Return Report</p>
                </a>
              </li>
                <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Inventory Report</p>
                </a>
              </li>
                <li class="nav-item">
                <a href="/DeliveryResourceReport" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Delivery Resource Report</p>
                </a>
              </li>
            </ul>
          </li>


           <asp:PlaceHolder runat="server" ID="plcUsers">
            <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-user"></i>
              <p>
                Users
                <i class="fa fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/Users" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Admin Users</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/OrderPicker" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Order Picker</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/DeliveryBoys" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Delivery Boys</p>
                </a>
              </li>

            </ul>
          </li>   
        </asp:PlaceHolder>
            <li class="nav-item"><a href="/Tenant/Appearance/Settings" class="nav-link"><i class="nav-icon fas fa-edit"></i><p>Website</p></a></li>
            <%--<li class="nav-item"><asp:LoginStatus CssClass="nav-link" runat="server"  /></li>--%>
        </ul>
      </nav>
