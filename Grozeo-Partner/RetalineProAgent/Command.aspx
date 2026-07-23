<%@ Page Language="C#" MasterPageFile="~/Site.Master"  AutoEventWireup="true" CodeBehind="Command.aspx.cs" Inherits="RetalineProAgent.Command" %>


<asp:Content ID="BodyContent" ContentPlaceHolderID="MainContent" runat="server"><br />
    <asp:Label ID="lblSQLConnectionString" AssociatedControlID="txtConnection" Text="SQL Server Connection string" runat="server"></asp:Label><br />
    <asp:TextBox ID="txtConnection" runat="server" style="max-width: none!important; width: 100%;"></asp:TextBox>
    <br />

    <asp:Label ID="lblSql" Text="SQL" runat="server"></asp:Label><br />
    <asp:TextBox ID="txtSql" runat="server" TextMode="MultiLine" style="max-width: none!important; width: 100%;"></asp:TextBox>
    <br /><asp:CheckBox ID="chkToXL" Text="Save to Excel" runat="server" /><br />
    <asp:Button ID="btnConnect" Text="Execute" runat="server" OnClick="btnConnect_Click" />
<asp:PlaceHolder ID="plsContent" runat="server"></asp:PlaceHolder>
    <asp:GridView ID="gvTables" AutoGenerateColumns="true" runat="server"></asp:GridView>
    <asp:SqlDataSource ID="sdsConnect" runat="server" 
         ConnectionString="<%$ ConnectionStrings:conn %>"        
         SelectCommand="SELECT name, database_id, create_date  FROM sys.databases" SelectCommandType="Text"></asp:SqlDataSource>

</asp:Content>
