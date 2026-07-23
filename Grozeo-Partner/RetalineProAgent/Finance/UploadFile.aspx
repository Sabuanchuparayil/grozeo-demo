<%@ Page Language="C#"  Title="Upload Document Proof"  AutoEventWireup="true" CodeBehind="UploadFile.aspx.cs" Inherits="RetalineProAgent.Finance.UploadFile" %>
<html>
    <head>
        <title> Upload File</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1" />
    </head>
    <body>
<form id="form1" runat="server">
    <asp:PlaceHolder ID="plcUpload" runat="server">
        <div class="modal fade" id="DocumentUploadpopup" data-bs-backdrop="static" data-bs-keyboard="false"
                              tabindex="-1" aria-labelledby="DocumentUploadpopupLabel" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered  modal-lg">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="sDocumentUploadpopupLabel">Document Upload</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="display: none;">
                                      <span aria-hidden="true">&times;</span>
                                    </button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="row">
                                      <div class="col-sm-12">
                                        <div class="form-group" style="display: none;">
                                          <label class="mb-0">Document Name</label>
                                          <%--<input type="text" class="form-control" placeholder="Enter a document name ...">--%>
                                            <asp:TextBox ID="txtdocname" runat="server" CssClass="form-control"></asp:TextBox>
                                        </div>
                                      </div><!--col-->

                                      <div class="col-md-12">
                                        <div class="card-body">
                                          <div id="actions" class="row">
                                            <div class="uploadsec">


                                              <div id="documentupload_input" runat="server" class="btn-group w-100 btn-success rounded position-relative align-items-center uplodbtm" >
                                                  <asp:FileUpload ID="fupDocProof"  runat="server" />
                                                  <img id="imgFileUpload" src="/content/images/uplad_logo_icon.png" class="align-items-center">
                                              </div>

   

                                            </div><!--col-lg-4-->


                                          </div><!--actions-->
                                        </div><!--card-body-->
                                      </div><!--col-md-9-->

                                    </div><!--row-->


                                  </div><!--modal-body-->
                                  <div class="modal-footer" style="display: none;">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="display: none;">Close</button>
        <%--                            <button type="button" class="btn btn-primary">Upload</button>--%>
                                      <asp:Button ID="btnupload" CssClass="btn btn-primary" runat="server" OnClick="btnupload_Click"  Text="Upload" />
                                  </div>
                                  <asp:Label ID = "lblProofStatus" runat="server" Text=""></asp:Label>
                                </div><!--modal-content-->
                              </div><!--modal-dialog-->
                            </div>
    </asp:PlaceHolder>
                   <!--modal-->
    <asp:Label ID="lblMessage" runat ="server">

    </asp:Label>
    <asp:PlaceHolder ID="plcScript" runat="server">
        <script type="text/javascript" >

            $(document).ready(function () {
                $('#' + "<%=fupDocProof.ClientID%>").attr('accept', 'image/x-png,image/jpeg,image/jpg');
            })

            function docReady(fn) {
                // see if DOM is already available
                if (document.readyState === "complete" || document.readyState === "interactive") {
                    // call on next available tick
                    setTimeout(fn, 1);
                } else {
                    document.addEventListener("DOMContentLoaded", fn);
                }
            }

            docReady(function () {
                // DOM is loaded and ready for manipulation here
                document.getElementById('fupDocProof').click();
            });

            function UploadFile(fileUpload) {
                if (fileUpload.value != '') {
                    document.getElementById("<%=btnupload.ClientID %>").click();
        }
    }
            
        </script>
    </asp:PlaceHolder>
    </form>
    </body>
 <style>
    #DocumentUploadpopup .modal-dialog{
      max-width:400px;
      margin: 1.75rem auto;
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      -webkit-box-align: center;
      -ms-flex-align: center;
      align-items: center;
      box-shadow: 0 0 20px 0 rgba(0,0,0,0.60);
      box-sizing: border-box;
      padding: 20px;
      border-radius: 10px;
    }
    .modal-content{
      width: 100%;
    }
    #actions {
      width: 210px;
      height: 125px;
      display: flex;
      position: relative;
      align-items: flex-start;
      margin: auto;
      padding: 10px;
      background-color: #FFF;
      border: 2px dotted #000;
      border-radius: 10px;
      margin-bottom: 20px;
      justify-content:center;
  }
/*    .uplodbtm {
        cursor: pointer;
        display: inline-block;
        overflow: hidden;
        position: relative;
        padding: 0;
        width: 210px;
        height: 100%;
        background: url(uplad_file.png) center no-repeat;
    }*/
    input[type="file"] {
      cursor: pointer;
      height: 100%;
      position: absolute;
      filter: alpha(opacity=1);
      -moz-opacity: 0;
      opacity: 0 !important;
      width: 100%;
      top:0;
      left:0px;
  }
  h5.modal-title{
    font-weight: 600;
    line-height: 1.2;
    margin: 0 0 20px;
    font-size: 18px;
    font-family: Arial, Helvetica, sans-serif;
  }

  .btn-primary{
    color: #fff;
    background-color: #1a8e06;
    border-color: #188205;
    display: inline-block;
    font-weight: 400;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    user-select: none;
    border: 1px solid transparent;
    padding: 0.594rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.5;
    cursor: pointer;
  }
  .btn-primary:hover {
    color: #fff;
    background-color: #1c9a06;
    border-color: #1a8e06;
  }
  .card-body p{
    font-weight: 400;
    line-height: 1.2;
    margin: 10px 0;
    font-size: 14px;
    color: #1c9a06;
    font-family: Arial, Helvetica, sans-serif;
  }
  .btn-primary.hide{
    display: none;
  }
  
  .disabled {
    pointer-events: none; /* Prevent mouse clicks or interactions */
    opacity: 0.5; /* Make the element look faded */
    cursor: not-allowed; /* Show a "not allowed" cursor on hover */
}
    
    
  </style>       
</html>