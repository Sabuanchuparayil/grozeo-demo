<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="HeaderSearch.ascx.cs" Inherits="RetalineProAgent.Controls.HeaderSearch" %>
          <div class="search-box">
            <input type="text" id="txtheadsearch" class="form-control" placeholder="Search">
              <%--<a href="/SearchResult.aspx" onclick="$(this).attr('href', '/SearchResult.aspx?key='+('#txtheadsearch').val())" class="btn btn-primary"><i class="fa fa-search"></i></a>--%>
            <button class="btn btn-primary" onclick="return loadSearch()"><i class="fa fa-search"></i></button>
          </div>
<script type="text/javascript">
    function loadSearch() {
        var key = $('#txtheadsearch').val();
        if (key && key != '')
            window.location.href = '/SearchResult.aspx?key=' + key;        
        return false;
    }
</script>