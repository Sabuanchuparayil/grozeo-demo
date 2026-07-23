<%@ Page Language="C#" MasterPageFile="~/Finance/FinanceMaster.master" AutoEventWireup="true" CodeBehind="ValueHead.aspx.cs" Inherits="RetalineProAgent.Finance.ValueHead" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">
      <a href="/Finance/Navigations/Costallocationandautoposting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
    <script type="text/javascript">
        function on() {
            document.getElementById("overlay").style.display = "flex";
        }
    </script>

    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link href="/content/lib/summernote/css/summernote-bs4.css" rel="stylesheet">
    <script src="/content/lib/summernote/js/summernote-bs4.min.js"></script>
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
        <a href="/Finance/Navigations/Costallocationandautoposting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Manage Value Head</h6>
     <p class="mb-0">You can see Manage Value Head here</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">   
        <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header shadow_top">
               <div class="row row-sm justify-content-between">
            <div class="col-12 col-lg-3">
                <div class="row row-sm">
                    <div class="form-group col-12 col-md-12 mb-2 mb-lg-0 pr-md-1">
                        <label for="seltype" class="tx-dark" runat="server">Event</label>
                        <asp:DropDownList ID="selEvent" CssClass="form-control py-0" AutoPostBack="true" runat="server">
                            <asp:ListItem Text="All Events" Value="0"></asp:ListItem>
                            <asp:ListItem Text="Checkout" Value="1"></asp:ListItem>
                            <asp:ListItem Text="Order Placing" Value="2"></asp:ListItem>
                            <asp:ListItem Text="Packing" Value="3"></asp:ListItem>
                            <asp:ListItem Text="Cancellation" Value="4"></asp:ListItem>
                            <asp:ListItem Text="Payment Success" Value="5"></asp:ListItem>
                            <asp:ListItem Text="Delivery Confirmation" Value="6"></asp:ListItem>
                            <asp:ListItem Text="Pickup for Delivery" Value="7"></asp:ListItem>
                            <asp:ListItem Text="Platform Confirmation" Value="8"></asp:ListItem>
                        </asp:DropDownList>
                    </div>
                </div>
                
            </div>
            <div class="col-12  col-lg-6">
                <div class=" w-100 d-flex flex-wrap flex-lg-nowrap align-items-end">

                    <div class="form-group w-100 mb-2 mb-lg-0 col-12 col-md-6 pl-0 pr-0 pr-md-2">
                        <label for="seltype" class="tx-dark" runat="server">Type</label>
                        <asp:DropDownList ID="dlentrytpeupdae" CssClass="form-control py-0" AutoPostBack="true" runat="server">
                            <asp:ListItem Text="All" Value="-1"></asp:ListItem>
                            <asp:ListItem Text="Computation" Value="1"></asp:ListItem>
                            <asp:ListItem Text="Posting" Value="2"></asp:ListItem>
                            <asp:ListItem Text="Allocation" Value="3"></asp:ListItem>
                        </asp:DropDownList>
                    </div>          
                    <div class="form-group d-flex mb-2 mb-lg-0 col-12 col-md-6 pr-0 pr-md-1 pl-0">
                           
                        <div class="input_search_box">
                              <input type="text" style="display:none" />
                            <input type="password" style="display:none" />
                            <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                            <asp:LinkButton runat="server" CssClass="input-group-append">                        
                            </asp:LinkButton>                             
                            <asp:LinkButton ID="lbtnSearch" CssClass="btn bd bd-l-0 tx-gray-600 "  runat="server" autocomplete="off"><i class="fa fa-search mt-1"></i></asp:LinkButton>
                        </div>
                            
                        </div>
                </div>

        </div>

        <div class="col-3 col-lg-3 d-flex align-items-end">
                <a href="javascript:void(0)" id="hlAddNewHead" class="btn btn-outline-success btn-sm py-1 px-3 mx-2 btnFormula">Add New</a>
            <asp:HiddenField ID="hidNewFormula" runat="server" />
        </div>

      </div>
                </div>

                <div class="card-body">

                    <div class="table-responsive mailbox-messages">
                <asp:GridView AutoGenerateColumns="false" ID="gvValueHeads" runat="server" CssClass="table table-bordered gridview_table" BorderStyle="Solid"
                    DataSourceID="SDSValueHeads" AllowPaging="true" PagerStyle-CssClass="pg_table" PageSize="15" DataKeyNames="Id">
                    <Columns>
                        
                        <asp:TemplateField HeaderText="Name" SortExpression="name">
                            <ItemTemplate><label class="lblColName"><%# Eval("Name") %></label><br /><small class="colDesc"><%# Eval("Description") %></small></ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="Event" SortExpression="event">
                            <ItemTemplate><label class="lblEventName"><%# Eval("event") %></label><br /><small>(<%# Eval("type") %>)</small></ItemTemplate>
                        </asp:TemplateField>

                        <asp:BoundField HeaderText="Calculation" DataField="calculation" SortExpression="calculation" />

                        <%--<asp:BoundField HeaderText="Formula" DataField="formula" />--%>
                        <asp:TemplateField>
                            <ItemTemplate>
                                <a href="javascip:void(0)" class="btnFormula" rowid="<%# Eval("id") %>" sourcetype="<%# Eval("sourceType") %>" eventid="<%# Eval("eventId") %>"
                                    displayorder="<%# Eval("displayorder") %>" costcentre_enabled="<%# Eval("costcentre_enabled") %>"><i class="fa fa-pencil-alt"></i></a>
                                <asp:HiddenField ID="hidFormula" runat="server" Value='<%# Eval("calculation") %>' />
                            </ItemTemplate>
                        </asp:TemplateField>

                    </Columns>
                    <EmptyDataTemplate>
                                <div class="text-center">
                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                    <h6 class="mb-3">No record available</h6>
                                </div>
                            </EmptyDataTemplate>
                     <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                      <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                </asp:GridView>
                <asp:SqlDataSource runat="server" ID="SDSValueHeads" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"  ProviderName="MySql.Data.MySqlClient"
                    SelectCommand="select * from finance_calculation_heads h where (trim(@search) like '' or Name like CONCAT('%', @search, '%')) and ( @rectype = -1 OR (@rectype = 1 AND h.type = 'Computation') OR (@rectype = 2 AND h.type = 'Posting') 
                        OR (@rectype = 3 AND h.type = 'Allocation')) and ( @eventid = 0 OR (@eventid = 1 AND h.event = 'Checkout') OR (@eventid = 2 AND h.event = 'Order Placing') OR (@eventid = 3 AND h.event = 'Packing')
                        OR (@eventid = 6 AND h.event = 'Delivery Confirmation') OR (@eventid = 7 AND h.event = 'Pickup for Delivery') OR (@eventid = 4 AND h.event = 'Cancellation') OR (@eventid = 5 AND h.event = 'Payment Success')
                        OR (@eventid = 8 AND h.event = 'Platform Confirmation'))  order by displayorder">
                    <SelectParameters>
                     <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                        <asp:ControlParameter Name="rectype" ControlID="dlentrytpeupdae" ConvertEmptyStringToNull="false" />
                        <asp:ControlParameter Name="eventid" ControlID="selEvent" ConvertEmptyStringToNull="false" />
                    </SelectParameters>
                </asp:SqlDataSource>
            </div>

                </div>
            </div>
            
        </div>
    </div>



    <div class="modal" id="formulaModal" data-backdrop="static">
	<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content tx-size-sm">
          
           <div class="modal-header">
           <h4 class="modal-title">Valuehead</h4>
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>

        <div class="modal-body">

            <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                          <label class="w-100 text-left tx-dark">Value Head: <span class="tx-danger">*</span></label>
                          <asp:TextBox ID="txtValueHead" runat="server" CssClass="form-control" placeholder="Enter value-head name" autocomplete="nofill" onkeypress="return allowAlphanumericUnderscore(event)"/>
                          <asp:RequiredFieldValidator runat="server" ControlToValidate="txtValueHead" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Value-head name is required" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                      </div><!-- col-4 -->

                    <div class="col-lg-7">
                        <div class="form-group">
                          <label class="w-100 text-left tx-dark">Source Type:</label>
                          <asp:RadioButton ID="rbtnTable" runat="server" Text="Order tables" GroupName="sourcetype" />&nbsp;
                          <asp:RadioButton ID="rbtnCalculation" GroupName="sourcetype" Checked="true" runat="server" Text="Calcuation" />&nbsp;
                          <asp:RadioButton ID="rbtnTernaryOperator" GroupName="sourcetype" Checked="false" runat="server" Text="Expression" />
                        </div>
                      </div><!-- col-4 -->
                    <div class="col-lg-5">
                        <div class="form-group">
                          <label class="w-100 text-left tx-dark">&nbsp;</label>
                          <asp:CheckBox ID="chkHasCostCenter" runat="server" Text="Cost center enabled?" />
                        </div>
                      </div><!-- col-4 -->

                    <div class="col-lg-12">
                        <div class="form-group">
                          <label class="w-100 text-left tx-dark">Description:</label>
                          <asp:TextBox ID="txtDescription" runat="server" CssClass="form-control" placeholder="Enter Description" autocomplete="nofill"/>
                        </div>
                      </div><!-- col-4 -->

                    <div class="col-lg-6">
                        <div class="form-group">
                        <label class="w-100 text-left tx-dark">Event: <span class="tx-danger">*</span></label>
                        <asp:DropDownList ID="selPopupEvent" CssClass="form-control py-0" runat="server">
                            <asp:ListItem Text="Select Event" Value=""></asp:ListItem>
                            <asp:ListItem Text="Checkout" Value="1"></asp:ListItem>
                            <asp:ListItem Text="Order Placing" Value="2"></asp:ListItem>
                            <asp:ListItem Text="Packing" Value="3"></asp:ListItem>
                            <asp:ListItem Text="Cancellation" Value="4"></asp:ListItem>
                            <asp:ListItem Text="Payment Success" Value="5"></asp:ListItem>
                            <asp:ListItem Text="Delivery Confirmation" Value="6"></asp:ListItem>
                            <asp:ListItem Text="Pickup for Delivery" Value="7"></asp:ListItem>
                            <asp:ListItem Text="Platform Confirmation" Value="8"></asp:ListItem>
                        </asp:DropDownList>

                          <asp:RequiredFieldValidator runat="server" ControlToValidate="selPopupEvent" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select event" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                      </div><!-- col-4 -->

                 <div class="col-lg-6">
                        <div class="form-group">
                        <label class="w-100 text-left tx-dark">Type: <span class="tx-danger">*</span></label>
                        <asp:DropDownList ID="selpouptype" CssClass="form-control py-0" runat="server">
                            <asp:ListItem Text="Select Type" Value=""></asp:ListItem>
                            <asp:ListItem Text="Computation" Value="1"></asp:ListItem>
                            <asp:ListItem Text="Posting" Value="2"></asp:ListItem>
                            <asp:ListItem Text="Allocation" Value="3"></asp:ListItem>                     
                        </asp:DropDownList>
                          <asp:RequiredFieldValidator runat="server" ControlToValidate="selpouptype" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Select Type" ValidationGroup="ValueHead" ForeColor="Red"></asp:RequiredFieldValidator>
                        </div>
                      </div>

            </div>
        <!-- Constant input -->
        <div class="row row-sm calcrow">
              <div class="col-lg-12">
                <asp:HiddenField ID="hidSelectedFormula" runat="server" /><asp:HiddenField ID="hidSelRowId" runat="server" />
                <label class="form-control-label mb-1 w-100 tx-dark" for="selField">Select Field:</label>
                <asp:DropDownList ID="selField" runat="server" CssClass="form-control select2-show-search formulaField" DataSourceID="SDSFields" DataTextField="name" DataValueField="column_name" AppendDataBoundItems="true">
                    <asp:ListItem Text="Select Field" Value=""></asp:ListItem>
                </asp:DropDownList>
                <asp:SqlDataSource runat="server" ID="SDSFields" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"  ProviderName="MySql.Data.MySqlClient"
                    SelectCommand="select * from finance_calculation_heads order by displayorder">
                </asp:SqlDataSource>
              </div>

                <div class="input-group input-group-sm mt-3">
                    <!-- Operator buttons -->
                    <div id="operators" class="mr-4">
                        <button id="addBtn" type="button" class="btnOperation" onclick="addOperator('+')">+</button>
                        <button id="subBtn" type="button" class="btnOperation" onclick="addOperator('-')">-</button>
                        <button id="mulBtn" type="button" class="btnOperation" onclick="addOperator('*')">*</button>
                        <button id="divBtn" type="button" class="btnOperation" onclick="addOperator('/')">/</button>
                        <button id="bracaterigtBtn" type="button" class="btnOperation" onclick="addOperator('(')">(</button>
                        <button id="bracateleftBtn" type="button" class="btnOperation" onclick="addOperator(')')">)</button>
                    </div>

                <input class="form-control formulaField" placeholder="value" type="number" id="constantInput">
                    <button id="addConstantBtn" type="button" class="input-group-append formulaField" onclick="addConstant()">
                        <div class="btn btn-primary">
                          Add Constant
                        </div>
                    </button>
                </div>

        </div>
        
            <div class="row row-sm calcrow">
                <div class="col-lg-12 mt-3" style="min-height: 80px; border: solid 1px;">
                <b>Calculation:</b> <button type="button" value="Delete" class="btn btn-outline-warning btn-sm py-1 px-3 mx-2 mt-2" onclick="deleteFormula()">Delete</button><br /> <span id="formulaDisplay"></span>
                </div>
            </div>

            <div class="row row-sm ternaryExpression">
                <div class="col-lg-10">
                    <label class="w-100 text-left tx-dark">Source Field: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="ddlField" runat="server" CssClass="form-control select2-show-search ">


                    </asp:DropDownList>

                </div>
                <div class="col-lg-2">
                        <button id="btnAddField" type="button"  style="margin-top: 26%; border:none" onclick="addField()">
                            <div class="btn btn-primary">
                              Add Field
                            </div>
                        </button>
                    </div>

                <div class="col-lg-12 mt-3" style="min-height: 80px; border: none;">
                    <asp:TextBox ID="tfdTernaryOperator" runat="server" TextMode="MultiLine" style="width: 100%; height: 100%; box-sizing: border-box;" ></asp:TextBox>>
                </div>
            </div>

            <div class="row sourcefieldrow">
                <div class="col-lg-12">
                    <label class="w-100 text-left tx-dark">Source Field: <span class="tx-danger">*</span></label>
                    <asp:DropDownList ID="selSourceField" runat="server" CssClass="form-control select2-show-search ">
                       

                    </asp:DropDownList>

                </div>
            </div>
            

            <div class="row positionRow">
                <div class="col-lg-12">
                    <label class="w-100 text-left tx-dark">Position: <span class="tx-danger">*</span></label>
            <asp:DropDownList ID="selPositionBefore" runat="server" CssClass="form-control select2-show-search" DataSourceID="SDSFields" DataTextField="name" DataValueField="displayorder" AppendDataBoundItems="true">
                <asp:ListItem Text="Default Value" Value="0"></asp:ListItem>
            </asp:DropDownList>

                </div>
            </div>

        </div>
        <div class="modal-footer">
          <asp:LinkButton runat="server" CssClass="btn btn-outline-success btn-sm py-1 px-3 mx-2" ID="btnSaveFormula" OnClientClick="return saveFormula()" ValidationGroup="ValueHead" OnClick="btnSaveFormula_Click">Save</asp:LinkButton>
          <asp:Button  data-dismiss="modal" ID="btnclose" runat="server" Text="Close" CssClass="btn btn-outline-dark btn-sm py-1 px-3 mx-2" />
           
        </div>
          </div>
      </div>
    </div>

<script type="text/javascript">

    let formulaArray = [];
    let currentFormula = '';
    let currentField = '';

    function loadCalculation() {
        var textBox = document.getElementById('<%= tfdTernaryOperator.ClientID %>');
        if ($('#<%= rbtnCalculation.ClientID %>').is(':checked')) {
            var strCalculation = $('#<%= hidSelectedFormula.ClientID %>').val();
            formulaArray = [];
            if (strCalculation != '')
                formulaArray = strCalculation.split(',');
            updateFormulaDisplay();
            textBox.value = "";
        }
        else if($('#<%= rbtnTable.ClientID %>').is(':checked')) {
            textBox.value = "";
        }else if($('#<%= rbtnTernaryOperator.ClientID %>').is(':checked')){
            $('div.sourcefieldrow').hide();
            $('div.ternaryExpression').show();
            $('div.calcrow').hide();

            
                textBox.value = $('#<%= hidSelectedFormula.ClientID %>').val();

        }


    }

    $('a.btnFormula').on('click', function (e) {
        var selrowid = $(this).attr('rowid');
        var name = '', desc = '', eventName = '', sourcetype = 0, eventid = '', displayorder = 0, costcentre_enabled = 0, hidval='', hidid='';

        var hidObj = $(this).next('input[type=hidden]');
        hidval = $(hidObj).val().trim();
        hidid = $(hidObj).attr('id');

        $('#<%= hidSelRowId.ClientID %>').val(selrowid);
        $('#<%= hidSelectedFormula.ClientID %>').val(hidval);
        $('#<%= btnSaveFormula.ClientID %>').attr('hidid', hidid);

        if (selrowid > 0) {
            name = $(this).closest('tr').find('.lblColName').text();
            desc = $(this).closest('tr').find('.colDesc').text();
            eventName = $(this).closest('tr').find('.lblEventName').text();
            sourcetype = $(this).attr('sourceType');
            eventid = $(this).attr('eventId');
            displayorder = $(this).attr('displayorder');
            costcentre_enabled = $(this).attr('costcentre_enabled');
        }

        $('#<%= txtValueHead.ClientID %>').val(name);
        $('#<%= txtDescription.ClientID %>').val(desc);
        $('#<%= chkHasCostCenter.ClientID %>').prop('checked', (costcentre_enabled && costcentre_enabled > 0 ? true : false));

        $('#<%= hidSelRowId.ClientID %>').val(selrowid);
        $('#<%= hidSelectedFormula.ClientID %>').val(hidval);
        $('#<%= btnSaveFormula.ClientID %>').attr('hidid', hidid);
        // $('#<%= selpouptype.ClientID %>').val()

        $('#<%= selPositionBefore.ClientID %> option[value="' + displayorder + '"]').attr('selected', 'selected');
        if (eventid && eventid >= 0)
            $('#<%= selPopupEvent.ClientID %> option[value="' + eventid + '"]').attr('selected', 'selected');
        else if (eventName && eventName != '')
            $('#<%= selPopupEvent.ClientID %> option').filter(function () { return $(this).html() == eventName; }).attr('selected', 'selected');

        if (sourcetype == 1)
            $('input[type=radio][id=<%= rbtnCalculation.ClientID %>]').prop("checked", true); 
        else if(sourcetype == 2)
            $('input[type=radio][id=<%= rbtnTernaryOperator.ClientID %>]').prop("checked", true);
        else
             $('input[type=radio][id=<%= rbtnTable.ClientID %>]').prop("checked", true);
        toggleSourceField();
        //if (hidval != '')
            loadCalculation();

        $('#formulaModal').modal({ backdrop: 'static', keyboard: false }, 'show');
    });

    $('#<%= selField.ClientID %>').change(function () {
        const selectedField = $(this).val();
        if (selectedField && (formulaArray.length === 0 || isOperator(formulaArray[formulaArray.length - 1]))) {
            formulaArray.push('['+selectedField +']');
            updateFormulaDisplay();
            //disableFieldDropdown();
            //enableOperators();
            //disableConstantInput();
        }
    });

    function addOperator(op) {
        //if (formulaArray.length > 0 && !isOperator(formulaArray[formulaArray.length - 1])) {
            formulaArray.push(op);
            updateFormulaDisplay();
            //disableOperators();
            //enableConstantInput();
            //enableFieldDropdown();
        //}
    }

    function addConstant() {
        const constant = $('#constantInput').val();
        if (constant !== '' && !isNaN(constant)) {
            formulaArray.push(constant);
            updateFormulaDisplay();
            //disableConstantInput();
            //enableOperators();
            //enableComputeValidateButtons();
        } else {
            alert('Please enter a valid number.');
        }
    }
    function updateFormulaDisplay() {
        $('#formulaDisplay').text(formulaArray.join(' '));
        $('.formulaField, .btnOperation').prop('disabled', false);
       

        var lastElement = null;
        if (formulaArray.length > 0)
            lastElement = formulaArray.slice().pop();
        if (!lastElement || isOperator(lastElement))
            $('.btnOperation').prop('disabled', true);
        else
            $('.formulaField').prop('disabled', true);

        $('#bracaterigtBtn').prop("disabled", false)
    }

    function validateFormula() {
        const formula = formulaArray.join(' ');
        if (formula.includes('/ 0')) {
            alert('Division by zero detected! Please correct the formula.');
        } else if (isOperator(formulaArray[formulaArray.length - 1])) {
            alert('Formula cannot end with an operator.');
        } else {
            alert('Formula is valid!');
        }
    }

    function saveFormula() {
        if (!isOperator(formulaArray[formulaArray.length - 1])) {
            var strcalc = formulaArray.join(',');
            $('#<%= hidSelectedFormula.ClientID %>').val(strcalc);
            var hidId = $('#' + <%= btnSaveFormula.ClientID %>).attr('hidid');
            $('#' + hidId).val(strcalc);
            var selectedValue = $('#<%= selpouptype.ClientID %>').val();
            if (selectedValue !== "") {
                    $('#formulaModal').modal('hide');
            }else{
                alert("Please select a valid type.");
            }
           
        } else {
            alert('Cannot save a formula that ends with an operator.');
            return false;
        }
        return true;
    }

    // Helper functions
    function isOperator(value) {
        return ['+', '-', '*', '/','(',')'].includes(value);
    }
    function deleteFormula() {
        formulaArray.pop();
        updateFormulaDisplay();
    }

    function allowAlphanumericUnderscore(event) {
        var charCode = event.keyCode || event.which;
        var keyChar = String.fromCharCode(charCode);

        // Regular expression to allow alphanumeric and underscore
        var regex = /^[a-zA-Z0-9_]$/;

        // Test the key character against the regex
        if (!regex.test(keyChar)) {
            event.preventDefault();  // Prevent invalid input
            return false;
        }

        return true;
    }

<%--    $('input[type=radio][id=<%= rbtnCalculation.ClientID %>]').on("click",function () {
        if ($(this).is(':checked')) {
            $('div.sourcefieldrow').hide();
            $('div.calcrow').show();
        }
        else {
            $('div.sourcefieldrow').show();
            $('div.calcrow').hide();
        }
    });
    $('input[type=radio][id=<%= rbtnTable.ClientID %>]').on("click", function () {
        if ($(this).is(':checked')) {
            $('div.sourcefieldrow').show();
            $('div.calcrow').hide();
        }
        else {
            $('div.sourcefieldrow').hide();
            $('div.calcrow').show();
        }
    });--%>

    $('#<%= rbtnTable.ClientID %>, #<%= rbtnCalculation.ClientID %>, #<%= rbtnTernaryOperator.ClientID %>').change(function () {
        toggleSourceField();
    });

    function toggleSourceField() {
        if ($('#<%= rbtnCalculation.ClientID %>').is(':checked')) {
            $('div.sourcefieldrow').hide();
            $('div.ternaryExpression').hide();
            $('div.calcrow').show();
        }
        else if($('#<%= rbtnTable.ClientID %>').is(':checked')) {
            $('div.ternaryExpression').hide()
            $('div.sourcefieldrow').show();
            $('div.calcrow').hide();
        }else if($('#<%= rbtnTernaryOperator.ClientID %>').is(':checked')){
            $('div.sourcefieldrow').hide();
            $('div.ternaryExpression').show();
            $('div.calcrow').hide();

            var textBox = document.getElementById('<%= tfdTernaryOperator.ClientID %>');
            if (textBox.value.trim() === "") {
                textBox.value = "";
            }
        }
    }

        function keepFocus(textbox) {
            setTimeout(function() {
                textbox.focus();
            }, 0);
        }

        function addField() {
            // Get the dropdown list and the selected value
            var ddlField = document.getElementById('<%= ddlField.ClientID %>');
            var selectedValue = ddlField.value;

            // Get the textbox
            var textBox = document.getElementById('<%= tfdTernaryOperator.ClientID %>');

            // Check if a value is selected in the dropdown
            if (selectedValue) {
                // Get the current cursor position in the textbox
                var start = textBox.selectionStart;
                var end = textBox.selectionEnd;

                // Insert the selected value at the cursor position
                var textBefore = textBox.value.substring(0, start);
                var textAfter = textBox.value.substring(end, textBox.value.length);
                textBox.value = textBefore + '[' + selectedValue + ']' + textAfter;

                // Set the cursor position after the inserted text
                textBox.selectionStart = textBox.selectionEnd = start + selectedValue.length + 2;

                // Focus back on the textbox
                textBox.focus();
            }
        }

</script>
    <script>
        $(document).ready(function () {
            $(document).ready(function () {
                //$('.select2').select2();
                $('.select2-show-search').select2();
                //Bootstrap Duallistbox
                /*$('.duallistbox').bootstrapDualListbox();*/
                <asp:Literal ID="ltrScript" runat="server"/>
            });
        });
    </script>
    <%--<style>
    .select2.select2-container {
        width:100%!important;
    }
</style>--%>
    <style>
        .select2-container {
             width: 100% !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
                display: block;
        }

        .select2-container.select2-container--open {
              z-index: 1050;
            }
    </style>
</asp:Content>