<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="MerchandisingPrdsdetails.aspx.cs" Inherits="RetalineProAgent.Business.MerchandisingPrdsdetails" %>

<div class="card card-table">
    <h5 class="modal-title" id="lblTitle">Other Merchants for this product</h5>
    <div class="table-responsive">
            <table class="table table-bordered" cellspacing="0" border="1">
                <thead class="custom-header">
                    <tr>
                        <th>Merchant</th>
                        <th>Address</th>
                        <th>MRP</th>
                        <th>Selling Price</th>
                        <th>Margin</th>
                    </tr>
                </thead>
                <asp:Repeater ID="rptDetails" runat="server" DataSourceID="SDSListDetails">
                    <itemtemplate>
                        <tbody>
                            <tr>
                                <td><%# Eval("storeGroupName") %></td>
                                <td><%# Eval("branchAddress") %></td>
                                <td><%# Eval("mrp") %></td>
                                <td><%# Eval("selling_price") %></td>
                                <td><%# Eval("grozeo_margin") %></td>
                            </tr>
                        </tbody>
                    </itemtemplate>
                </asp:Repeater>
            </table>
    </div>

    <asp:SqlDataSource runat="server" ID="SDSListDetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="WITH all_merchants AS (SELECT itm.stit_id,itm.stit_SKU,itm.stit_category_name,itm.product_category,IFNULL(bi.mrp, IFNULL(itemMrp, IFNULL(itm.stit_MRP, 0))) AS mrp,
        IFNULL(bi.selling_price, 0) AS selling_price,(SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = itm.product_category) AS subcategory,
        issponsered,fb.br_Name,fb.br_storeGroup,bi.grozeo_margin,bi.discount_selling_price,fb.br_Address AS branchAddress,fb.areaId AS areaId,bi.branch_id,(SELECT br_Name FROM finascop_branch WHERE bi.branch_id=br_ID) AS branchName,
        (SELECT store_group_name FROM finascop_branch_group WHERE store_group_id=fb.br_storeGroup) AS storeGroupName,COUNT(*) OVER (PARTITION BY bi.stit_ID) AS total_count,
        ROW_NUMBER() OVER (PARTITION BY itm.stit_SKU ORDER BY bi.grozeo_margin DESC, discount_selling_price ASC) AS rn FROM finascop_stock_branch_inventory bi
        INNER JOIN finascop_stock_itemmaster itm ON bi.stit_ID = itm.stit_id
        LEFT JOIN finascop_branch fb ON fb.br_ID = bi.branch_id
        LEFT JOIN (SELECT stit_Id, itemMrp FROM item_mrp WHERE stit_Id = stit_ID GROUP BY stit_Id) itemBewMrp ON itemBewMrp.stit_Id = bi.stit_ID
        WHERE bi.issponsered = 1), ranked_merchants AS (SELECT stit_id,stit_SKU,stit_category_name,product_category,subcategory,mrp,selling_price,
        grozeo_margin,branchAddress,areaId,branchName,storeGroupName,branch_id,total_count,DENSE_RANK() OVER (PARTITION BY stit_id ORDER BY grozeo_margin DESC, discount_selling_price ASC) AS rnk
        FROM all_merchants) SELECT stit_id,stit_SKU,stit_category_name,product_category,subcategory,mrp,selling_price,grozeo_margin,branchAddress,areaId,storeGroupName,
        branchName,branch_id,total_count FROM ranked_merchants WHERE stit_id = @itemId AND rnk > 1"
        OnSelecting="SDSListDetails_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="itemId" DefaultValue="0" />
                            </SelectParameters>
                        </asp:SqlDataSource>
    <style>
        .custom-header {
    background-color: #2a6436;
  font-weight: 700;
  font-size: 12px;
}
    </style>

    <%--<script type="text/javascript">
    // Function to set the title dynamically
    function setTitle(title) {
        var lblTitle = document.getElementById('lblTitle');
        if (lblTitle) {
            lblTitle.innerText = title;
        }
    }

    // Call the function with the desired title
        setTitle('stit_SKU');
    </script>--%>

</div>

